<h2><a href="{$url}/news/{$news_full_link}">{$news_title}</a></h2>
<div>
    <div class="pull-left">{$lang::news_view_category}: <a
                href="{$url}/news/{$news_category_url}">{$news_category_text}</a></div>
    <div class="pull-right"><i class="icon-repeat"></i> {$lang::news_view_publish_date}: {$news_date}</div>
</div>
<hr/>
<div>
    {$news_text}
</div>
<br/>
<div class="pull-left">
    {$if com.news.tag}<i class="icon-tags"></i> {$lang::news_view_tags}: {$news_tag} {$/if}
    <i class="icon-pencil"></i> {$lang::news_view_author}: <a href="{$url}/user/id{$author_id}">{$author_nick}</a>
</div>
<div class="pull-right">
    {$if com.news.view_count}<i class="icon-eye-open"></i> {$lang::news_view_countviews}: {$news_view_count} {$/if}
    <a href="{$url}/news/{$news_full_link}#comment_load"><i class="icon-comment"></i>
        {$lang::comments_text_title}: {$news_comment_count}</a> <a href="{$url}/news/{$news_full_link}">{$lang::news_view_button_more} <i
                class="icon-share-alt"></i></a>
</div>
<br/>
<hr class="soft"/>