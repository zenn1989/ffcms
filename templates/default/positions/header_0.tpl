<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <div class="nav-collapse">
                <ul class="nav">
                    <li><a href="{$url}"> {$lang::position_header_main}</a></li>
                    <li><a href="{$url}/news/"> {$lang::position_header_news}</a></li>
                    <li><a href="{$url}/user/"> {$lang::position_header_users}</a></li>
                </ul>
                <ul class="nav pull-right">
                    {$if user.auth}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> {$user_nick} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="{$url}/user/id{$user_id}">{$lang::position_header_toprofile}</a></li>
                            <li><a href="{$url}/message">{$lang::position_header_messages} ({$user_notify.pm_new_count})</a></li>
                            <li><a href="{$url}/user/id{$user_id}/friends/request">{$lang::position_header_friendrequests} ({$user_notify.friend_request_count})</a></li>
                            <li class="divider"></li>
                            <li><a href="{$url}/logout">{$lang::usercontrol_menu_exit}</a></li>
                        </ul>
                    </li>
                    {$/if}
                    {$if user.auth && user.admin}
                    <li class="navbar-text"><a href="{$url}/admin.php">{$lang::position_header_adminpanel}</a></li>
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