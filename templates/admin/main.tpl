<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<title>{$lang::admin_page_title}</title>
	
	<meta charset="utf-8">
	
	<meta name="author" content="zenn">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	
	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/font-awesome.css">
	
	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/bootstrap.css">
	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/bootstrap-responsive.css">

	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/ui-lightness/jquery-ui-1.8.21.custom.css">	
	
	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/application.css">
	<link rel="stylesheet" href="{$url}/{$tpl_dir}/css/pages/dashboard.css">

	<script src="{$url}/{$tpl_dir}/js/libs/modernizr-2.5.3.min.js"></script>

</head>

<body>
	
<div id="wrapper">
	
<div id="topbar">
	
	<div class="container">

		<div id="top-nav">
			
			<ul>
				<li class="dropdown">
					<a href="{$url}" target="_blank">{$lang::admin_open_site_link}</a>
					 <i class="icon-arrow-up"></i>
				</li>
			</ul>
			
			<ul class="pull-right">
				<li><i class="icon-user"></i> {$lang::admin_welcome_msg} {$username}</li>
				<li><a href="{$url}/logout">{$lang::admin_exit_link}</a></li>
			</ul>
			
		</div> <!-- /#top-nav -->
		
	</div> <!-- /.container -->
	
</div> <!-- /#topbar -->

{$header}

<div id="masthead">
	
	<div class="container">
		
		<div class="masthead-pad">
			
			<div class="masthead-text">
				<h2>{$lang::admin_panel_header}</h2>
				<p>{$lang::admin_panel_msg_welcome}</p>
			</div> <!-- /.masthead-text -->
			
		</div>
		
	</div> <!-- /.container -->	
	
</div> <!-- /#masthead -->




<div id="content">
	{$body}
</div> <!-- /#content -->

</div> <!-- /#wrapper -->




<div id="footer">
		
	<div class="container">
		
		<div class="row">
			
			<div class="span6">
				© 2012 <a href="http://ffcms.ru">ffcms</a>, all rights reserved.
			</div> <!-- /span6 -->

		</div> <!-- /row -->
		
	</div> <!-- /container -->
	
</div> <!-- /#footer -->





<script src="{$url}/{$tpl_dir}/js/libs/jquery-1.7.2.min.js"></script>
<script src="{$url}/{$tpl_dir}/js/libs/jquery-ui-1.8.21.custom.min.js"></script>

<script src="{$url}/{$tpl_dir}/js/libs/bootstrap/bootstrap.min.js"></script>

</body>
</html>