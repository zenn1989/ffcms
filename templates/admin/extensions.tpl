<h1>{{ page.title }}</h1>
<hr />
<ul class="nav nav-tabs nav-justified">
    <li class="active"><a href="#all" data-toggle="tab">{{ language.admin_extension_view_all }}</a></li>
    <li><a href="#active" data-toggle="tab">{{ language.admin_extension_view_enabled }}</a></li>
    <li><a href="#disabled" data-toggle="tab">{{ language.admin_extension_view_disabled }}</a></li>
    <li><a href="#noinstall" data-toggle="tab">{{ language.admin_extension_view_noinstall }}</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="all">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ language.admin_extension_table_name }}</th>
                    <th>{{ language.admin_extension_table_desc }}</th>
                    <th>{{ language.admin_extension_table_action }}</th>
                </tr>
                </thead>
                <tbody>
                {% for name,param in extension %}
                    <tr>
                        <td>{{ loop.index }}</td>
                        <td>
                            <a href="?object={{ type }}&action={{ name }}">{{ param.title }}</a>
                            {% if param.way != null %}<a href="{{ system.url }}/{{ param.way }}/" target="_blank"><label class="label label-info">/{{ param.way }}/</label></a>{% endif %}
                            {% if param.type == 'modules' %}
                                {% if param.path_choice == 1 %} {# its a success URI path choice #}
                                    <a href="?object={{ type }}&action={{ name }}&sys=pathwork"><label class="label label-success">{{ param.path_allow }}</label></a>
                                {% elseif param.path_choice == 0 %} {# its a deny URI path choice #}
                                    <a href="?object={{ type }}&action={{ name }}&sys=pathwork"><label class="label label-danger">{{ param.path_deny }}</label></a>
                                {% endif %}
                            {% endif %}
                        </td>
                        <td>{{ param.desc }}</td>
                        <td class="text-center"><a href="?object={{ type }}&action={{ name }}"><i class="fa fa-cogs"></i></a> <a href="?object={{ type }}&action={{ name }}&sys=info"><i class="fa fa-info-circle"></i></a></td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane" id="active">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ language.admin_extension_table_name }}</th>
                    <th>{{ language.admin_extension_table_desc }}</th>
                    <th>{{ language.admin_extension_table_action }}</th>
                </tr>
                </thead>
                <tbody>
                {% for name,param in extension %}
                    {% if param.enabled == 1 %}
                        <tr>
                            <td>{{ loop.index }}</td>
                            <td><a href="?object={{ type }}&action={{ name }}">{{ param.title }}</a></td>
                            <td>{{ param.desc }}</td>
                            <td class="text-center"><a href="?object={{ type }}&action={{ name }}"><i class="fa fa-cogs"></i></a> <a href="?object={{ type }}&action={{ name }}&sys=disable"><i class="fa fa-power-off"></i></a></td>
                        </tr>
                    {% endif %}
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane" id="disabled">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ language.admin_extension_table_name }}</th>
                    <th>{{ language.admin_extension_table_desc }}</th>
                    <th>{{ language.admin_extension_table_action }}</th>
                </tr>
                </thead>
                <tbody>
                {% for name,param in extension %}
                    {% if param.enabled != 1 %}
                        <tr>
                            <td>{{ loop.index }}</td>
                            <td>{{ param.title }}</td>
                            <td>{{ param.desc }}</td>
                            <td class="text-center"><a href="?object={{ type }}&action={{ name }}&sys=enable"><i class="fa fa-refresh"></i></a></td>
                        </tr>
                    {% endif %}
                {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane" id="noinstall">
        {% if noinstall %}
            <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ language.admin_extension_table_name }}</th>
                        <th>{{ language.admin_extension_table_action }}</th>
                    </tr>
                </thead>
                <tbody>
                {% for install in noinstall %}
                    <tr>
                        <td>{{ loop.index }}</td>
                        <td>{{ install }}</td>
                        <td class="text-center"><a href="?object={{ type }}&action={{ install }}&sys=install"><i class="fa fa-plus-circle"></i></a></td>
                    </tr>
                {% endfor %}
                </tbody>
            </table>
            </div>
        {% else %}
             <p class="alert alert-warning">No extensions</p>
        {% endif %}
    </div>
</div>