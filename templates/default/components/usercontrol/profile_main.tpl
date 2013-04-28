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
<script>
	$(document).ready(function() {
		var subject_id;
		$('.answershow').click(function(e) {
			subject_id = e.target.id;
			// загружаем сообщение + ответы на него по ajax отсылая get на api.php
			$.get('{$url}/api.php?action=readwall&id='+subject_id, function(data) {
				$('#wall-jquery').html(data);
			});
		});
		$('#addanswer').click(function(e){
			var answer_text = $('#answer').val();
			if(answer_text.length > 0)
			{
				$.post('{$url}/api.php?action=postwall&id='+subject_id, { message : answer_text}, function(data) {
					$('#wall-jquery').html(data);
				});
				$('#answer').val(null);
			}
		});
	});
</script>