{$user_header}
<hr />
<div class="row">
	<div class="span3">
		{$user_photo_control}
	</div>
	<div class="span5">
		<div class="tabbable">
		{$user_menu}
		{$user_main_block}
		</div>
	</div>
</div>
<div id="readanswer" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>{$lang::usercontrol_profile_wall_answer_head}</h3>
	</div>
	<div class="modal-body">
		{$if user.auth}
		<div id="requestpost">
		<textarea class="input-block-level" id="answer"></textarea>
		<div class="pull-right"><a href="#" id="addanswer" class="btn btn-success">{$lang::global_send_button}</a></div><br />
		</div>
		{$/if}
		<hr />
		<div id="wall-jquery">{$lang::usercontrol_profile_wall_answer_load}</div>
	</div>
</div>
{$jsapi api.php?action=js&dir=js&name=profile_ajax}