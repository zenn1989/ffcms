    function replayto(username)
    {
        $(document).ready(function() {
            var comment_value = $('#comment_value').val();
            $('#comment_value').val('[b]'+username + '[/b], ' + comment_value);
        });
    }
    $(document).ready(function() {
		$('#doLoadComment').click(function(e) {
            current_point++;
            $.post('{$url}/api.php?action=viewcomment',
            {object : comment_object, id : comment_id, hash : comment_hash, comment_position : current_point },
            function(data) {
                $('#comment_list').html(data);
            });
        });
		$('#comment_send').click(function(e){
            $('#comment_value').sync();
			var comment_text = $('#comment_value').val();
			if(comment_text.length > 0)
			{
				$.post('{$url}/api.php?action=postcomment',
                { comment_message : comment_text, object : comment_object, id : comment_id, hash : comment_hash, comment_position : current_point },
                function(data) {
					$('#comment_list').html(data);
				});
				$('#comment_value').val(null);
			}
		});
	});