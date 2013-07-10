<div class="container">
    <div class="row">
        <div class="span12">
            <h3 style="text-align: center;">{$title}</h3>
            <ul class="nav nav-tabs" data-tabs="tabs">
                <li class="active"><a href="#allext" data-toggle="tab">{$word_all}</a></li>
                <li><a href="#activeext" data-toggle="tab">{$word_active}</a></li>
                <li><a href="#notactiveext" data-toggle="tab">{$word_noactive}</a></li>
                <li><a href="#toinstall" data-toggle="tab">{$word_toinstall}</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="allext">
                    {$all_list}
                </div>
                <div class="tab-pane" id="activeext">
                    {$active_list}
                </div>
                <div class="tab-pane" id="notactiveext">
                    {$notactive_list}
                </div>
                <div class="tab-pane" id="toinstall">
                    {$toinstall_list}
                </div>
            </div>
        </div>
    </div>
</div>