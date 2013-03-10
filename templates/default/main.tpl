<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>{$title}</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta name="keywords" content="{$keywords}" />
<meta name="description" content="{$description}" />
<meta content="ffcms" name="author" />
<link href="{$url}/{$tpl_dir}/css/bootstrap.css" rel="stylesheet" />
<link href="{$url}/{$tpl_dir}/css/bootstrap-responsive.css" rel="stylesheet" />
<link href="{$url}/{$tpl_dir}/css/docs.css" rel="stylesheet" />
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<!-- {header} menu -->
{$header}
<!-- / {header} -->

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
			<form class="form-search">
			<input class="search-query" id="search-term" placeholder="Some data" type="text" />

			<button class="btn" id="search-submit" type="submit"> Search</button>
			</form>
		</div>
	<!-- /search menu -->
	</div>
	<!-- second navi menu -->
	<div class="subnav">
		{$mod_top_menu}
	</div>
	<!-- /second navi menu -->
	<div style="padding-top: 15px;"></div>
	<div class="row">
		{$left}
		<div class="span9">
			<div class="well">
				{$body}
			</div>
		</div>
		{$right}
	</div>
</div>
{$bottom}
<hr class="soften" />

{$footer}
<script src="{$url}/{$tpl_dir}/js/jquery-1.9.1.min.js" type="text/javascript"></script>
<script src="{$url}/{$tpl_dir}/js/bootstrap.js" type="text/javascript"></script>

</body>
</html>
