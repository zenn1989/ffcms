<ul class="nav nav-tabs">
	{$if com.usercontrol.menu_wall}
	<li class="active"><a href="{$url}/user/id{$target_user_id}"><i class="icon-home"></i> {$lang::usercontrol_profile_menu_wall}</a></li>
	{$/if}
	{$if !com.usercontrol.menu_wall}
	<li><a href="{$url}/user/id{$target_user_id}"><i class="icon-home"></i> {$lang::usercontrol_profile_menu_wall}</a></li>
	{$/if}
	{$if com.usercontrol.menu_mark}
	<li class="active"><a href="{$url}/user/id{$target_user_id}/marks"><i class="icon-pencil"></i> {$lang::usercontrol_profile_menu_marks}</a></li>
	{$/if}
	{$if !com.usercontrol.menu_mark}
	<li><a href="{$url}/user/id{$target_user_id}/marks"><i class="icon-pencil"></i> {$lang::usercontrol_profile_menu_marks}</a></li>
	{$/if}
	{$if com.usercontrol.menu_friends}
	<li class="active"><a href="{$url}/user/id{$target_user_id}/friends"><i class="icon-user"></i> {$lang::usercontrol_profile_menu_friends}</a></li>
	{$/if}
	{$if !com.usercontrol.menu_friends}
	<li><a href="{$url}/user/id{$target_user_id}/friends"><i class="icon-user"></i> {$lang::usercontrol_profile_menu_friends}</a></li>
	{$/if}
	{$if com.usercontrol.menu_dropdown && com.usercontrol.menu_dropdown_notempty}
	<li class="dropdown active">
	<a href="#" data-toggle="dropdown"><i class="icon-share-alt"></i> {$lang::usercontrol_profile_menu_more}<b class="caret"></b></a>
		<ul class="dropdown-menu">
			{$additional_hook_list}
		</ul>
	</li>
	{$/if}
	{$if !com.usercontrol.menu_dropdown && com.usercontrol.menu_dropdown_notempty}
	<li class="dropdown">
	<a href="#" data-toggle="dropdown"><i class="icon-share-alt"></i> {$lang::usercontrol_profile_menu_more}<b class="caret"></b></a>
		<ul class="dropdown-menu">
			{$additional_hook_list}
		</ul>
	</li>
	{$/if}
</ul>