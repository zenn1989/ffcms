<ul class="pager">
	{$if com.usercontrol.have_previous}
	<li class="previous"><a href="{$url}/user/id{$target_user_id}/marks/{$mark_prev}">&larr;</a></li>
	{$/if} 
	{$if com.usercontrol.have_next}
	<li class="next"><a	href="{$url}/user/id{$target_user_id}/marks/{$mark_next}">&rarr;</a></li>
	{$/if}
</ul>
<table class='table table-striped'>
	<tbody>
	{$marks_body}
	</tbody>
</table>