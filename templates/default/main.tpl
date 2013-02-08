<html>
<head>
<meta charset="utf-8" />
<title>FFCms page example</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta name="keywords" content="" />
<meta content="" name="author" />
<link href="{$url}/{$tpl_dir}/css/bootstrap.css" rel="stylesheet" />
<link href="{$url}/{$tpl_dir}/css/bootstrap-responsive.css" rel="stylesheet" />
<link href="{$url}/{$tpl_dir}/css/docs.css" rel="stylesheet" />
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<!-- {$header} menu, position: $template->header[0] = parrent; -->
<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<div class="nav-collapse">
				<ul class="nav">
					<li><a href="#"> Menu</a></li>

					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Dropdown Menu
						<b class="caret"></b>
						</a>

						<ul class="dropdown-menu">
							<li>
							<a href="#"> Menu</a>
							</li>

							<li>
							<a href="#"> Menu</a>
							</li>
						</ul>
					</li>

					<li><a href="#"> Menu</a></li>

					<li><a href="#"> Menu</a></li>
				</ul>
				<ul class="nav pull-right">
					<li class="navbar-text"><a href="#"><strong> Register</strong></a></li>
					<li class="navbar-text"><a href="#"><strong> Login</strong></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- / {$header} -->

<div class="container">
	<div class="row">
	<!-- Logotype -->
		<div class="span7">
			<a href="/">
			<img src="{$url}/{$tpl_dir}/img/PageData-Banner.png" />
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
		<div class="span3" id="lnav">
			<div class="well">
				<ul class="nav nav-list">
				<li class="nav-header"> Block example</li>
				<li><a href="#">Link #1</a></li>
				<li><a href="#">Link #2</a></li>
				<li><a href="#">Link #3</a></li>
				<li><a href="#">Link #4</a></li>
				<li>
					<ul>
						<li><a href="#">Link new</a></li>
						<li><a href="#">Link new</a></li>
						<li><a href="#">Link new</a></li>
						
					</ul>
				</li>
				</ul>
			</div>
		</div>
		<div class="span9" id="body">
			{$body}
		</div>
	</div>
</div>
<hr class="soften" />

<footer style="text-align: center;padding-bottom: 15px;">Powered by ffcms</footer>
<script src="{$url}/{$tpl_dir}/js/jquery-1.9.1.min.js" type="text/javascript"></script>
<script src="{$url}/{$tpl_dir}/js/bootstrap.js" type="text/javascript"></script>

</body>
</html>
