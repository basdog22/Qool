<?php
//error_reporting(E_ALL);
function halcyonic_css(){
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
	<!--[if IE 9]>
	<link rel="stylesheet" href="<?php qoolinfo('home')?>/<?php template_path()?>/css/style-ie9.css" />
	<![endif]-->
	<?php
}

function halcyonic_favicon(){
	$settings = theme_settings();
	if(strstr("http",$settings['favicon'])):?>
	<link rel="shortcut icon" href="<?php echo $settings['favicon']?>" />
	<?php else:?>
	<link rel="shortcut icon" href="<?php qoolinfo('home')?>/<?php template_path()?>/<?php echo $settings['favicon']?>" />
	<?php endif;
}
?>