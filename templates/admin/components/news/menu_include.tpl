<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{{ language.admin_menu_word }}</a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li><a href="?object=components&action=news">{{ language.admin_component_news_manage }}</a></li>
            <li><a href="?object=components&action=news&make=add">{{ language.admin_component_news_add }}</a></li>
            <li><a href="?object=components&action=news&make=category">{{ language.admin_component_news_category }}</a></li>
            <li><a href="?object=components&action=news&make=settings">{{ language.admin_component_news_settings }}</a></li>
        </ul>
    </div>
</nav>