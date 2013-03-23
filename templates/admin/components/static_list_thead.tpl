<div class="pull-right"><form action="" method="post" class="form-search">
	<label><input type="text" name="search" placeHolder="Data..." value="{$ext_search_value}" class="input-medium search-query" /> <input value="{$lang::admin_component_static_search_button}" type="submit" name="dosearch" class="btn" /></label>
</form></div>
<table class="table table-bordered">
	<thead>
		<tr>
			<th>{$lang::admin_component_static_th_id}</th>
			<th>{$lang::admin_component_static_th_title}</th>
			<th>{$lang::admin_component_static_th_path}</th>
			<th>{$lang::admin_component_static_th_edit}</th>
		</tr>
	</thead>
	<tbody>
		{$ext_body_result}
	</tbody>
</table>
<div class="pagination pagination-centered">
  <ul>
  	<li class="disabled"><a href="#">Страницы: </a></li>
    {$ext_pagination_list}
  </ul>
</div>