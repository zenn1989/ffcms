<h2><a href="{$url}/news/{$news_full_link}">{$news_title}</a></h2>
<div>
	<div class="pull-left">{$lang::news_view_category}: <a href="{$url}/news/{$news_category_url}">{$news_category_text}</a></div>
	<div class="pull-right">{$lang::news_view_publish_date}: {$news_date}</div>
</div>
<hr />
<div>
{$news_text}
</div>
<br />
<div class="pull-left">
{$if com.news.tag && com.news.have_tag}
	{$lang::news_view_tags}: {$news_tag}
{$/if}
{$lang::news_view_author}: <a href="{$url}/user/id{$author_id}">{$author_nick}</a>
</div>
<div class="pull-right"><a href="{$url}/news/{$news_full_link}" class="btn btn-success">{$lang::news_view_button_more}</a></div>
<br />
<hr class="soft" />