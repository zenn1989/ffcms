<div class="container">

    <div class="row">

        <div class="span4">
            <h3>{$lang::admin_header_return}</h3>

            <p>{$lang::admin_msg_short_stat} - {$date_today}.</p>

            <table class="table stat-table">
                <tbody>
                <tr>
                    <td class="value">{$view_count}</td>
                    <td class="full">{$lang::admin_view_count_td}</td>
                </tr>
                <tr>
                    <td class="value">{$user_unique}</td>
                    <td class="full">{$lang::admin_visiters_count_td}</td>
                </tr>
                <tr>
                    <td class="value">{$unique_registered}</td>
                    <td class="full">{$lang::admin_visiters_reg_count_td}</td>
                </tr>
                <tr>
                    <td class="value">{$unique_unregistered}</td>
                    <td class="full">{$lang::admin_visiters_noreg_count_td}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <!-- /.span4 -->

        <div class="span8">
            <div id="week-chart" class="chart-holder"></div>
            <!-- /#bar-chart -->
        </div>
        <!-- /.span8 -->

    </div>
    <!-- /.row -->

    <div class="row">

        <div class="span4">

            <h3 class="title">{$lang::admin_block_info_system}</h3>

            <table class="table">
                <tr>
                    <td>{$lang::admin_block_info_ostype}</td>
                    <td>{$server_os_type}</td>
                </tr>
                <tr>
                    <td>{$lang::admin_block_info_phpversion}</td>
                    <td>{$server_php_ver}</td>
                </tr>
                <tr>
                    <td>{$lang::admin_block_info_mysqlversion}</td>
                    <td>{$server_mysql_ver}</td>
                </tr>
                <tr>
                    <td>{$lang::admin_block_info_loadavg}</td>
                    <td>{$server_load_avg}</td>
                </tr>
            </table>

        </div>
        <!-- /.span4 -->

        <div class="span4">

            <h3 class="title">{$lang::admin_block_info_directory}</h3>

            <table class="table">
                <tr>
                    <td>/uploads</td>
                    <td>{$folder_uploads_access}(+rw)</td>
                </tr>
                <tr>
                    <td>/language</td>
                    <td>{$folder_language_access}(+rw)</td>
                </tr>
                <tr>
                    <td>/cache</td>
                    <td>{$folder_cache_access}(+rw)</td>
                </tr>
                <tr>
                    <td>/config.php</td>
                    <td>{$file_config_access}(+rw)</td>
                </tr>
            </table>

        </div>
        <!-- /.span4 -->

        <div class="span4">

            <h3 class="title">{$lang::admin_block_info_about}</h3>

            <p>{$lang::admin_block_info_descmsg}</p>
            <table class="table">
                <tr>
                    <td>{$lang::admin_block_info_officialsite}</td>
                    <td><a href="http://ffcms.ru">www.ffcms.ru</a></td>
                </tr>
                <tr>
                    <td>GitHub</td>
                    <td><a href="https://github.com/zenn1989/ffcms/">ffcms</a></td>
                </tr>
                <tr>
                    <td>{$lang::admin_block_info_revision}</td>
                    <td>0.1</td>
                </tr>
            </table>
        </div>
        <!-- /.span4 -->

    </div>
    <!-- /.row -->

</div>
<!-- /.container -->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {
        packages: [ "corechart" ]
    });
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Day', 'View', 'Users'],
            {$json_chart_result}
        ]);

        var options = {
            title: 'Site stat'
        };

        var chart = new google.visualization.LineChart(document
                .getElementById('week-chart'));
        chart.draw(data, options);
    }
</script>