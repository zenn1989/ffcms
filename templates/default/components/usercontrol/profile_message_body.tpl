<blockquote class="white">
    <table class="table">
        <tr>
            <td></td>
            <td><a href="{$url}/user/id{$message_from_id}">{$from_nick}</a></td>
        </tr>
        <tr>
            <td width="20%"><img src="{$url}/upload/user/avatar/small/{$user_avatar}" style="max-width: 60px;"/></td>
            <td>{$user_message}</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <div class="pull-right"><a
                            href="{$url}/message/topic/{$message_topic_id}">{$lang::usercontrol_profile_pm_readmore}</a>
                </div>
            </td>
        </tr>
    </table>
</blockquote>