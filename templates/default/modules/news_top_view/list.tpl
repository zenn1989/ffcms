<div class="panel panel-default">
    <div class="panel-body">
        <h4>{{ language.viewnews_title }}</h4>
        {% for item in local.top %}
            <a href="{{ item.pathway }}">{{ item.title }}</a> <div class="pull-right">{{ item.views }} <i class="fa fa-eye"></i></div>
            <hr class="commenttype" />
        {% else %}
            {{ language.viewnews_empty }}
        {% endfor %}
    </div>
</div>