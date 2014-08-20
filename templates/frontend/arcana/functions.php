<?php
function arcana_css(){
	$settings = theme_settings();
	if($settings['use_jscss']=='Yes'):?>
	<noscript>
	<?php themecss();?>
	</noscript>
	<?php themejs();?>
	<script src="<?php qoolinfo('home')?>/<?php template_path()?>/css/5grid/init.js?use=mobile,desktop,1000px&amp;mobileUI=1&amp;mobileUI.theme=none&amp;mobileUI.titleBarHeight=60&amp;mobileUI.openerWidth=52"></script>
	<?php else:?>
	<?php themecss();?>
	<?php endif;?>
	<link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/themes/default/default.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/themes/light/light.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/themes/dark/dark.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/themes/bar/bar.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/nivo-slider.css" type="text/css" media="screen" />
	<?php google_jqueryui();?>
	<!--[if IE 9]>
	<link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/css/style-ie9.css" />
	<![endif]-->
	<script src="<?php qoolinfo('home')?>/<?php template_path()?>/jquery.yabox.min.js"></script>
	<script>
	$(document).ready(function(){
		
		$('.thumbnails a img').yabox({
			fullClass: 'fullPolaroid',
			cbs: {
				show: $().yabox.animated.show(),
				hide: $().yabox.animated.hide()
			},
			hideOnClick: true,
			$content: $(this).attr('src')
		});
	});

	</script>
	
	<?php
}

function arcana_favicon(){
	$settings = theme_settings();
	if(strstr("http",$settings['favicon'])):?>
	<link rel="shortcut icon" href="<?php echo $settings['favicon']?>" />
	<?php else:?>
	<link rel="shortcut icon" href="<?php qoolinfo('home')?>/<?php template_path()?>/<?php echo $settings['favicon']?>" />
	<?php endif;
}
?>