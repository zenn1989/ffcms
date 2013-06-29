<div class="row">
    <div class="span4">
        <div class="row">
            {$friend_body}
        </div>
        <ul class="pager">
            {$if com.usercontrol.have_previous}
            <li class="previous">
                <a href="{$url}/user/id{$target_user_id}/friends/{$friend_prev}">&larr;</a>
            </li>
            {$/if}
            {$if com.usercontrol.have_next}
            <li class="next">
                <a href="{$url}/user/id{$target_user_id}/friends/{$friend_next}">&rarr;</a>
            </li>
            {$/if}
        </ul>
    </div>
    <div class="span1">
        <div class="tabbable tabs-right">
            <ul class="nav nav-tabs">
                {$if com.usercontrol.self_profile && !com.usercontrol.profile_friend_request}
                <li class="active"><a
                            href="{$url}/user/id{$target_user_id}/friends">{$lang::usercontrol_profile_friends}</a></li>
                <li>
                    <a href="{$url}/user/id{$target_user_id}/friends/request">{$lang::usercontrol_profile_requests_friends}</a>
                </li>
                {$/if}
                {$if com.usercontrol.self_profile && com.usercontrol.profile_friend_request}
                <li><a href="{$url}/user/id{$target_user_id}/friends">{$lang::usercontrol_profile_friends}</a></li>
                <li class="active"><a
                            href="{$url}/user/id{$target_user_id}/friends/request">{$lang::usercontrol_profile_requests_friends}</a>
                </li>
                {$/if}
            </ul>
        </div>
    </div>
</div>