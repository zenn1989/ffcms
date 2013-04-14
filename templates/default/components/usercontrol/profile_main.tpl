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
		<div id="wall-jquery">{$lang::usercontrol_profile_wall_answer_load}</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('.answershow').click(function(e) {
			var subject_id = e.target.id;
			// загружаем сообщение + ответы на него по ajax отсылая get на api.php
			$.get('http://ffcms/api.php?u=1&action=readwall&id='+subject_id, function(data) {
				$('#wall-jquery').html(data);
			});
		})
	});
</script>