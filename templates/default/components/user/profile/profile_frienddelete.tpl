<h4>{{ language.usercontrol_profile_frienddelete_title }}</h4>
<p>{{ language.usercontrol_profile_frienddelete_desc }}</p>
<div class="media">
    <a class="pull-left" href="{{ system.url }}/user/id{{ friend.user_id }}">
        <img style="max-width: 64px;max-height: 64px;" class="media-object" src="{{ system.script_url }}/{{ local.friend.delete.user_avatar }}">
    </a>
    <div class="media-body">
        <h4 class="media-heading">{{ local.friend.delete.user_name|escape|default('Deleted...') }}</h4>
        <form action="" method="post">
            <input type="submit" name="delete" value="{{ language.usercontrol_profile_frienddelete_buttondel }}" class="btn btn-danger" />
        </form>
    </div>
</div>