<script>
    var wm_mul_counter = {{ local.config.balance_wm_mul }};
    var ik_mul_counter = {{ local.config.balance_ik_mul }};
    var rk_mul_counter = {{ local.config.balance_rk_mul }};
    $(function() {
        $('#wm_source_count').blur(function(){
            var amount = parseFloat($('#wm_source_count').val());
            if(amount <= 0)
                return false;
            var mulamount = amount * wm_mul_counter;
            $('#wm_target_count').val(mulamount);
            return null;
        });
        $('#ik_source_count').blur(function(){
            var amount = parseFloat($('#ik_source_count').val());
            if(amount <= 0)
                return false;
            var mulamount = amount * ik_mul_counter;
            $('#ik_target_count').val(mulamount);
            return null;
        });
        $('#rk_source_count').blur(function(){
           var amount = parseFloat($('#rk_source_count').val());
            if(amount <= 0)
                return false;
            var mulamount = amount * rk_mul_counter;
            $('#rk_target_count').val(mulamount);
            return null;
        });
    });
</script>
<h2>{{ language.usercontrol_profile_settings_balance_title }}</h2>
<hr />
<div class="row">
    <div class="col-md-8 col-md-offset-4">
        <div class="pull-right">
            <button type="button" class="btn btn-success">{{ language.usercontrol_profile_settings_balance_button }}: {{ user.balance }}{{ local.config.balance_valut_name }}</button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#balancerecharge"><i class="fa fa-plus"></i></button>
        </div>
    </div>
</div>
<br />
<p>{{ language.usercontrol_profile_settings_balance_desc }}</p>
{% if local.balancelogs %}
<div class="table-responsive">
<table class="table table-bordered">
    <thead>
    <tr>
        <th>ID</th>
        <th>{{ language.usercontrol_profile_settings_balance_log_type }}</th>
        <th>{{ language.usercontrol_profile_settings_balance_log_price }}</th>
        <th>{{ language.usercontrol_profile_settings_balance_log_date }}</th>
        <th>{{ language.usercontrol_profile_settings_balance_log_comment }}</th>
    </tr>
    </thead>
    <tbody>
    {% for log in local.balancelogs %}
    <tr{% if log.type == 'balance.add' or log.type == 'balance.wmadd' or log.type == 'balance.ikadd' or log.type == 'balance.rkadd' %} class="alert-success"{% elseif log.type == 'balance.min' %} class="alert-danger"{% endif %}>
        <td>{{ log.id }}</td>
        <td>{% if log.type == 'balance.add' or log.type == 'balance.wmadd' or log.type == 'balance.ikadd' or log.type == 'balance.rkadd' %}{{ language.usercontrol_profile_settings_balance_in }}{% elseif log.type == 'balance.min' %}{{ language.usercontrol_profile_settings_balance_out }}{% else %}unknown{% endif %}</td>
        <td>{{ log.amount }}</td>
        <td>{{ log.date }}</td>
        <td>{{ log.message }}</td>
    </tr>
    {% endfor %}
    </tbody>
</table>
</div>
{% endif %}
<div class="modal fade" id="balancerecharge" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">{{ language.usercontrol_profile_settings_balance_recharge_title }}</h4>
            </div>
            <div class="modal-body">
                <p class="alert alert-info">{{ language.usercontrol_profile_settings_balance_recharge_desc }}</p>
                <div class="row">
                    {% if local.config.balance_use_webmoney %}
                    <div class="col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-heading">Webmoney - {{ local.config.balance_wm_type }}</div>
                            <div class="panel-body">
                                <p class="text-center"><img src="{{ system.script_url }}/resource/cmscontent/payment/webmoney.png" /></p>
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#paywebmoney">{{ language.usercontrol_profile_settings_balance_recharge_btn }}</button>
                            </div>
                        </div>
                    </div>
                    {% endif %}
                    {% if local.config.balance_use_ik %}
                        <div class="col-md-4">
                            <div class="panel panel-primary">
                                <div class="panel-heading">Interkassa - {{ local.config.balance_ik_valute }}</div>
                                <div class="panel-body">
                                    <p class="text-center"><img src="{{ system.script_url }}/resource/cmscontent/payment/interkassa.png" /></p>
                                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#payinterkassa">{{ language.usercontrol_profile_settings_balance_recharge_btn }}</button>
                                </div>
                            </div>
                        </div>
                    {% endif %}
                    {% if local.config.balance_use_rk %}
                        <div class="col-md-4">
                            <div class="panel panel-primary">
                                <div class="panel-heading">Robokassa - {{ local.config.balance_rk_valute }}</div>
                                <div class="panel-body">
                                    <p class="text-center"><img src="{{ system.script_url }}/resource/cmscontent/payment/robokassa.png" /></p>
                                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#payrobokassa">{{ language.usercontrol_profile_settings_balance_recharge_btn }}</button>
                                </div>
                            </div>
                        </div>
                    {% endif %}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">{{ language.usercontrol_profile_settings_balance_cancel_btn }}</button>
            </div>
        </div>
    </div>
</div>
{% if local.config.balance_use_webmoney %}
<div class="modal fade" id="paywebmoney" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Webmoney</h4>
            </div>
            <div class="modal-body">
                <p>{{ language.usercontrol_profile_settings_balance_wmtype_selected_desc }} {{ local.config.balance_wm_type }}: {{ local.config.balance_wm_mul }}</p>
                <form class="form-horizontal" role="form" action="https://merchant.webmoney.ru/lmi/payment.asp" method="post">
                    <input type="hidden" name="LMI_PAYMENT_DESC" value="Recharge balance on {{ system.url }}. User id: {{ user.id }}">
                    <input type="hidden" name="LMI_PAYMENT_NO" value="{{ user.id }}">
                    <input type="hidden" name="LMI_PAYEE_PURSE" value="{{ local.config.balance_wm_purse }}">
                    {% if local.config.balance_wm_test %}
                    <input type="hidden" name="LMI_SIM_MODE" value="0"> <!-- 0 = all ok, 1 = all fail, 2 = 80/20 -->
                    {% endif %}
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_wmtype_topay_title }} {{ local.config.balance_wm_type }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="LMI_PAYMENT_AMOUNT" placeholder="10.00" id="wm_source_count" required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_wmtype_paycalc_title }}</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" placeholder="0" id="wm_target_count" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_wmtype_paypurse_title }}</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="{{ local.config.balance_wm_purse }}" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <input type="submit" name="submit" value="{{ language.usercontrol_profile_settings_balance_wmtype_startpay_btn }}" class="btn btn-success" />
                            <button type="button" class="btn btn-warning" data-dismiss="modal">{{ language.usercontrol_profile_settings_balance_cancel_btn }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{% endif %}
{% if local.config.balance_use_ik %}
    <div class="modal fade" id="payinterkassa" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Interkassa 2.0</h4>
                </div>
                <div class="modal-body">
                    <p>{{ language.usercontrol_profile_settings_balance_iktype_selected_desc }}: {{ local.config.balance_ik_mul }}</p>
                    <form name="payment" method="post" action="https://sci.interkassa.com/" accept-charset="UTF-8" class="form-horizontal">
                        <input type="hidden" name="ik_co_id" value="{{ local.config.balance_ik_id }}" />
                        <input type="hidden" name="ik_pm_no" value="{{ user.id }}" />
                        <input type="hidden" name="ik_desc" value="Recharge balance on {{ system.url }}. User id: {{ user.id }}" />
                        <input type="hidden" name="ik_loc" value="{{ system.lang }}" />
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_iktype_topay_title }} {{ local.config.balance_ik_valute }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="ik_am" placeholder="10.00" id="ik_source_count" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_iktype_tobalance_title }}</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" placeholder="0" id="ik_target_count" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <input type="submit" name="submit" value="{{ language.usercontrol_profile_settings_balance_iktype_startpay_btn }}" class="btn btn-success" />
                                <button type="button" class="btn btn-warning" data-dismiss="modal">{{ language.usercontrol_profile_settings_balance_cancel_btn }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{% endif %}
{% if local.config.balance_use_rk %}
    <div class="modal fade" id="payrobokassa" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Robokassa</h4>
                </div>
                <div class="modal-body">
                    <p>{{ language.usercontrol_profile_settings_balance_rktype_desc }}: {{ local.config.balance_rk_mul }}</p>
                    <form name="payment" method="post" action="" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_rktype_topay_title }} {{ local.config.balance_rk_valute }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="topay" placeholder="10.00" id="rk_source_count" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ language.usercontrol_profile_settings_balance_rktype_tobalance_title }}</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" placeholder="0" id="rk_target_count" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <input type="submit" name="rk_submit" value="{{ language.usercontrol_profile_settings_balance_rktype_btn_pay }}" class="btn btn-success" />
                                <button type="button" class="btn btn-warning" data-dismiss="modal">{{ language.usercontrol_profile_settings_balance_cancel_btn }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{% endif %}
