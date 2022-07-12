<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController;


class AuthController extends Controller
{
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

    public function goToMain(){
        $login_field = 'login';
        $pass_field = 'password';
        return view('main', ['login_field' => $login_field, 'pass_field' => $pass_field]);
    }

    public function authenticate(Request $request){

        $request->validate([
            'login' => ['required', 'regex:/^[0-9a-zA-Z-_]+$/', 'min:8', 'max:30'],
            'password' => ['required', 'regex:/^[0-9a-zA-Z]+$/', 'min:8', 'max:70']
        ]);

        $hash = DB::table('Auth_data')->where('login', $request->input('login'))->value('password');

        if(!empty(DB::select('SELECT * FROM Auth_data WHERE login=(?)', [$request->input('login')])) && password_verify($request->input('password'),$hash)){
            $request->session()->regenerate();
            $acc_type = DB::table('Auth_data')->where('login', $request->input('login'))->value('acc_type');
            $request->session()->put('accountType', $acc_type);
            $request->session()->put('login', $request->input('login'));
            return ['type' => 0, 'accType' => $acc_type];
        }
        return ['type' => 1];
    }

    //Login processing
    public function cabinet_clerk(Request $request){
        if(DB::table('Messages_cond')->where('worker', '=', $request->session()->get('login'))->where('active', '=', 1)->doesntExist()){
            return view('cabinet_clerk');
        } else {
            $id_msg = DB::table('Messages_cond')->where('worker', '=', $request->session()->get('login'))->where('active', '=', 1)->value('id_message');
            //$ITN = DB::table('Auth_data')->where('login', )->value('ITN');
            return redirect('cabinet/messages/answer/'.DB::table('Messages')->where('id_message', $id_msg)->value('sender').'/'.$id_msg);
        }
    }

    public function cabinet_user(Request $request){
        $ITN = DB::table('Auth_data')->where('login', $request->session()->get('login'))->value('ITN');
        session(['ITN' => $ITN]);
        //$name = DB::table('Registered')->where('ITN', $ITN)->value('first_name');
        $name = UserController::getName($request);
        $full_msg = UserController::getFullMsg($request);
        $amount = UserController::getAmount($full_msg);
//        foreach ($full_msg as $item){
//            if($item['viewed'] == 0){
//                $amount++;
//            }
//        }
//        foreach ($full_msg as $item){
//            $full_msg[$i] = collect($item)->all();
//            $i++;
//        }
        //return view ('test', ['bruh'=>$full_msg]);
        return view('cabinet_user', ['msg' => $full_msg, 'name' => $name, 'amount' => $amount]);
    }

    public function cabinet_loan_officer(Request $request){
        if(DB::table('Application')->where('login_worker', '=', $request->session()->get('login'))->where('valid','=', 1)->doesntExist()) {
            $data = collect(DB::select('SELECT * FROM Application WHERE login_worker="login_worker"'))->all();
            return view('cabinet_loan_officer', ['data' => $data]);
        } else {
            $id_app = DB::table('Application')->where('login_worker', '=' ,$request->session()->get('login'))->where('valid', '=', 1)->value('id_app');
            $ITN = DB::table('Application')->where('login_worker', '=' ,$request->session()->get('login'))->where('valid', '=', 1)->value('ITN');
            return redirect('/application/'.$ITN.'/'.$id_app);
        }
    }

    public function cabinet(Request $request){
        if($request->session()->has('accountType')) {
            //$acc_type = $request->session()->get('accountType');
            //return $this->cabinet_loan_officer();
            //return view ('test', ['bruh'=>$acc_type]);
//            if($acc_type == 1){
//                $this->cabinet_user($request);
//            }
//            if($acc_type == 2){
//                $this->cabinet_loan_officer($request);
//            }
//            if($acc_type == 3){
//                //$this->cabinet_clerk($request);
//                return view ('test', ['bruh'=>$acc_type]);
//            }

            switch ($request->session()->get('accountType')){
                case 1:{
                    return $this->cabinet_user($request);
                }
                case 2:{
                    return $this->cabinet_loan_officer($request);
                }
                case 3:{
                    return $this->cabinet_clerk($request);
                }
                default:{
                    return redirect()->route('main_route');
                }
            }

        }
        return redirect()->route('main_route');
    }

    public function leave(Request $request){
        if($this->isLoanOfficer($request)) {
            //DB::update('UPDATE Application SET login_worker="login_worker" WHERE id_app=?', [$request->session()->get('id')]);
            DB::table('Application')->where('id_app', [$request->session()->get('id')])->where('valid', '=', 1)->update(['login_worker' => "login_worker"]);
            if(DB::table('Application')->where('id_app', [$request->session()->get('id')])->value('valid')==1) {
                DB::table('Messages')->where('id_message', [$request->session()->get('id_msg')])->update(['sender' => DB::table('Auth_data')->where('ITN', $request->session()->get('ITN'))->value('login')]);
            }
        } else if($this->isClerk($request) && $request->session()->has('ITN')) {
            $id_answer = DB::table('Messages_cond')->where('worker', $request->session()->get('login'))->where('active', '=', 1)->value('id_answer');
            DB::table('Messages')->where('id_message', $id_answer)->update(['sender' => DB::table('Auth_data')->where('ITN', $request->session()->get('ITN'))->value('login')]);
            DB::table('Messages_cond')->where('id_answer', $id_answer)->where('active', '=', 1)->update(['worker' => 'worker']);
        }
        $request->session()->flush();
        return redirect()->route('main_route');
    }



}