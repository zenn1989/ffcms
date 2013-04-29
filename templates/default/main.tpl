<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>{$title}</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta name="keywords" content="{$keywords}" />
<meta name="description" content="{$description}" />
<meta content="ffcms" name="author" />
{$cssfile css/bootstrap.css}
{$cssfile css/bootstrap-responsive.css}
{$cssfile css/docs.css}
{$jsfile js/jquery-1.9.1.min.js}
{$jsfile js/bootstrap.js}
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
{$header}
<div class="container">
	<div class="row">
	<!-- Logotype -->
		<div class="span7">
			<a href="/">
			<img src="{$url}/{$tpl_dir}/img/logo.png" />
			</a>
		</div>
	<!-- /Logotype -->
	<!-- search menu -->
		<div class="span5" align="right">
			<form id="search" class="form-search">
			<input class="search-query" id="search-term" placeholder="Some data" type="text" />

			<button class="btn" id="search-submit" type="submit"> Search</button>
			</form>
			<script>
			$('#search').submit(function(e){
				var query = $('#search-term').val();
				window.location.replace("{$url}/search/"+query);
				return false;
			});
			</script>
		</div>
	<!-- /search menu -->
	</div>
	<!-- second navi menu -->
	<div class="subnav">
		{$mod_top_menu}
	</div>
	<!-- /second navi menu -->
	<div style="padding-top: 10px;"></div>
	<div class="row">
		{$left}
		<div class="span9">
			<div class="well well-500px">
				{$body}
			</div>
		</div>
		{$right}
	</div>
</div>
{$bottom}
<hr class="soften" />

{$footer}
</body>
</html>
