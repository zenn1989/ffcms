<div class="pull-right"><a href="?object=antivirus" class="btn btn-success">{$lang::admin_antivirus_exclude_tomain}</a></div>
<br />
<p>{$lang::admin_antivirus_exclude_desc}</p>
<form action="" method="post" class="form-inline">
    {$lang::admin_antivirus_exclude_label_dir}: <input type="text" name="antivir_dir" placeholder="/forum/"/> <input type="submit" name="antivir_exclude" class="btn btn-danger" value="{$lang::admin_antivirus_exclude_dosubmit}" />
</form>
<table class="table table-bordered">
    <thead>
        <tr>
            <th style="width: 80%">{$lang::admin_antivirus_exclude_th1}</th>
            <th style="width: 20%">{$lang::admin_antivirus_exclude_th2}</th>
        </tr>
    </thead>
    <tbody>
        {$antivirus_exclude_body}
    </tbody>
</table>