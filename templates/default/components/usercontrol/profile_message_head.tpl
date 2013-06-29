<div class="pull-left">
    <a href="{$url}/message/all" class="btn btn-success">{$lang::usercontrol_profile_pm_menuall}</a> <a
            href="{$url}/message/in" class="btn btn-info">{$lang::usercontrol_profile_pm_menuin}</a> <a
            href="{$url}/message/out" class="btn btn-danger">{$lang::usercontrol_profile_pm_menuout}</a>
</div>
<div class="pull-right">
    <a href="{$url}/message/write" class="btn btn-success">{$lang::usercontrol_profile_pm_writenew}</a>
</div><br/>
<hr/>
<ul class="pager">
    {$if com.usercontrol.have_previous}
    <li class="previous"><a href="{$url}/message/{$message_type}/{$message_prev}">&larr;</a></li>
    {$/if}
    {$if com.usercontrol.have_next}
    <li class="next"><a href="{$url}/message/{$message_type}/{$message_next}">&rarr;</a></li>
    {$/if}
</ul>
{$message_body}