<div class="pull-right"><form action="" method="post" class="form-search">
	<label><input type="text" name="search" placeHolder="Data..." value="{$ext_search_value}" class="input-medium search-query" /> <input value="{$lang::admin_component_static_search_button}" type="submit" name="dosearch" class="btn" /></label>
</form></div>
{$ext_table}
<div class="pagination pagination-centered">
  <ul>
  	<li class="disabled"><a href="#">{$lang::admin_extension_pagination_word}: </a></li>
    {$ext_pagination_list}
  </ul>
</div>