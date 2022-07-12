<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Личный кабинет | Уведомления </title>
    <meta charset='utf-8'/>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'/>
    <link rel="shortcut icon" href="http://194.67.116.171/img/tiunoff.png" type="image/png">
    <link href="http://194.67.116.171/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://194.67.116.171/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="http://194.67.116.171/css/lk_view_notifications.css"/>
    <link rel="stylesheet" href="http://194.67.116.171/css/nav-style.css"/>
</head>

<body>
<!-- - - - - - - - - - - - - - - - -  N A V B A R - - - - - - - - - - - - - - - - - -  -  -->
<nav class="navbar navbar-expand-lg" style="background-color: black;"></nav>

<div class="p-3 mb-2 bg-dark">
    <ul class="nav justify-content-end">
        <li class="nav-item col-12 col-md-11">
            <a class="nav-link text-white" href="#">Главная</a>
        </li>
        <li class="nav-item col-12 col-md-1">
            <button type="button" class="btn btn-style" onclick="location.href='http://194.67.116.171/leave'">Выйти</button>
        </li>
    </ul>
</div>
<!-- - - - - - - - - - - - - - - - -  N A V B A R - - - - - - - - - - - - - - - - - -  -  -->

<div class="container-fluid">
    <div class="col-md-12 mt-4 mb-5 text-center title_lk">
        <h1 class = "py-3">ЛИЧНЫЙ КАБИНЕТ</h1>
    </div>
    <div class="col-md-12 mt-4 mb-5 text-center">
        <h5 style="font-weight: bold"><a class="text-danger" href="http://194.67.116.171/cabinet">ЛИЧНЫЙ КАБИНЕТ</a>/УВЕДОМЛЕНИЯ</h5>
    </div>
    <div class="row">
        <div class="col-md-8 mb-5 pb-5 mx-auto">
            @if(!empty($msg))
                @foreach($msg as $item)
                    @switch($item->msg_type)
                        @case(1)
                            <div class="col-12 col-lg-8 mx-auto support-msg mt-2 border rounded border-dark">
                                <div>ОТВЕТ НА ЗАЯВКУ В ЦЕНТР ПОДДЕРЖКИ ПОЛЬЗОВАТЕЛЕЙ №{{$item->id_message}}</div>
                                <div>ТЕМА: {{$item->msg_topic}}</div>
                            @break
                        @case(2)
                            <div class="col-12 col-lg-8 mx-auto application-msg mt-2 border rounded border-dark">
                                <div>ОТВЕТ НА ЗАЯВКУ НА ОФОРМЛЕНИЕ КРЕДИТА №{{$item->msg_topic}}</div>
                            @break
                        @default
                            <div class="col-12 col-lg-8 mx-auto system-msg mt-2 border rounded border-dark">
                                <div>СИСТЕМНОЕ СООБЩЕНИЕ №{{$item->id_message}}</div>
                            @break
                    @endswitch
                    <div class="text-msg">
                        {{$item->text}}
                    </div>
                </div>
                @endforeach
            @else
                <h1 class="text-center">
                    На данный момент нет уведомлений.
                </h1>
            @endif



            <!--
            <table class="table table-bordered border-dark mb-5 mt-5">
                <thead class = "text-center">
                <tr>
                    <th scope="col" class="text_size">ДАТА ПОЛУЧЕНИЯ</th>
                    <th scope="col" class="text_size">ТЕМА СООБЩЕНИЯ</th>
                    <th scope="col" class="text_size">ОТПРАВИТЕЛЬ</th>
                    <th scope="col" class="text_size">ТЕКСТ СООБЩЕНИЯ</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row"></th>
                    <td>&nbsp</td>
                    <td>&nbsp</td>
                    <td>
                        <div class = "text-center">
                            <button type="button" id = "show_button_1" class="btn-detail">
                                подробнее
                            </button>
                        </div>
                        <div id="wrapper_1">
                            <div class="modal_content">
                                <div class="knopkaX" id="close_1"></div>
                                <form id="main_1" name="main_1" accept-charset="UTF-8">
                                    Тема: <br/>
                                    Отправитель: <br/>
                                    Текст сообщения: <br/>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>&nbsp</td>
                    <td>&nbsp</td>
                    <td>
                        <div class = "text-center">
                            <button type="button" id = "show_button_2" class="btn-detail">
                                подробнее
                            </button>
                        </div>
                        <div id="wrapper_2">
                            <div class="modal_content">
                                <div class="knopkaX" id="close_2"></div>
                                <form id="main_2" name="main_2" accept-charset="UTF-8">
                                    Тема: <br/>
                                    Отправитель: <br/>
                                    Текст сообщения: <br/>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>&nbsp</td>
                    <td>&nbsp</td>
                    <td>
                        <div class = "text-center">
                            <button type="button" id = "show_button_3" class="btn-detail">
                                подробнее
                            </button>
                        </div>
                        <div id="wrapper_3">
                            <div class="modal_content">
                                <div class="knopkaX" id="close_3"></div>
                                <form id="main_3" name="main_3" accept-charset="UTF-8">
                                    Тема: <br/>
                                    Отправитель: <br/>
                                    Текст сообщения: <br/>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            -->
        </div>
    </div>
</div>

<!-- - - - - - - - - - - - - - - - -  F  O  O  T  E  R - - - - - - - - - - - - - - - - - -  -  -->
<footer class="bg-dark text-center text-white">
    <div class="container p-4">
        <section>
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Контактная информация:</h5>
                    +7-918-931-39-32
                </div>
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Адрес:</h5>
                    г. Краснодар, ул. Красная 32
                </div>
            </div>
        </section>
    </div>

    <div class="text-center p-3" style="background-color: black;">
        © 2022. Tiunoff bank, официальный сайт.
    </div>
</footer>
<!-- - - - - - - - - - - - - - - - -  F  O  O  T  E  R - - - - - - - - - - - - - - - - - -  -  -->

<script src="http://194.67.116.171/js/lk_view_notifications.js"></script>
<script src="http://194.67.116.171/js/bootstrap.bundle.min.js"></script>

</body>
</html>
