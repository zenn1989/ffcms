<h2>{$news_title}</h2>
<div>
	{$if com.news.have_category}
	<div class="pull-left">{$lang::news_view_category}: <a href="{$url}/news/{$news_category_url}/">{$news_category_text}</a></div>
	{$/if}
	{$if !com.news.have_category}
	<div class="pull-left">{$lang::news_view_category}: <a href="{$url}/news/">{$lang::news_view_category_unset}</a></div>
	{$/if}
	<div class="pull-right">{$lang::news_view_publish_date}: {$news_date}</div>
</div>
<hr />
<div>
{$news_text}
</div>
<hr />
{$if com.news.tag && com.news.have_tag}
<div class="pull-left">{$lang::news_view_tags}: {$news_tag}</div>
{$/if}
<div class="pull-right">{$lang::news_view_author}: <a href="{$url}/user/id{$author_id}">{$author_nick}</a></div>
