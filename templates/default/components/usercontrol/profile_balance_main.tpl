<h3>{$lang::usercontrol_profile_settings_balance_title}</h3>
<button class="pull-right btn btn-info">{$lang::usercontrol_profile_settings_balance_button}: {$user_balance}$</button>
<br />
<p>{$lang::usercontrol_profile_settings_balance_desc}</p>
{$if com.usercontrol.have_balance_log}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>{$lang::usercontrol_profile_settings_balance_log_number}</th>
            <th>{$lang::usercontrol_profile_settings_balance_log_price}</th>
            <th>{$lang::usercontrol_profile_settings_balance_log_type}</th>
            <th>{$lang::usercontrol_profile_settings_balance_log_date}</th>
        </tr>
    </thead>
    <tbody>
        {$operations_table}
    </tbody>
</table>
{$/if}