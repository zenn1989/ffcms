		<img src="{$url}/upload/user/avatar/big/{$user_avatar}" /><br />
		<ul class="nav nav-tabs nav-stacked">
			{$if user.auth && com.usercontrol.self_profile}
			<div id="changeavatar" class="modal hide fade">
			<form action="" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>{$lang::usercontrol_profile_photo_title}</h3>
				</div>
				<div class="modal-body">
				
				<input type="file" name="avatar" />
				
				</div>
				<div class="modal-footer">
					<input type="submit" name="avatarupdate" value="{$lang::global_send_button}" class="btn btn-success" />
				</div>
			</form>
			</div>
			<li><a href="#changeavatar" data-toggle="modal">{$lang::usercontrol_profile_mymenu_avachange}</a></li>
			<li><a href="#">{$lang::usercontrol_profile_mymenu_personalmsg}</a></li>
			<li><a href="#">{$lang::usercontrol_profile_mymenu_settings}</a></li> 
			{$/if}
			{$if user.auth && !com.usercontrol.self_profile && com.usercontrol.in_friends}
			<li><a href="#">{$lang::usercontrol_profile_mymenu_writemsg}</a></li> 
			{$/if} 
			{$if user.auth && !com.usercontrol.self_profile && !com.usercontrol.in_friends && !com.usercontrol.in_friends_request}
			<form class="hidden" id="friendget" action="" method="post"><input type="hidden" name="requestfriend" value="1" /></form>
			<li><a onclick="document.getElementById('friendget').submit();">{$lang::usercontrol_profile_mymenu_addfriend}</a></li> 
			{$/if}
			{$if user.auth && !com.usercontrol.self_profile && !com.usercontrol.in_friends && com.usercontrol.in_friends_request}
			<li><a>{$lang::usercontrol_profile_mymenu_requestsended}</a></li>
			{$/if}
		</ul>