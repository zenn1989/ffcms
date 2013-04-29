	$(document).ready(function() {
		var subject_id;
		$('.answershow').click(function(e) {
			subject_id = e.target.id;
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