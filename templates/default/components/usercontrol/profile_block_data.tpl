<div class="tab-content">
    <div class="tab-pane active">
        <div class="span1"></div>
        <div class="span4">
            <table class="table table-striped">
                <tr>
                    <td width="40%">{$lang::usercontrol_profile_pers_reg_date}</td>
                    <td width="60%">{$user_regdate}</td>
                </tr>
                <tr>
                    <td>{$lang::usercontrol_profile_pers_birthday_date}</td>
                    <td>{$user_birthday}</td>
                </tr>
                <tr>
                    <td>{$lang::usercontrol_profile_pers_sex}</td>
                    <td>{$user_sex}</td>
                </tr>
                {$if com.usercontrol.have_phone}
                <tr>
                    <td>{$lang::usercontrol_profile_pers_phone}</td>
                    <td>{$user_phone}</td>
                </tr>
                {$/if}
                {$if com.usercontrol.have_webpage}
                <tr>
                    <td>{$lang::usercontrol_profile_pers_website}</td>
                    <td><a href="{$url}/api.php?action=redirect&url={$user_website}" target="_blank">{$user_website}</a>
                    </td>
                </tr>
                {$/if}
            </table>

            <h3 class="centered">{$lang::usercontrol_profile_pers_wall}</h3>
            <hr/>
            {$if user.auth && com.usercontrol.in_friends}
            <form action="{$url}/user/id{$target_user_id}" method="post">
                <textarea style="width: 100%;" name="wall_text"
                          placeHolder="{$lang::usercontrol_profile_pers_form_write}"></textarea>
                <input type="submit" name="wall_post" value="{$lang::global_send_button}"
                       class="btn btn-success pull-right"/>
            </form>
            <br/>
            {$/if}
            <div>
                {$user_wall}
            </div>
            <div>
                <ul class="pager">
                    {$if com.usercontrol.have_previous}
                    <li class="previous">
                        <a href="{$url}/user/id{$target_user_id}/wall/{$wall_prev}">&larr;</a>
                    </li>
                    {$/if}
                    {$if com.usercontrol.have_next}
                    <li class="next">
                        <a href="{$url}/user/id{$target_user_id}/wall/{$wall_next}">&rarr;</a>
                    </li>
                    {$/if}
                </ul>
            </div>
        </div>
    </div>
</div>