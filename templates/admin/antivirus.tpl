<form action="" method="post">
    <input type="submit" name="submit" value="{$lang::admin_antivirus_rescan}" class="pull-right btn btn-success"/>
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