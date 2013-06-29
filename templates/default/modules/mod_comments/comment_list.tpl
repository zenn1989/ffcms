<blockquote class="white">
    <table class="table">
        <tr>
            <td></td>
            <td>{$lang::comments_text_messagefrom}: <a
                        href="{$url}/user/id{$poster_id}"><b>{$poster_nick}</b></a> {$lang::comments_text_messageon} {$comment_date}
            </td>
        </tr>
        <tr>
            <td width="65px"><img src="{$url}/upload/user/avatar/small/{$poster_avatar}" style="max-width: 60px;"/></td>
            <td>{$comment_text}</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <div class="pull-left">
                    {$if user.auth}
                    {$comment_link_edit}
                    {$comment_link_delete}
                    {$/if}
                </div>
                <div class="pull-right">
                    {$if user.auth}
                    <a onclick="replayto('{$poster_nick}')" href="#"><i
                                class="icon-random"></i> {$lang::comments_text_answerto}</a>
                    {$/if}
                </div>
            </td>
        </tr>
    </table>
</blockquote>