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
<article class="article-item">
    <h1>{{ local.title }}</h1>
    {% if local.show_date %}
        <div class="meta">
            <span> </span>
            <div class="pull-right">
                <span><i class="fa fa-calendar"></i>{{ local.date }}</span>
            </div>
        </div>
    {% else %}
        <hr />
    {% endif %}
    {{ local.text }}
</article>