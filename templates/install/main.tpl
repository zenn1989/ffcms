<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="{$url}/{$tpl_dir}/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        background: url("{$url}/{$tpl_dir}/img/background.jpg") fixed;
        padding-top: 90px; /* 60px to make the container go all the way to the bottom of the topbar */
		background-color: black;
        background-repeat: no-repeat;
        background-position: top;
      }
    </style>
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="{$url}/{$tpl_dir}/js/html5shiv.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="{$url}/install/">FFCMS</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="{$url}/install/?action=install">{$lang::install_mainahref}</a></li>
              <li><a href="{$url}/install/?action=update">{$lang::install_updatehref}</a></li>
              <li><a href="http://ffcms.ru" target="_blank">{$lang::install_devlink}</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row">
		<div class="span12">
		<div class="span2"></div>
		<div class="span8">
			<div class="well" style="min-height: 600px;">
				<h4>Установщик системы и обновлений ffcms</h4>
                <small class="pull-right">Версия: 0.1 developer preview</small><br />
				<hr />

			    {$body}
			</div>
		</div>
		<div class="span2"></div>
		</div>
	  </div>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="{$url}/{$tpl_dir}/js/jquery.js"></script>
    <script src="{$url}/{$tpl_dir}/js/bootstrap.min.js"></script>


  </body>
</html>
