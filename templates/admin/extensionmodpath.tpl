{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ language.admin_extension_modroute_title }}</h1>
<hr />
<p>{{ language.admin_extension_modroute_desc }}</p>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>{{ language.admin_extension_modroute_htable_th_param }}</th>
            <th>{{ language.admin_extension_modroute_htable_th_value }}</th>
            <th>{{ language.admin_extension_modroute_htable_th_desc }}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><code>;</code></td>
            <td>{{ language.admin_extension_modroute_htable_td_val_1 }}</td>
            <td><code>news/*;static/*</code></td>
        </tr>
        <tr>
            <td><code>*</code></td>
            <td>{{ language.admin_extension_modroute_htable_td_val_2 }}</td>
            <td>
                <code>*</code> - {{ language.admin_extension_modroute_htable_td_ex_2_1 }}<br />
                <code>news/*</code> - {{ language.admin_extension_modroute_htable_td_ex_2_2 }}
            </td>
        </tr>
        <tr>
            <td><code>index</code></td>
            <td>{{ language.admin_extension_modroute_htable_td_val_3 }}</td>
            <td><code>index;news/*</code> - {{ language.admin_extension_modroute_htable_td_ex_3 }}</td>
        </tr>
        </tbody>
    </table>
</div>
{% if local.saved %}
    {{ notifytpl.success(language.admin_extension_modroute_notify_save) }}
{% endif %}
<form class="form-horizontal" role="form" method="post">
    <input type="hidden" name="csrf_token" value="{{ system.csrf_token }}" />
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_extension_modroute_modname }}</label>
        <div class="col-lg-9">
            <input type="text" class="form-control" value="{{ local.mod_name }}" disabled>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label" for="mod_rule_type">{{ language.admin_extension_modroute_ruletype }}</label>
        <div class="col-lg-9">
            <select name="mod_rule_type" class="form-control" id="mod_rule_type">
                <option value="0"{% if local.path_choice != 1 %} selected{% endif %}>Deny</option>
                <option value="1"{% if local.path_choice == 1 %} selected{% endif %}>Allow</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-3 control-label">{{ language.admin_extension_modroute_routesval }}</label>
        <div class="col-lg-9">
            <input type="text" name="mod_rule_value" value="{% if local.path_choice == 1 %}{{ local.path_allow }}{% else %}{{ local.path_deny }}{% endif %}" class="form-control" />
        </div>
    </div>
    <input type="submit" name="submit" value="{{ language.admin_extension_modroute_btn_save }}" class="btn btn-success" />
</form>