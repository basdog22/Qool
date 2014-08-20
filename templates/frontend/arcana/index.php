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
		<link href="http://www.qool.gr/templates/frontend/arcana/pret/prettify.css" type="text/css" rel="stylesheet" />
		<script type="text/javascript" src="http://www.qool.gr/templates/frontend/arcana/pret/prettify.js"></script>
		<?php qool_header()?>
		<script>
		function jprety(){
			$("pre").addClass('prettyprint');
			prettyPrint();
		}
		</script>
	</head>
	<body onload="jprety()">
	<script type="text/javascript">

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-8238354-2']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

</script>
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
						<div class="4u">
							<div class="g-plusone" data-annotation="inline" data-width="300"></div>
						</div>
						<div class="4u">
							<div class="fb-like" data-href="http://www.facebook.com/pages/Qool-CMS/559008654112575" data-send="true" data-width="280" data-show-faces="false" data-font="segoe ui"></div>
						</div>
						<div class="4u" style="text-align:right">
							<a href="https://twitter.com/share" class="twitter-share-button last" data-via="QoolCMS" data-lang="el" data-size="large" data-hashtags="CMS">Qool CMS</a>
						</div>
					</div>
					<div class="row">
						<div class="12u">
							<div id="copyright">
								&copy; 20[0-9]{2} <a href="http://www.qool.gr/">Qool CMS</a>. All rights reserved. | Design: <a target="_blank" rel="nofollow" href="http://html5up.net">HTML5 Up!</a> | <a href="http://www.bookland.gr/">Books</a>
							</div>
						</div>
					</div>
				</footer>
			</div>
			<link rel="publisher" href="https://plus.google.com/b/114452869554442850033/114452869554442850033"/>
			<link href="https://plus.google.com/101007547568982500066?rel=author"/>
<script type="text/javascript">
(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script>
<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/el_GR/all.js#xfbml=1&appId=230562596993743";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<?php qool_footer()?>
<script type="text/javascript" src="<?php qoolinfo('home')?>/<?php template_path()?>/jquery.nivo.slider.pack.js"></script>
    <script type="text/javascript">
    $(window).load(function() {
    	$('#banner').nivoSlider({
    		effect: 'random', // Specify sets like: 'fold,fade,sliceDown'
    		slices: 15, // For slice animations
    		boxCols: 8, // For box animations
    		boxRows: 4, // For box animations
    		animSpeed: 1000, // Slide transition speed
    		pauseTime: 8000, // How long each slide will show
    		startSlide: 0, // Set starting Slide (0 index)
    		directionNav: true, // Next & Prev navigation
    		controlNav: false, // 1,2,3... navigation
    		controlNavThumbs: false, // Use thumbnails for Control Nav
    		pauseOnHover: true, // Stop animation while hovering
    		manualAdvance: false, // Force manual transitions
    		prevText: 'Prev', // Prev directionNav text
    		nextText: 'Next', // Next directionNav text
    	});
    });
    </script>

	</body>
</html>