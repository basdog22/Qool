<!DOCTYPE HTML>

<html>
	<head>
		<!--
		Minimaxing 1.0 by nodethirtythree + FCT
		http://nodethirtythree.com | @nodethirtythree
		Released under the Creative Commons Attribution 3.0 license (nodethirtythree.com/license)
		-->
		<title><?php site_title()?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<?php the_meta()?>
		
		
		<!--5grid-->
		<script src="<?php qoolinfo('home')?>/<?php $this->eprint($this->tplPath)?>/css/5grid/viewport.js"></script>
		<!--[if lt IE 9]><script src="<?php qoolinfo('home')?>/<?php $this->eprint($this->tplpath)?>/css/5grid/ie.js"></script><![endif]-->
		<link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php $this->eprint($this->tplpath)?>/css/5grid/core.css" />
		<link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php $this->eprint($this->tplpath)?>/css/style.css" />
		<!--[if IE 9]><link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php $this->eprint($this->tplpath)?>/css/style-ie9.css" /><![endif]-->
		<?php qool_header()?>
	</head>
	<body>
	<!-- ********************************************************* -->
		<div id="header-wrapper">
			<div class="5grid">
				<div class="12u-first">
					
					<header id="header">
						<h1><a href="<?php qoolinfo('home')?>"><?php site('frontend_title')?></a></h1>
						<?php menu("Main",array("wrap_menu"=>'nav',"wrap_link"=>'span','current_class'=>'current-page-item'))?>
						
					</header>
				
				</div>
			</div>
		</div>
		
		<?php include(get_the_include())?>
		
		<div id="footer-wrapper">
			<div class="5grid">
				<div class="8u-first">
					
					<section>
						<h2>Important Stuff</h2>
						<div class="3u-first">
							<?php widget('footer_1','h2')?>
						</div>
						<div class="3u">
							<?php widget('footer_2','h2')?>
						</div>
						<div class="3u">
							<?php widget('footer_3','h2')?>
						</div>
						<div class="3u">
							<?php widget('footer_4','h2')?>
						</div>
					</section>
				
				</div>
				<div class="4u">

					<section>
						<?php widget('top_box1','h2')?>
					</section>

				</div>
				<div class="12u-first">

					<div id="copyright">
						&copy; Qool CMS. All rights reserved. | Design: <a href="http://html5up.net">HTML5 Up!</a>
					</div>

				</div>
			</div>
		</div>
	<?php qool_footer()?>
	</body>
</html>