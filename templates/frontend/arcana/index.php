<!DOCTYPE HTML>
<html>
	<head>
	<!--
	Arcana 1.0 by HTML5 Up!
	html5up.net | @nodethirtythree
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
	-->
		<title><?php site_title()?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		
		<?php the_meta()?>
		<?php arcana_favicon();?>
		<?php arcana_css()?>
		<?php qool_header()?>
	</head>
	<body>
	<!-- Header -->
			<div id="header-wrapper">
				<header class="5grid-layout" id="site-header">
					<div class="row">
						<div class="12u">
							<div class="mobileUI-site-name" id="logo">
								<h1><?php site('frontend_title')?></h1>
							</div>
							<nav class="mobileUI-site-nav" id="site-nav">
							<?php menu('Main',array('wrap_menu'=>'ul','wrap_link'=>'li','current_class'=>'current_page_item','class'=>'menu_item'))?>
							</nav>
						</div>
					</div>
				</header>
			</div>

		<!-- Main -->

			<div id="main-wrapper">
				<div class="5grid-layout">
					<?php include(get_the_include())?>
				</div>
			</div>

		<!-- Footer -->

			<div id="footer-wrapper">
				<footer class="5grid-layout" id="site-footer">
					<div class="row">
						<div class="3u">
							<section class="first">
								<?php widget('footer_1','h2')?>
							</section>
						</div>
						<div class="3u">
							<section>
								<?php widget('footer_2','h2')?>
							</section>
						</div>
						<div class="3u">
							<section>
								<?php widget('footer_3','h2')?>
							</section>
						</div>
						<div class="3u">
							<section class="last">
								<?php widget('footer_4','h2')?>
							</section>
						</div>
					</div>
					<div class="row">
						<div class="12u">
							<div class="divider"></div>
							
						</div>
					</div>
					
					<div class="row">
						<div class="12u">
							<div id="copyright">
								&copy; 20[0-9]{2} <a href="http://www.qool.gr/">Qool CMS</a>. All rights reserved. | Design: <a target="_blank" rel="nofollow" href="http://html5up.net">HTML5 Up!</a>
							</div>
						</div>
					</div>
				</footer>
			</div>
<?php qool_footer()?>
	</body>
</html>