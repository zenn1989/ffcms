<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <div class="nav-collapse">
                <ul class="nav">
                    <li><a href="#"> Главная</a></li>

                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"> Новости
                            <b class="caret"></b>
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a href="#"> Все новости</a>
                            </li>

                            <li>
                                <a href="#"> Астрономия</a>
                            </li>
                        </ul>
                    </li>

                    <li><a href="#"> Блоги</a></li>

                    <li><a href="{$url}/user/"> Пользователи</a></li>
                </ul>
                <ul class="nav pull-right">
                    {$if user.auth}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> {$user_nick} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="{$url}/user/id{$user_id}">К профилю</a></li>
                            <li><a href="{$url}/message">Сообщения ({$user_notify.pm_new_count})</a></li>
                            <li><a href="{$url}/user/id{$user_id}/friends/request">Запросы в друзья({$user_notify.friend_request_count})</a></li>
                            <li class="divider"></li>
                            <li><a href="{$url}/logout">{$lang::usercontrol_menu_exit}</a></li>
                        </ul>
                    </li>
                    {$/if}
                    {$if user.auth && user.admin}
                    <li class="navbar-text"><a href="{$url}/admin.php">Админ панель</a></li>
                    {$/if}
                    {$if !user.auth}
                    <li class="navbar-text"><a href="{$url}/register">{$lang::usercontrol_menu_reg}</a></li>
                    <li class="navbar-text"><a href="{$url}/login">{$lang::usercontrol_menu_auth}</a></li>
                    {$/if}
                </ul>
            </div>
        </div>
    </div>
</div>