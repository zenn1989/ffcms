<form action="" method="post">
    <div class="pull-right">
        <a href="?object=antivirus&action=exclude" class="btn btn-danger">{$lang::admin_antivirus_exclude_onlist}</a> <input type="submit" name="submit" value="{$lang::admin_antivirus_rescan}" class="btn btn-success"/>
    </div>
</form>
<br/><br/>
<h5>{$lang::admin_antivirus_injected_h5}</h5>
<p>{$lang::admin_antivirus_injected_desc}</p>
{$avir_injected}
<h5>{$lang::admin_antivirus_wrong_h5}</h5>
<p>{$lang::admin_antivirus_wrong_desc}</p>
{$avir_wrong}
<h5>{$lang::admin_antivirus_unknown_h5}</h5>
<p>{$lang::admin_antivirus_unknown_desc}</p>
{$avir_unknown}
<h5>{$lang::admin_antivirus_safe_h5}</h5>
<p>{$lang::admin_antivirus_safe_desc}</p>
{$avir_clear}