<div id="header">
	
	<div class="container">
		
		<a href="?" class="brand"></a>
	
		<div class="nav-collapse">
			<ul id="main-nav" class="nav pull-right">
				<li class="nav-icon active">
					<a href="?"><i class="icon-home"></i> {$lang::admin_main_link}</a>
				</li>
				
				<li class="dropdown">					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-fire"></i>
						<span>{$lang::admin_nav_system}</span> 
						<b class="caret"></b>
					</a>	
				
					<ul class="dropdown-menu">							
						<li><a href="?object=settings">{$lang::admin_nav_li_settings}</a></li>
						<li><a href="?object=clear">{$lang::admin_nav_li_clear}</a></li>
						<li><a href="?object=antivirus">{$lang::admin_nav_li_avir}</a></li>
						<li><a href="?object=dump">{$lang::admin_nav_li_backup}</a></li>
					</ul>    				
				</li>
				
				<li class="dropdown">					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-th"></i>
						<span>{$lang::admin_nav_modules}</span> 
						<b class="caret"></b>
					</a>	
				
					<ul class="dropdown-menu">
						{$module_list}
					</ul>
				</li>
				
				<li class="dropdown">					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-copy"></i>
						<span>{$lang::admin_nav_components}</span> 
						<b class="caret"></b>
					</a>	
				
					<ul class="dropdown-menu">
						{$component_list}
					</ul>    				
				</li>
				
				<li class="dropdown">					
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
						<i class="icon-external-link"></i>
						<span>{$lang::admin_nav_hooks}</span> 
						<b class="caret"></b>
					</a>	
				
					<ul class="dropdown-menu">							
						{$hook_list}
					</ul>    				
				</li>
			</ul>
			
		</div> <!-- /.nav-collapse -->

	</div> <!-- /.container -->
	
</div> <!-- /#header -->