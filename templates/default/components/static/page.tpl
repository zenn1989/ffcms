<link rel="stylesheet" href="{{ system.script_url }}/resource/fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="{{ system.script_url }}/resource/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>
<script>
    $(document).ready(function() {
        $(".fancybox").fancybox({
            openEffect	: 'elastic',
            closeEffect	: 'elastic'
        });
    });
</script>
<article class="article-item" itemscope="itemscope" itemtype="{{ system.protocol }}://schema.org/Article">
    <h1 itemprop="name">{{ local.title }}</h1>
        <div class="meta">
            {% if local.show_date %}
            <span><i class="fa fa-calendar"></i>{{ local.date }}</span>
            {% else %}
            <span>&nbsp;</span>
            {% endif %}
            {% if not local.is_main %}
            <div class="pull-right">
                <a href="{{ local.pathway }}?print=true" target="_blank"><i class="fa fa-print"></i></a>
            </div>
            {% endif %}
        </div>
    <div itemprop="articleBody">
        {{ local.text }}
    </div>
</article>