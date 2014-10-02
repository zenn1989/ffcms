<link href="{{ system.theme }}/css/morris.css" rel="stylesheet" />
<script src="{{ system.theme }}/js/raphael.js"></script>
<script src="{{ system.theme }}/js/morris.js"></script>
<h1>{{ language.admin_panel_header }} <small>{{ language.admin_panel_msg_welcome }}</small></h1>
<hr />
<div class="row">
    <div class="col-lg-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> {{ language.admin_msg_short_stat }} : {{ "now"|date('d-m-Y') }}</h3>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td>{{ local.stat.view_count }}</td>
                        <td>{{ language.admin_view_count_td }}</td>
                    </tr>
                    <tr>
                        <td>{{ local.stat.total_count }}</td>
                        <td>{{ language.admin_visiters_count_td }}</td>
                    </tr>
                    <tr>
                        <td>{{ local.stat.registered_count }}</td>
                        <td>{{ language.admin_visiters_reg_count_td }}</td>
                    </tr>
                    <tr>
                        <td>{{ local.stat.guest_count }}</td>
                        <td>{{ language.admin_visiters_noreg_count_td }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {{ language.admin_stats_graph_title }}
            </div>
            <div class="panel-body">
                <div id="week-stat" style="min-height: 170px;"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="panel panel-primary">
            <div class="panel-heading">{{ language.admin_block_info_system }}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <td>{{ language.admin_block_info_ostype }}</td>
                        <td>{{ local.stat.server_os_type }}</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_block_info_phpversion }}</td>
                        <td>{{ local.stat.server_php_ver }}</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_block_info_mysqlversion }}</td>
                        <td>{{ local.stat.server_mysql_ver }}</td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_block_info_loadavg }}</td>
                        <td>{{ local.stat.server_load_avg }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel panel-primary">
            <div class="panel-heading">{{ language.admin_block_info_directory }}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <td>/upload</td>
                        <td>{{ local.stat.folder_uploads_access }}[+rw]</td>
                    </tr>
                    <tr>
                        <td>/language</td>
                        <td>{{ local.stat.folder_language_access }}[+rw]</td>
                    </tr>
                    <tr>
                        <td>/cache</td>
                        <td>{{ local.stat.folder_cache_access }}[+rw]</td>
                    </tr>
                    <tr>
                        <td>/config.php</td>
                        <td>{{ local.stat.file_config_access }}[+rw]</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel panel-primary">
            <div class="panel-heading">{{ language.admin_block_info_about }}</div>
            <div class="panel-body">
                <p>{{ language.admin_block_info_descmsg }}</p>
                <table class="table">
                    <tr>
                        <td>{{ language.admin_block_info_officialsite }}</td>
                        <td><a href="http://ffcms.ru" target="_blank">www.ffcms.ru</a></td>
                    </tr>
                    <tr>
                        <td>GitHub</td>
                        <td><a href="https://github.com/zenn1989/ffcms/" target="_blank">/zenn1989/ffcms/</a></td>
                    </tr>
                    <tr>
                        <td>{{ language.admin_block_info_revision }}</td>
                        <td>{{ system.version }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-8">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {{ language.admin_block_info_logs_title }}
            </div>
            <div class="panel-body">
                {% if local.stat.logs %}
                <table class="table table-bordered">
                    {% for log in local.stat.logs %}
                    <tr>
                        <td>{{ log }}</td>
                    </tr>
                    {% endfor %}
                </table>
                {% else %}
                    <p>{{ language.admin_block_info_logs_nothing }}</p>
                {% endif %}
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                {{ language.admin_block_operation_title }}
            </div>
            <div class="panel-body">
                <a href="?object=cleancache" class="btn btn-success btn-block">{{ language.admin_block_operation_cacheclean }}</a> <br />
                <a href="?object=cleanstats" class="btn btn-warning btn-block">{{ language.admin_block_operation_statclean }}</a> <br />
                <a href="?object=cleanlogs" class="btn btn-info btn-block">{{ language.admin_block_operation_logclean }}</a>

            </div>
        </div>
    </div>
</div>

<script>
    Morris.Line({
        element: 'week-stat',
        data: [
            {% for stat in local.stat.graph_data %}
            { dateobject: '{{ stat.date }}', views: {{ stat.views }}, uniq: {{ stat.unique }} },
            {% endfor %}
        ],
        xkey: 'dateobject',
        ykeys: ['views', 'uniq'],
        labels: ['Views', 'Users']
    });
</script>