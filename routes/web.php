<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\LoanOfficerController;
use App\Http\Controllers\ClerkController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

function isClerk($request): bool
{
    return $request->session()->has('accountType') && $request->session()->get('accountType')==3;
}

function isLoanOfficer($request): bool
{
    return $request->session()->has('accountType') && $request->session()->get('accountType')==2;
}

function isUser($request): bool
{
    return $request->session()->has('accountType') && $request->session()->get('accountType')==1;
}

Route::get('/', function (Request $request) {
    if(isLoanOfficer($request)){
        return view('main', ['login' => $request->session()->get('login')]);
    }
    $login_field = 'login';
    $pass_field = 'password';
    $acc_type=0;
    if($request->session()->has('accountType')){
        $acc_type = $request->session()->get('accountType');
    }
    return view('main', ['login_field' => $login_field, 'pass_field' => $pass_field, 'acc_type' => $acc_type]);
})->name('main_route');

Route::post('/login', [AuthController::class, 'authenticate']);

Route::post('/registration', [RegistrationController::class, 'registration']);

Route::get('/registration', function (){
    return view('registration');
})->name('registration_route');

//Cabinet
Route::get('/cabinet', [AuthController::class, 'cabinet'])->name('cabinet_route');

//Application
Route::get('/application/{itn}/{id}', function ($itn, $id, Request $request){
    if(isLoanOfficer($request)){
            $id_message = DB::table('Application')->where('id_app', $id)->value('id_message');
            session(['ITN'=>$itn, 'id'=>$id, 'id_msg' => $id_message]);
            DB::table('Messages')->where('id_message', $id_message)->update(['sender' => $request->session()->get('login')]);
            //DB::select('SELECT * FROM Application WHERE login_worker=?', [$request->session()->get('login')])
            //DB::update('UPDATE Application SET in_work=1 WHERE id_app=?', [$id]);
            DB::table('Application')->where('id_app', $id)->update(['login_worker' => $request->session()->get('login')]);
            return view('application', ['id' => $id]);
    }
    return redirect()->route('main_route');
});

//Solvency
Route::get('/solvency', function (Request $request){
    if(isLoanOfficer($request)){
        return view('solvency', ['id' => $request->session()->get('id')]);
    }
    return redirect()->route('main_route');
});

Route::post('/solvency', [LoanOfficerController::class, 'getSolvency']);

//Loan Response
Route::get('/loan_response', function (Request $request){
    if(isLoanOfficer($request)) {
        $data = current(DB::select('SELECT * FROM Application WHERE id_app = ?', [$request->session()->get('id')]));
        //return view('test', ['bruh' => $data]);
        if(!empty($data)) {
            if ($data->rating == 0) {
                return view('loan_response', ['itn' => $data->ITN, 'sum' => $data->sum, 'term' => $data->term, 'fee' => $data->fee, 'rating' => $data->rating, 'id' => $data->id_app, 'flag' => $data->flag]);
            } else {
                return view('loan_response', ['itn' => $data->ITN, 'sum' => $data->sum, 'term' => $data->term, 'fee' => $data->fee, 'rating' => $data->rating, 'fee_max' => $data->fee_max, 'term_new' => $data->term_new, 'sum_new' => $data->sum_new, 'id' => $data->id_app, 'flag' => $data->flag]);
            }
        }
    }
    return view('loan_response', ['error' => 'yeah, dude, we have got error']);
})->name('loan_response_route');


//Rating
Route::post('/rating', [LoanOfficerController::class, 'getRating']);

//Additional Data Check
Route::get('/additional_data_check', function (Request $request){
   if(isLoanOfficer($request)){
       $data = current(collect(DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->get())->all());
       $pass_data = DB::table('Registered')->where('ITN', $request->session()->get('ITN'))->value('passport_data');
       $count = 0;
       if(!empty(DB::table('Overdue')->where('ITN', $request->session()->get('ITN')))){
           $count = DB::table('Overdue')->where('ITN', $request->session()->get('ITN'))->value('count_o');
       }
       //return view('test', ['bruh'=>$data]);
       $guarantor_ITN = 80085;
       if(!empty(DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->value('guarantor'))){
           $guarantor_ITN = DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->value('guarantor');
       }
       return view('additional_data_check', ['INIPA' => $data->INIPA, 'criminal_record' => $data->criminal_record, 'income_statement' => $data->income_statement, 'ITN' => $data->ITN, 'passport_data'=>$pass_data, 'count' => $count, 'id'=>$request->session()->get('id'), 'guarantor' => $guarantor_ITN, 'statement' => $data->statement]);
   } else {
       return redirect()->route('main_route');
   }
});

Route::get('/additional_data_check_guarantor', function (Request $request){
    if(isLoanOfficer($request) && !empty(DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->value('guarantor'))){
        $guarantor_ITN = DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->value('guarantor');
        $data = current(collect(DB::table('Guarantor')->where('ITN', $guarantor_ITN)->get())->all());
        $statement = DB::table('Additional_data')->where('ITN', $request->session()->get('ITN'))->value('statement');
        return view('additional_data_check_guarantor', ['INIPA' => $data->INIPA, 'income_statement' => $data->income_statement, 'ITN' => $data->ITN, 'passport_data'=>$data->passport_data, 'id'=>$request->session()->get('id'), 'last_name' => $data->last_name, 'first_name' => $data->first_name, 'middle_name' => $data->middle_name, 'statement' => $statement]);
    } else {
        return redirect()->route('main_route');
    }
});

Route::post('/additional_data_check', [LoanOfficerController::class, 'checkResult']);

Route::post('/additional_data_check_guarantor', [LoanOfficerController::class, 'checkResultGuarantor']);

//Loan Response Message
Route::get('/loan_response_message', function (Request $request){
    if(isLoanOfficer($request)) {
        $data = DB::select('SELECT * FROM Application WHERE id_app = ?', [$request->session()->get('id')]);
        if(!empty($data)) {
            return view('loan_response_message', ['id' => $request->session()->get('id')]);
        }
    }
    return redirect()->route('main_route');

})->name('loan_response_message_route');

Route::post('/loan_response_message', [LoanOfficerController::class, 'loanResponseMessage']);


//Black List
Route::get('/black_list', function (Request $request){
    if(isLoanOfficer($request)){
        return view('black_list', ['id' => $request->session()->get('id')]);
    }
    return redirect()->route('main_route');
});

Route::post('/black_list', [LoanOfficerController::class, 'insertIntoBlackList']);

Route::any('/leave', [AuthController::class, 'leave'])->name('leave_route');

Route::get('/loan_response_contract', function (Request $request){
    if(isLoanOfficer($request)){
        $data = current(collect(DB::table('Application')->where('id_app', $request->session()->get('id'))->get())->all());
        //
        //$confirm = DB::table('Application')->where('id_app', $request->session()->get('id'))->value('confirm');
        return view('loan_response_contract', ['confirm' => $data->confirm, 'id' => $request->session()->get('id'), 'contract' =>$data->contract ]);
    }
    return redirect()->route('main_route');
});

Route::get('/cabinet/loan_response/contract/refuse', [LoanOfficerController::class, 'loanResponseContractRefuse']);

Route::post('/loan_response_contract', [LoanOfficerController::class, 'loanResponseContract']);

Route::get('/drop_application', function (Request $request){
    if(isLoanOfficer($request)){
        DB::table('Application')->where('id_app', $request->session()->get('id'))->update(['login_worker' => "login_worker"]);
        return redirect()->route('cabinet_route');
    } else if(isClerk($request)) {
        $id_answer = DB::table('Messages_cond')->where('worker', $request->session()->get('login'))->where('active', '=', 1)->value('id_answer');
        DB::table('Messages')->where('id_message', $id_answer)->update(['sender' => DB::table('Auth_data')->where('ITN', $request->session()->get('ITN'))->value('login')]);
        DB::table('Messages_cond')->where('id_answer', $id_answer)->update(['worker' => 'worker']);
        return redirect()->route('clerk_msgs_route');
    }
    return redirect()->route('main_route');
});

//User

Route::get('/cabinet/additional_data', [UserController::class, 'getAdditional']);

Route::post('/cabinet/notifications', [UserController::class, 'messageControl']);

Route::post('/application/send', [UserController::class, 'sendApplication']);

Route::post('/cabinet/additional_data/add', [UserController::class, 'addAdditional']);

Route::post('/cabinet/additional_data/addFile', [UserController::class, 'addAdditionalFile']);

Route::post('/cabinet/additional_data/delete', [UserController::class, 'deleteAdditional']);

Route::get('/cabinet/statuses', [UserController::class, 'getStatuses']); //2 //blade

Route::get('/cabinet/additional_data/guarantor', [UserController::class, 'getGuarantor']);

Route::post('/cabinet/additional_data/guarantor', [UserController::class, 'addGuarantor']);

Route::post('/cabinet/safety', [UserController::class, 'changeAuthData']);

Route::get('/cabinet/safety', [UserController::class, 'getSafety']);

Route::get('/cabinet/support', [UserController::class, 'getSupport']);

Route::post('/cabinet/support', [UserController::class, 'sendSupport']);

Route::post('/application/test', function (Request $request){
    return ['bruh' => $request->input('_token')];
});

Route::post('/cabinet/contract/refuse', [UserController::class, 'refuseContract']);

Route::post('/cabinet/contract/add', [UserController::class, 'addContract']);

Route::post('/test_form', function (Request $request){
    $file = $request->file('file');
    $extension = $file->extension();
    $file->storeAs('', 'bruh5'.'.'.$extension);
    $path = Storage::path('bruh5'.'.'.$extension);
    $url = Storage::url('bruh5'.'.'.$extension);
    return ['msg' => $url];
});

Route::get('/test_form', function (Request $request){
    return view('test_form');
});

Route::get('/nigger/{file}', function (Request $request, $file){
    $path = Storage::path($file);
    return response()->file($path);
});

Route::get('/files/{file}', function (Request $request, $file){
    if(isUser($request) || isLoanOfficer($request)) {
        $path = Storage::path($file);
        return response()->file($path);
    } else if ($file = "personal.pdf") {
        $path = Storage::path($file);
        return response()->file($path);
    }
    return redirect()->route('main_route');
    //return Storage::download($file);
});

//Скачивание онли договора
Route::get('/files/download/loan_statement.pdf', function (Request $request){
    if(isUser($request)){
        $path = Storage::path('loan_statement.pdf');
        return response()->download($path);
    }
    return redirect()->route('main_route');
});

//Clerk

Route::get('/cabinet/messages', [ClerkController::class, 'getMessages'])->name('clerk_msgs_route');

Route::get('/cabinet/messages/answer/{login}/{id_msg}', [ClerkController::class, 'getAnswer']);

Route::post('/cabinet/messages/answer/send', [ClerkController::class, 'sendAnswer']);



