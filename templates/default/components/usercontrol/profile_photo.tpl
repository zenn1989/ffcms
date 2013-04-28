		<img src="{$url}/upload/user/avatar/big/{$user_avatar}" /><br />
		<ul class="nav nav-tabs nav-stacked">
			{$if user.auth && com.usercontrol.self_profile && com.usercontrol.menu_avatar}
			<li class="active"><a href="{$url}/settings/avatar" data-toggle="modal">{$lang::usercontrol_profile_mymenu_avachange}</a></li>
			{$/if}
			{$if user.auth && com.usercontrol.self_profile && !com.usercontrol.menu_avatar}
			<li><a href="{$url}/settings/avatar" data-toggle="modal">{$lang::usercontrol_profile_mymenu_avachange}</a></li>
			{$/if}
			{$if user.auth && com.usercontrol.self_profile && com.usercontrol.menu_message}
			<li class="active"><a href="{$url}/message">{$lang::usercontrol_profile_mymenu_personalmsg}</a></li>
			{$/if}
			{$if user.auth && com.usercontrol.self_profile && !com.usercontrol.menu_message}
			<li><a href="{$url}/message">{$lang::usercontrol_profile_mymenu_personalmsg}</a></li>
			{$/if}
			{$if user.auth && com.usercontrol.self_profile && com.usercontrol.menu_settings}
			<li class="active"><a href="{$url}/settings">{$lang::usercontrol_profile_mymenu_settings}</a></li> 
			{$/if}
			{$if user.auth && com.usercontrol.self_profile && !com.usercontrol.menu_settings}
			<li><a href="{$url}/settings">{$lang::usercontrol_profile_mymenu_settings}</a></li> 
			{$/if}
			{$if user.auth && !com.usercontrol.self_profile && com.usercontrol.in_friends}
			<li><a href="{$url}/message/write/{$target_user_id}">{$lang::usercontrol_profile_mymenu_writemsg}</a></li> 
			{$/if} 
			{$if user.auth && !com.usercontrol.self_profile && !com.usercontrol.in_friends && !com.usercontrol.in_friends_request}
			<form class="hidden" id="friendget" action="" method="post"><input type="hidden" name="requestfriend" value="1" /></form>
			<li><a onclick="document.getElementById('friendget').submit();">{$lang::usercontrol_profile_mymenu_addfriend}</a></li> 
			{$/if}
			{$if user.auth && !com.usercontrol.self_profile && !com.usercontrol.in_friends && com.usercontrol.in_friends_request}
			<li><a>{$lang::usercontrol_profile_mymenu_requestsended}</a></li>
			{$/if}
		</ul>