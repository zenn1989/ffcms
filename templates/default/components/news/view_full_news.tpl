<h2>{$news_title}</h2>
<div>
    {$if com.news.have_category}
    <div class="pull-left">{$lang::news_view_category}: <a
                href="{$url}/news/{$news_category_url}/">{$news_category_text}</a></div>
    {$/if}
    {$if !com.news.have_category}
    <div class="pull-left">{$lang::news_view_category}: <a href="{$url}/news/">{$lang::news_view_category_unset}</a>
    </div>
    {$/if}
    <div class="pull-right">
        {$lang::news_view_publish_date}: {$news_date}
    </div>
</div>
<hr/>
<div>
    {$news_text}
</div>
<hr/>
{$if com.news.tag}
<div class="pull-left">{$lang::news_view_tags}: {$news_tag}</div>
{$/if}
<div class="pull-right">
    {$lang::news_view_author}: <a href="{$url}/user/id{$author_id}">{$author_nick}</a>
    {$if com.news.view_count}{$lang::news_view_countviews}: {$news_view_count} {$/if}
</div>
<!-- COMMENT FORM & LIST -->
<br/><br/>
<ul class="nav nav-tabs">
    <li class="active"><a href="#comment_site" data-toggle="tab">{$lang::comments_text_title}</a></li>
    <!-- VK comment example :
    <li><a href="#comment_vk" data-toggle="tab">Vkontakte</a></li>
    -->
</ul>
{$jsapi api.php?action=js&dir=js&name=comment_ajax}
<script>
    var comment_object = null;
    var comment_id = null;
    var comment_hash = null;
    var comment_pathway = null;
    var current_point = 0;
    comment_object = '{$js.comment_object}';
    comment_id = {$js.comment_id};
    comment_hash = '{$js.comment_hash}';
    comment_pathway = '{$js.comment_strpathway}';
</script>
<div class="tab-content">
    <div class="tab-pane active" id="comment_site">
        {$com.comment_form}
        <div id="comment_list">{$com.comment_list}</div>
        <div id="loader_comment">
            <div id="comment_load">
                <table class="table">
                    <tr>
                        <td style="text-align: center;" class="alert alert-success"><a href="#comment_load" id="doLoadComment">{$lang::comments_text_loadmore}</a></blockquote></td>
                        <td style="text-align: center;" class="alert alert-error"><a href="#comment_load" id="doLoadAllComment">{$lang::comments_text_loadall}</a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <!--
    <div class="tab-pane" id="comment_vk">Code vk.com here</div>
    -->
</div>
<!-- /END COMMENT FORM & LIST -->