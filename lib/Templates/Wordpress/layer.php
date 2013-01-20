<?php
error_reporting(E_ERROR);
//ini_set("memory_limit","1000M");
function do_action($when){
	global $actions;
	
	foreach ($actions[$when] as $k=>$v){
		if(function_exists($v)){
			$v();
		}
		
	}
	unset($actions[$when]);
}

function get_userdata(){
	return $_SESSION['user'];
}
function wp_register_script( $handle, $src, $deps=array(), $ver='', $in_footer=false ){
	if($src){
		$qool = &get_array('qool');
		$qool->queeScript("<script type='text/javascript' src='{$src}'></script>",$handle);
	}
}

function wp_register_style( $handle, $src, $deps=array(), $ver='', $media='all' ){
	
	if($src){
		$qool = &get_array('qool');
		$qool->queeStyle("<link media='{$media}' rel='stylesheet' href='{$src}' />",$handle);
	}
}

function wp_enqueue_style( $handle, $src, $deps=array(), $ver='', $media='all' ){
	do_action('wp_print_styles');
	queedStyles($handle);
}

function wp_enqueue_script( $handle,$src,$deps=array(),$ver='',$in_footer=false ){
	queedScripts($handle);
}

function wp_enqueue_scripts($handle,$src){
	do_action('wp_print_scripts');
	queedScripts();
}

function get_template_directory(){
	return TEMPLATE_ABSOLUTE_DIR;
}

function get_theme_mod($mod){
	return get_qool_theme_setting($mod);
}


function get_qool_theme_setting($setting){
	$settings = theme_settings();
	return $settings[$setting];
}

function get_stylesheet_directory_uri(){
	$uri = qoolinfo('home',0)."/".template_path(0)."/css";
	return $uri;
}

function get_stylesheet_uri(){

	return get_stylesheet_directory_uri()."/style.css";
}

function get_stylesheet_directory(){
	return APPL_PATH.template_path(0)."/css";
}

function get_template_directory_uri(){
	$uri = qoolinfo('home',0)."/".template_path(0);
	return $uri;
}

function get_theme_data($filename){
	$uri = qoolinfo('home',0)."/".template_path(0)."/css/style.css";
	return $uri;
}

function delete_transient(){
	return ;
}

function remove_theme_mod(){
	return ;
}

function set_theme_mod(){
	return ;
}

function get_option($option,$default=false){
	global $wpoptions;
	if($result = wp_qool_special_posts($option)){
		return $result;
	}
	if($wpoptions[$option]){
		return $wpoptions[$option];
	}
	$goption = qoolinfo($option,0);


	$themeoption = get_qool_theme_setting($option);

	if(!$goption && $themeoption){
		return $themeoption;
	}
	if($goption && !$themeoption){
		return $goption;
	}

	if(!$goption && !$themeoption && get_qool_theme_setting('options_as_array')==true){
		$theme = theme_settings();
		$settings = array_merge($default,$theme);
		return $settings;
	}

	return $default;
}

function remove_action( $tag, $function_to_remove, $priority = 10 ) {
	return remove_filter( $tag, $function_to_remove, $priority );
}

function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
	$function_to_remove = _wp_filter_build_unique_id($tag, $function_to_remove, $priority);

	$r = isset($GLOBALS['wp_filter'][$tag][$priority][$function_to_remove]);

	if ( true === $r) {
		unset($GLOBALS['wp_filter'][$tag][$priority][$function_to_remove]);
		if ( empty($GLOBALS['wp_filter'][$tag][$priority]) )
			unset($GLOBALS['wp_filter'][$tag][$priority]);
		unset($GLOBALS['merged_filters'][$tag]);
	}

	return $r;
}

function wp_qool_special_posts($option){
	$qool = &get_array('qool');
	switch ($option){
		case 'sticky_posts':
			$stickytax = $qool->getTaxonomyByName('sticky');
			$ids = $qool->getIdsByTaxonomy($stickytax['id']);
			foreach ($ids as $k=>$v){
				$stickies[] = $v['id'];
			}
			return $stickies;
			break;

	}
}





function is_multisite(){
	return false;
}
function load_theme_textdomain(){
	return ;
}

function get_locale(){
	$locale = new Zend_Locale();
	return $locale->getDefault();
}
function _x($str){
	return t($str,0);
}
function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	global $wp_filter, $merged_filters;

	$idx = _wp_filter_build_unique_id($tag, $function_to_add, $priority);
	$wp_filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
	unset( $merged_filters[ $tag ] );
	return true;
}

function apply_filters($tag, $value) {
	global $wp_filter, $merged_filters, $wp_current_filter;
	
	$args = array();

	// Do 'all' actions first
	if ( isset($wp_filter['all']) ) {
		$wp_current_filter[] = $tag;
		$args = func_get_args();
		_wp_call_all_hook($args);
	}

	if ( !isset($wp_filter[$tag]) ) {
		if ( isset($wp_filter['all']) )
			array_pop($wp_current_filter);
		return $value;
	}

	if ( !isset($wp_filter['all']) )
		$wp_current_filter[] = $tag;

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	if ( empty($args) )
		$args = func_get_args();

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function']) ){
				$args[1] = $value;
				$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
			}

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $value;
}

function _wp_call_all_hook($args) {
	global $wp_filter;

	reset( $wp_filter['all'] );
	do {
		foreach( (array) current($wp_filter['all']) as $the_ )
			if ( !is_null($the_['function']) )
				call_user_func_array($the_['function'], $args);

	} while ( next($wp_filter['all']) !== false );
}

function _wp_filter_build_unique_id($tag, $function, $priority) {
	global $wp_filter;
	static $filter_id_count = 0;

	if ( is_string($function) )
		return $function;

	if ( is_object($function) ) {
		// Closures are currently implemented as objects
		$function = array( $function, '' );
	} else {
		$function = (array) $function;
	}

	if (is_object($function[0]) ) {
		// Object Class Calling
		if ( function_exists('spl_object_hash') ) {
			return spl_object_hash($function[0]) . $function[1];
		} else {
			$obj_idx = get_class($function[0]).$function[1];
			if ( !isset($function[0]->wp_filter_id) ) {
				if ( false === $priority )
					return false;
				$obj_idx .= isset($wp_filter[$tag][$priority]) ? count((array)$wp_filter[$tag][$priority]) : $filter_id_count;
				$function[0]->wp_filter_id = $filter_id_count;
				++$filter_id_count;
			} else {
				$obj_idx .= $function[0]->wp_filter_id;
			}

			return $obj_idx;
		}
	} else if ( is_string($function[0]) ) {
		// Static Calling
		return $function[0].$function[1];
	}
}
function get_transient(){
	return false;
}

function add_action($when,$what){
	global $actions;
	$actions[$when][] = $what;
}

function add_shortcode(){
	return ;
}

function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
	$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
	$r =& $args;
	else
	wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
	return array_merge( $defaults, $r );
	return $r;
}

function wp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
	$array = stripslashes_deep( $array );
	$array = apply_filters( 'wp_parse_str', $array );
}

function has_filter(){
	return ;
}

function is_404(){
	global $tpl;
	if($tpl->tpl->tplObject['theInclude']=='404'){
		return true;
	}
	return false;
}

function is_home(){
	global $tpl;
	$config = qoolinfo('config',0);
	if($tpl->tpl->tplObject['current_href']==$config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder){
		return true;
	}else{
		return false;
	}
}
function get_queried_object(){
	return new stdClass();
}

function term_description( $term_id=false, $taxonomy=false ){
	return $taxonomy;
}
function get_post_type($id){
	$qool = &get_array('qool');
	$type = $qool->getContentType($id);
	return $type['title'];
}

function is_tag($slug){
	$qool = &get_array('qool');
	if($qool->data['tax']){
		return true;
	}
	return false;
}
function is_tax($tax=false,$term=false){
	$qool = &get_array('qool');
	if(!$tax && !$term){
		if($qool->data['type']){
			return true;
		}
	}
	if($tax && !$term){
		if($qool->data['type']==$tax){
			return true;
		}
	}
	if($tax && $term){
		if($qool->data['type']==$tax && $qool->data['tax']==$term){
			return true;
		}
	}
	return false;
}

function is_search(){
	$qool = &get_array('qool');
	if($qool->data['action']=='search'){
		return true;
	}
	return false;
}

function is_year(){
	$qool = &get_array('qool');
	if($qool->data['action']=='archive'){
		return true;
	}
	return false;
}
function is_month(){
	$qool = &get_array('qool');
	if($qool->data['action']=='archive'){
		return true;
	}
	return false;
}
function is_day(){
	$qool = &get_array('qool');
	if($qool->data['action']=='archive'){
		return true;
	}
	return false;
}

function single_term_title($term,$echo){
	t($term,$echo);
}

function  is_author(){
	if(user('level',0)==1){
		return true;
	}
	return false;
}

function is_archive(){
	$qool = &get_array('qool');
	if($qool->data['action']=='archive'){
		return true;
	}
	return false;
}

function add_option( $option, $value, $deprecated=false, $autoload=false ){
	$wpxml = Zend_Registry::get('optionsxml');
	$xml = simplexml_load_file($wpxml);
	$dirs = Zend_Registry::get('dirs');
	foreach ($xml->option as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['name']==$option){
			return ;
		}
	}
	$node = $xml->addChild('option');
	$node->addAttribute('name',$option);
	$node->addAttribute('value',$value);
	if($autoload){
		$node->addAttribute('autoload','yes');
	}else{
		$node->addAttribute('autoload','no');
	}
	$xml->asXML(template_path(0).DIR_SEP."wp.xml");
}

function update_option($option,$value){
	$wpxml = Zend_Registry::get('optionsxml');
	$xml = simplexml_load_file($wpxml);
	$dirs = Zend_Registry::get('dirs');
	$i = 0;
	foreach ($xml->option as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['name']==$option){
			if($v['@attributes']['autoload']=='yes'){
				$autoload = true;
			}
			unset($xml->option[$i]);
		}
		$i++;
	}
	$node = $xml->addChild('option');
	$node->addAttribute('name',$option);
	$node->addAttribute('value',$value);
	if($autoload){
		$node->addAttribute('autoload','yes');
	}else{
		$node->addAttribute('autoload','no');
	}
	$xml->asXML(template_path(0).DIR_SEP."wp.xml");
}

function delete_option($option){
	$wpxml = Zend_Registry::get('optionsxml');
	$xml = simplexml_load_file($wpxml);
	$dirs = Zend_Registry::get('dirs');
	$i = 0;
	foreach ($xml->option as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['name']==$option){
			unset($xml->option[$i]);
		}
		$i++;
	}
	$xml->asXML(template_path(0).DIR_SEP."wp.xml");
}

function wp_load_alloptions(){
	global $wpoptions;
	$wpxml = Zend_Registry::get('optionsxml');
	$xml = simplexml_load_file($wpxml);
	foreach ($xml->option as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['autoload']=='yes'){
			$wpoptions[$v['@attributes']['name']] = $v['@attributes']['value'];
		}
	}
}

function __($str){
	return t($str,0);
}

function get_bloginfo( $show = '', $filter = 'raw' ) {

	switch( $show ) {
		case 'home' : // DEPRECATED
		case 'siteurl' : // DEPRECATED
		case 'url' :
			$output = home_url();
			break;
		case 'wpurl' :
			$output = site_url();
			break;
		case 'description':
			$output = get_option('blogdescription');
			break;
		case 'rdf_url':
			$output = get_feed_link('rdf');
			break;
		case 'rss_url':
			$output = get_feed_link('rss');
			break;
		case 'rss2_url':
			$output = get_feed_link('rss2');
			break;
		case 'atom_url':
			$output = get_feed_link('atom');
			break;
		case 'comments_atom_url':
			$output = get_feed_link('comments_atom');
			break;
		case 'comments_rss2_url':
			$output = get_feed_link('comments_rss2');
			break;
		case 'pingback_url':
			$output = get_option('siteurl') .'/xmlrpc.php';
			break;
		case 'stylesheet_url':
			$output = get_stylesheet_uri();
			break;
		case 'stylesheet_directory':
			$output = get_stylesheet_directory_uri();
			break;
		case 'template_directory':
		case 'template_url':
			$output = get_template_directory_uri();
			break;
		case 'admin_email':
			$output = get_option('admin_email');
			break;
		case 'charset':
			$output = get_option('blog_charset');
			if ('' == $output) $output = 'UTF-8';
			break;
		case 'html_type' :
			$output = get_option('html_type');
			break;
		case 'version':
			global $wp_version;
			$output = $wp_version;
			break;
		case 'language':
			$output = get_locale();
			$output = str_replace('_', '-', $output);
			break;
		case 'text_direction':
			//_deprecated_argument( __FUNCTION__, '2.2', sprintf( __('The <code>%s</code> option is deprecated for the family of <code>bloginfo()</code> functions.' ), $show ) . ' ' . sprintf( __( 'Use the <code>%s</code> function instead.' ), 'is_rtl()'  ) );
			if ( function_exists( 'is_rtl' ) ) {
				$output = is_rtl() ? 'rtl' : 'ltr';
			} else {
				$output = 'ltr';
			}
			break;
		case 'name':
		default:
			$output = get_option('blogname');
			break;
	}

	$url = true;
	if (strpos($show, 'url') === false &&
		strpos($show, 'directory') === false &&
		strpos($show, 'home') === false)
		$url = false;

	if ( 'display' == $filter ) {
		if ( $url )
			$output = apply_filters('bloginfo_url', $output, $show);
		else
			$output = apply_filters('bloginfo', $output, $show);
	}
	if(!$output){
		return qoolinfo($value,0);
	}
	return $output;
}

function current_user_can(){
	return false;
}

function is_child_theme(){
	return false;
}

function set_transient(){
	return ;
}

function register_sidebar(){
	return ;
}

function is_admin(){
	if(user('level',0)==1){
		return true;
	}
	return false;
}

function get_header(){
	do_action('get_header');
	include APPL_PATH.template_path(0)."/header.php";
	
}

function has_action(){
	return false;
}
function absint( $maybeint ) {
	return abs( intval( $maybeint ) );
}
function get_footer(){
	do_action('get_footer');
	include APPL_PATH.template_path(0)."/footer.php";
}

function language_attributes($doctype = 'html') {
	$attributes = array();
	$output = '';

	if ( function_exists( 'is_rtl' ) && is_rtl() )
	$attributes[] = 'dir="rtl"';

	if ( $lang = get_bloginfo('language') ) {
		if ( get_option('html_type') == 'text/html' || $doctype == 'html' )
		$attributes[] = "lang=\"$lang\"";

		if ( get_option('html_type') != 'text/html' || $doctype == 'xhtml' )
		$attributes[] = "xml:lang=\"$lang\"";
	}

	$output = implode(' ', $attributes);
	$output = apply_filters('language_attributes', $output);
	echo $output;
}

function wp_head(){
	$_SESSION['qool_main_include'] = false;
	google_jQuery();
	if(!$_SESSION['wp_init']){
		do_action('init');
		$_SESSION['wp_init'] = true;
	}
	do_action('wp_enqueue_scripts');
	themecss();
	themejs();
	do_action('wp_print_scripts');
	qool_header();
	do_action('wp_head');
	$_SESSION['have_posts_called'] = false;
	$_SESSION['qool_main_include'] = false;
	$_SESSION['num_posts_called'] = 0;
}

function body_class(){
	$class = "class='body'";
	$class = apply_filters('body_class',$class);
	echo $class;
}

function wp_footer() {
	do_action('wp_footer');
	qool_footer();
	do_action('wp_print_footer_scripts');
}

function bloginfo($what){
	switch ($what){
		case "stylesheet_directory":
			echo qoolinfo('home',0)."/".template_path(0);
			return ;
			break;
		case "stylesheet_url":
			echo qoolinfo('home',0)."/".template_path(0)."/style.css";
			return ;
			break;
		case "template_directory":
			echo qoolinfo('home',0)."/".template_path(0);
			return ;
			break;
		case "template_url":
			echo qoolinfo('home',0)."/".template_path(0);
			return ;
			break;
		case "charset":
			echo 'UTF-8';
			return ;
			break;
		case "html_type":
			echo 'text/html';
			return ;
			break;
		case "language":
			echo Zend_Registry::get('langcode');
			return ;
			break;
		case "text_direction":
			echo "ltr";
			return ;
			break;
		case "version":
			echo '3.5';
			return ;
			break;
		
	}
	$wp_defaults = array(
	'name'=>'frontend_title',
	'description'=>'slogan',
	'url'=>'home',
	'wpurl'=>'home'
	);
	//has more.. go to: http://codex.wordpress.org/Function_Reference/bloginfo
	site($wp_defaults[$what]);
}

function wp_title( $sep='|', $echo=true, $seplocation=''){
	$title = site_title(0);
	$title = apply_filters('wp_title', $title);
	if($echo){
		echo $title;
	}
	return $title;
}

function  is_paged(){
	global $tpl;
	if($tpl->tpl->tplObject['pager']['pages']>0){
		return true;
	}
	return false; //for now
}

function get_query_var($var){
	$qool = &get_array('qool');
	return $qool->data[$var];
}

function get_header_image(){
	$settings = theme_settings();
	return $settings['header_image'];
}

function get_custom_header(){
	$settings = theme_settings();
	$image = $settings['header_image'];
	$imagedata = getimagesize($image);
	$data = array();
	$data['width'] = $imagedata[0];
	$data['height'] = $imagedata[1];
	$data['url'] = $image;
	$data['thumbnail_url'] = $image;

	return (object) wp_parse_args( $data, $default );
}

function header_image(){
	$settings = theme_settings();
	echo  $settings['header_image'];
}

function home_url(){
	qoolinfo('home');
}

function is_active_sidebar($index){
	global $tpl;
	$i = 0;
	foreach ($tpl->tpl->tplObject['builtwidgets'] as $k=>$v){
		$sidebars[$i] = $k;
		$i++;
	}
	if($sidebars[$index]){
		return true;
	}
	return false;
}

function get_sidebar($name=false){
	do_action('get_sidebar');
	if($name){
		include APPL_PATH.template_path(0).DIR_SEP."sidebar-{$name}.php";
	}else{
		include APPL_PATH.template_path(0).DIR_SEP."sidebar.php";
	}
}

function get_search_form(){
	do_action('get_search_form');
	include APPL_PATH.template_path(0).DIR_SEP."searchform.php";
}

function esc_attr_e( $text, $domain = 'default' ) {
	echo esc_attr( $text );
}

function is_page_template($template){
	global $tpl;
	if($tpl->tpl->tplObject['theInclude'].".php"==$template){
		return true;
	}
	return false;
}

function wp_nav_menu($args){
	
	menu("Main",$args);
}

function is_category(){
	if($qool->data['type']=='category'){
		return true;
	}
	return false;
}

function have_posts(){
	global $tpl;
	$qool = &get_array('qool');
	$_SESSION['num_posts_called']++;
	//check if the action is one that needs a special include.
	switch ($qool->data['action']){
		case "login":
			
			
			if(!$_SESSION['qool_main_include'] && !$_SESSION['have_posts_called']){
				
				$_SESSION['qool_main_include'] = true;
				return true;
			}else{
				
				if($_SESSION['have_posts_called']){
					do_action('loop_end');
					return false;
				}else{
					
					return true;
				}
			}
			break;
	}
	if(!$content = get_array('content')){

		if(!$_SESSION['have_posts_called']){

			$single = $tpl->tpl->tplObject['single'];
			if($single){
				return true;
			}
		}
		do_action('loop_end');
		return false;
	}else{
		$single = $tpl->tpl->tplObject['content'][0];
		if($single){
			return true;
		}
		do_action('loop_end');
		return false;
	}
	do_action('loop_end');
	return false;
}

function the_post(){
	global $tpl;
	do_action('the_post');
	if($_SESSION['qool_main_include']){
		
		load_the_include();
		$_SESSION['qool_main_include'] = false;
		$_SESSION['have_posts_called'] = true;
		return ;
	}
	if(get_array('content')){
		$tpl->tpl->tplObject['single'] = $tpl->tpl->tplObject['content'][0];
	}
	$_SESSION['have_posts_called'] = true;
	
	unset($tpl->tpl->tplObject['content'][0]);
	array_shift(&$tpl->tpl->tplObject['content']);
	return ;
}

function get_site_transient(){
	return ;
}

function get_currentuserinfo(){
	return $_SESSION['user'];
}

function wp_remote_post($url, $args = array()) {
	return new stdClass();
}

function is_wp_error( $thing ){
	return false;
}

function wp_remote_retrieve_body(){
	return '';
}

function is_serialized(){
	return false;
}

function get_post_types(){
	return get_post_stati();
}



function is_rtl(){
	return false;
}

function get_template_part($slug=false,$part=false){
	do_action('get_template_part_'.$slug,$slug,$name);
	if($slug && $part){
		if(file_exists(APPL_PATH.template_path(0).DIR_SEP.$slug."-".$part.".php")){
			include APPL_PATH.template_path(0).DIR_SEP.$slug."-".$part.".php";
			return true;
		}
	}else{
		if(file_exists(APPL_PATH.template_path(0).DIR_SEP.$slug.".php")){
			include APPL_PATH.template_path(0).DIR_SEP.$slug.".php";
			return true;
		}
	}
	//if we reached this spot, no wp template was found. we should load qool template or an addon here...
	//the thing is that wp does this recursive
	if(!$_SESSION['qool_main_include']){
		load_the_include();
		$_SESSION['qool_main_include'] = true;
	}
}

function get_post_format($id){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	$qool = &get_array('qool');

	//get id
	$all = $qool->getAllBySlug($single['slug']);

	$type = $qool->getContentType($all['type_id']);

	return $type['lib']; //just for now
}

function post_class(){
	echo "post";
}

function the_ID(){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	$qool = &get_array('qool');

	//get id
	$all = $qool->getAllBySlug($single['slug']);
	echo $all['id'];
}

function has_post_thumbnail(){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	if($single['thumbnail']){
		return true;
	}
	return false;
}

function the_post_thumbnail($class='thumbnail'){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];

	echo "<img src='".qoolinfo('home',0)."/{$single['thumbnail']}' class='{$class}' />";
}

function the_permalink(){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	$qool = &get_array('qool');

	//get id
	$all = $qool->getAllBySlug($single['slug']);

	$type = $qool->getContentType($all['type_id']);
	$perma = qoolinfo('home',0);
	$perma .= "/".$type['lib'];
	$perma .= "/". the('slug',0);
	echo apply_filters('the_permalink', $perma);
}

function the_title_attribute(){
	the('title');
}

function the_title(){
	$title = the('title',0);
	$title = apply_filters('the_title',$title);
	echo $title;
}

function the_excerpt(){
	$excerpt =  the('content',0);
	$excerpt = apply_filters('the_excerpt',$excerpt);
	echo $excerpt;
}



function get_the_excerpt(){
	$excerpt =  the('content',0);
	$excerpt = apply_filters('get_the_excerpt',$excerpt);
	return $excerpt;
}

function the_content(){
	$content = the('content',0);
	$content = apply_filters('the_content',$content);
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;
}



function wp_link_pages(){
	return ;
}





function comments_template( $file='comments.php', $separate_comments=false ){
	include APPL_PATH.template_path(0)."/".$file;
}

function is_singular(){
	global $tpl;
	if($single = $tpl->tpl->tplObject['single']){

		return true;
	}
	return false;
}

function in_category(){
	return false;
}

function  the_author_posts_link(){
	echo "<a href='".qoolinfo('home',0)."/profiles/".the('author')."'>".the('author')."</a>";
}

function get_permalink($id = 0, $leavename = false){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	$qool = &get_array('qool');

	//get id
	$all = $qool->getAllBySlug($single['slug']);

	$type = $qool->getContentType($all['type_id']);
	$perma = qoolinfo('home',0);
	$perma .= "/".$type['lib'];
	$perma .= "/". the('slug',0);
	return apply_filters('post_link', $perma, $post, $leavename);
	
}

function permalink_anchor($mode = 'id') {
	
	
}

function get_post_permalink( $id = 0, $leavename = false, $sample = false ) {
	

	return apply_filters('post_type_link', $post_link, $post, $leavename, $sample);
}

function post_permalink( $post_id = 0, $deprecated = '' ) {
	
}

function get_page_link( $id = false, $leavename = false, $sample = false ) {
	
	return apply_filters('page_link', $link, $id, $sample);
}
function _get_page_link( $id = false, $leavename = false, $sample = false ) {
	
	return apply_filters( '_get_page_link', $link, $id );
}


function post_password_required(){
	return false;
}

function have_comments(){
	return false;
}




function the_time($d=false){
	if($d){
		$format = $d;
	}else{
		$format = "d-m-Y";
	}
	echo apply_filters('the_time', date($format,the('datestr',0)), $d);
}

function get_the_date($d=false){
	if($d){
		$format = $d;
	}else{
		$format = "d-m-Y";
	}
	
	$the_date =   date($format,the('datestr',0));
	return apply_filters('get_the_date', $the_date, $d);
}

function the_date($d=false){
	if($d){
		$format = $d;
	}else{
		$format = "d-m-Y";
	}
	return  date($format,the('datestr',0));
}

function is_attachment(){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	if($single['thumbnail']){
		return true;
	}
	return false;
}

function wp_attachment_is_image($id){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	if($single['thumbnail']){
		return true;
	}
	return false;
}

function wp_get_attachment_url($id){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];

	return qoolinfo('home',0)."/".$single['thumbnail'];
}

function wp_get_attachment_metadata( $post_id = 0, $unfiltered = false ) {

	$data = getimagesize(wp_get_attachment_url($post_id));

	$meta['width'] = $data[0];
	$meta['height'] = $data[1];
	return $meta;
}



function  _e($str){
	t($str);
}

function dynamic_sidebar($index){
	do_action('dynamic_sidebar');
	global $tpl;
	$i = 0;
	foreach ($tpl->tpl->tplObject['builtwidgets'] as $k=>$v){
		$sidebars[$i] = $k;
		$i++;
	}

	widget($sidebars[$index]);
}

function is_front_page(){
	global $tpl;
	$config = qoolinfo('config',0);
	if($tpl->tpl->tplObject['current_href']==$config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder){
		return true;
	}else{
		return false;
	}

}

function esc_html($html){
	return $html;
}

function esc_url( $url, $protocols = null, $_context = 'display' ) {
	$original_url = $url;

	if ( '' == $url )
	return $url;
	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = _deep_replace($strip, $url);
	$url = str_replace(';//', '://', $url);

	if ( strpos($url, ':') === false && ! in_array( $url[0], array( '/', '#', '?' ) ) &&
	! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
	$url = 'http://' . $url;

	// Replace ampersands and single quotes only when displaying.
	if ( 'display' == $_context ) {
		$url = str_replace( '&amp;', '&#038;', $url );
		$url = str_replace( "'", '&#039;', $url );
	}

	return $url;
}

function _deep_replace( $search, $subject ) {
	$found = true;
	$subject = (string) $subject;
	while ( $found ) {
		$found = false;
		foreach ( (array) $search as $val ) {
			while ( strpos( $subject, $val ) !== false ) {
				$found = true;
				$subject = str_replace( $val, '', $subject );
			}
		}
	}

	return $subject;
}

function esc_attr($attr=''){
	return $attr;
}



function do_action_ref_array($tag, $args) {

	if ( ! isset($wp_actions) )
	$wp_actions = array();

	if ( ! isset($wp_actions[$tag]) )
	$wp_actions[$tag] = 1;
	else
	++$wp_actions[$tag];

	// Do 'all' actions first
	if ( isset($wp_filter['all']) ) {
		$wp_current_filter[] = $tag;
		$all_args = func_get_args();
		_wp_call_all_hook($all_args);
	}

	if ( !isset($wp_filter[$tag]) ) {
		if ( isset($wp_filter['all']) )
		array_pop($wp_current_filter);
		return;
	}

	if ( !isset($wp_filter['all']) )
	$wp_current_filter[] = $tag;

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
		if ( !is_null($the_['function']) )
		call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter[$tag]) !== false );

	array_pop($wp_current_filter);
}

function apply_filters_ref_array($tag, $args) {
	global $wp_filter, $merged_filters, $wp_current_filter;

	// Do 'all' actions first
	if ( isset($wp_filter['all']) ) {
		$wp_current_filter[] = $tag;
		$all_args = func_get_args();
		_wp_call_all_hook($all_args);
	}

	if ( !isset($wp_filter[$tag]) ) {
		if ( isset($wp_filter['all']) )
		array_pop($wp_current_filter);
		return $args[0];
	}

	if ( !isset($wp_filter['all']) )
	$wp_current_filter[] = $tag;

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
		if ( !is_null($the_['function']) )
		$args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $args[0];
}


function get_post_type_object(){
	return ;
}

function get_post_stati(){
	global $tpl;
	return $tpl->tpl->tplObject['contentAvailable'];
}

function is_user_logged_in(){
	if(user('level',0)<8000){
		return true;
	}
	return false;
}

function setup_postdata($post) {
	global $tpl;
	$tpl->tpl->tplObject['single'] = $post;
	return true;
}

function has_post_format(){
	return false;
}

function wp_reset_postdata(){
	return ;
}

function is_single(){
	return true;
}

function is_ssl(){
	$config = qoolinfo('config',0);
	if($config->host-http=='https://'){
		return true;
	}
	return false;
}

function add_theme_support(){
	return ;
}

function set_post_thumbnail_size(){

}

function add_editor_style(){

}
function register_nav_menus(){

}

function get_terms(){

}


function is_page($slug=false){
	if($slug){
		global $tpl;
		$single = $tpl->tpl->tplObject['single'];
		if($slug==$single['slug']);
		return true;
	}
	return false;
}

function get_post_meta($id,$key=false,$single=false){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	return $single['title'];
	$qool = &get_array('qool');
	$post = $qool->getAllById($id);
	$type = $qool->getContentType($post['type_id']);
	$post = $qool->getContent($type['lib'],$post['id'],1);
	return $post;
}

function get_the_title($id){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	return $single['title'];
	$qool = &get_array('qool');
	$post = $qool->getAllById($id);
	$type = $qool->getContentType($post['type_id']);
	$post = $qool->getContent($type['lib'],$post['id'],1);
	
	return apply_filters( 'the_title', $post['title'], $id );
}

function wp_remote_get(){
	return array();
}

function wp_list_pages( $args ){
	$args = wp_parse_args($args);
	list_content($args);
}



function wp_get_archives(){
	return ;
}

function wp_list_categories($args=''){
	echo apply_filters( 'wp_list_categories', $output, $args );
}

function wp_register(){
	if(is_admin()){

	}

}
function wp_loginout(){
	if(is_admin()){

	}

}

function wp_meta(){
	do_action('wp_meta');
}

function comments_popup_link(){

}

function has_tag($tag,$post){
	return true;
}

function previous_image_link(){

}

function next_image_link(){

}

function add_image_size(){
	
}
function load_plugin_textdomain(){
	
	
}
function register_taxonomy(){
	
}

function get_posts($args){
	return new WP_Query($args);
}

function wp_insert_post(){
	
}

function update_post_meta(){
	
}

function wp_set_object_terms(){
	
}

function sanitize_key( $key ) {
	$raw_key = $key;
	$key = strtolower( $key );
	$key = preg_replace( '/[^a-z0-9_\-]/', '', $key );
	return apply_filters( 'sanitize_key', $key, $raw_key );
}

function wp_register_sidebar_widget(){
	
}

function register_widget_control(){
	
}

function get_categories(){
	
}

function plugin_basename(){
	
}

function single_post_title(){
	wp_title();
}

function get_cat_id(){
	
}

function query_posts(){
	
}

function get_post_custom_values(){
	
}

function the_author(){
	
	
}

function automatic_feed_links(){
	
}

function register_post_type(){
	
}

function has_nav_menu(){
	return true;
}

function wp_reset_query(){
	
}

function addslashes_gpc($gpc) {
	if ( get_magic_quotes_gpc() )
		$gpc = stripslashes($gpc);

	return esc_sql($gpc);
}

function esc_sql($gpc){
	return $gpc;
}

function get_term_children(){
	
}

function wp_deregister_script( $handle ){
	
}

function get_post_custom_keys(){
	
}


function get_the_content($more_link_text = null, $stripteaser = false) {
	$output = the('content',0);
	$output .= apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more\" class=\"more-link\">$more_link_text</a>", $more_link_text );	
	return $output;
}

function is_active_widget(){
	
}

function site_url(){
	qoolinfo('home');
}




function get_the_author($deprecated = '') {
	
}


function get_the_modified_author() {
	
}

function the_modified_author() {
	
}
function get_the_author_meta( $field = '', $user_id = false ) {
	
}


function the_author_meta($field = '', $user_id = false) {
	
}


function get_the_author_link() {
	
}


function the_author_link() {
	
}

function get_the_author_posts() {
	
}


function get_author_posts_url($author_id, $author_nicename = '') {
	
}


function wp_list_authors($args = '') {
	
		
}


function is_multi_author() {
	
}


function __clear_multi_author_cache() {
	
}

function _walk_bookmarks($bookmarks, $args = '' ) {
	
}


function wp_list_bookmarks($args = '') {
	
}
function get_category_link( $category ) {
	
}


function get_category_parents( $id, $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	
}


function get_the_category( $id = false ) {
	return apply_filters( 'get_the_categories', $categories );
}


function _usort_terms_by_name( $a, $b ) {
	
}


function _usort_terms_by_ID( $a, $b ) {
	
}


function get_the_category_by_ID( $cat_ID ) {
	
}


function get_the_category_list( $separator = '', $parents='', $post_id = false ) {
	
	return apply_filters( 'the_category', $thelist, $separator, $parents );
}







function category_description( $category = 0 ) {
	
}

function wp_dropdown_categories( $args = '' ) {
	

	
}





function wp_tag_cloud( $args = '' ) {
	

	$return = apply_filters( 'wp_tag_cloud', $return, $args );

	
}


function default_topic_count_text( $count ) {
	
}


function default_topic_count_scale( $count ) {
	
}


function wp_generate_tag_cloud( $tags, $args = '' ) {
	return apply_filters( 'wp_generate_tag_cloud', $return, $tags, $args );
}


function _wp_object_name_sort_cb( $a, $b ) {
	
}

function _wp_object_count_sort_cb( $a, $b ) {
	
}


function walk_category_tree() {
	
}


function walk_category_dropdown_tree() {
	
}



function get_tag_link( $tag ) {
	
}


function get_the_tags( $id = 0 ) {
	return apply_filters( 'get_the_tags', get_the_terms( $id, 'post_tag' ) );
}


function get_the_tag_list( $before = '', $sep = '', $after = '', $id = 0 ) {
	return apply_filters( 'the_tags', get_the_term_list( $id, 'post_tag', $before, $sep, $after ), $before, $sep, $after, $id );
}





function tag_description( $tag = 0 ) {
	
}





function get_the_terms( $id, $taxonomy ) {
	$terms = apply_filters( 'get_the_terms', $terms, $id, $taxonomy );
}


function get_the_term_list( $id, $taxonomy, $before = '', $sep = '', $after = '' ) {
	
	$term_links = apply_filters( "term_links-$taxonomy", $term_links );
}


function the_terms( $id, $taxonomy, $before = '', $sep = ', ', $after = '' ) {
	
	echo apply_filters('the_terms', $term_list, $taxonomy, $before, $sep, $after);
}


function has_category( $category = '', $post = null ) {
	
}

function has_term( $term = '', $taxonomy = '', $post = null ) {
	
}

function get_comment_author( $comment_ID = 0 ) {
	
	return apply_filters('get_comment_author', $author);
}


function comment_author( $comment_ID = 0 ) {
	$author = apply_filters('comment_author', get_comment_author( $comment_ID ) );
	
}


function get_comment_author_email( $comment_ID = 0 ) {
	
	return apply_filters('get_comment_author_email', $comment->comment_author_email);
}


function comment_author_email( $comment_ID = 0 ) {
	echo apply_filters('author_email', get_comment_author_email( $comment_ID ) );
}


function comment_author_email_link($linktext='', $before='', $after='') {
	
}


function get_comment_author_email_link($linktext='', $before='', $after='') {
	
}


function get_comment_author_link( $comment_ID = 0 ) {
	
	return apply_filters('get_comment_author_link', $return);
}


function comment_author_link( $comment_ID = 0 ) {
	
}

function get_comment_author_IP( $comment_ID = 0 ) {
	
	return apply_filters('get_comment_author_IP', $comment->comment_author_IP);
}


function comment_author_IP( $comment_ID = 0 ) {
	
}


function get_comment_author_url( $comment_ID = 0 ) {
	
	return apply_filters('get_comment_author_url', $url);
}


function comment_author_url( $comment_ID = 0 ) {
	echo apply_filters('comment_url', get_comment_author_url( $comment_ID ));
}

function get_comment_author_url_link( $linktext = '', $before = '', $after = '' ) {
	
	return apply_filters('get_comment_author_url_link', $return);
}


function comment_author_url_link( $linktext = '', $before = '', $after = '' ) {
	
}


function comment_class( $class = '', $comment_id = null, $post_id = null, $echo = true ) {
	
}


function get_comment_class( $class = '', $comment_id = null, $post_id = null ) {
	return apply_filters('comment_class', $classes, $class, $comment_id, $post_id);
}


function get_comment_date( $d = '', $comment_ID = 0 ) {
	
	return apply_filters('get_comment_date', $date, $d);
}


function comment_date( $d = '', $comment_ID = 0 ) {
	
}

function get_comment_excerpt( $comment_ID = 0 ) {
	return apply_filters('get_comment_excerpt', $excerpt);
}


function comment_excerpt( $comment_ID = 0 ) {
	echo apply_filters('comment_excerpt', get_comment_excerpt($comment_ID) );
}


function get_comment_ID() {
	
	return apply_filters('get_comment_ID', $comment->comment_ID);
}


function comment_ID() {

}
function get_comment_link( $comment = null, $args = array() ) {
	return apply_filters( 'get_comment_link', $link . '#comment-' . $comment->comment_ID, $comment, $args );
}


function get_comments_link($post_id = 0) {
	
}

function comments_link( $deprecated = '', $deprecated_2 = '' ) {
	
}

function get_comments_number( $post_id = 0 ) {
	return apply_filters('get_comments_number', $count, $post_id);
}

function comments_number( $zero = false, $one = false, $more = false, $deprecated = '' ) {
	echo apply_filters('comments_number', $output, $number);
}

function get_comment_text( $comment_ID = 0 ) {
	return apply_filters( 'get_comment_text', $comment->comment_content, $comment );
}

function comment_text( $comment_ID = 0 ) {
	echo apply_filters( 'comment_text', get_comment_text( $comment_ID ), $comment );
}

function get_comment_time( $d = '', $gmt = false, $translate = true ) {
	return apply_filters('get_comment_time', $date, $d, $gmt, $translate);
}

function comment_time( $d = '' ) {
	
}

function get_comment_type( $comment_ID = 0 ) {
	return apply_filters('get_comment_type', $comment->comment_type);
}

function comment_type($commenttxt = false, $trackbacktxt = false, $pingbacktxt = false) {
	
}

function get_trackback_url() {
	return apply_filters('trackback_url', $tb_url);
}

function trackback_url( $deprecated_echo = true ) {
	
}
function get_post_reply_link($args = array(), $post = null) {
	return apply_filters('post_comments_link', $before . $link . $after, $post);
}

function post_reply_link($args = array(), $post = null) {
	
}

function get_cancel_comment_reply_link($text = '') {
	return apply_filters('cancel_comment_reply_link', '<a rel="nofollow" id="cancel-comment-reply-link" href="' . $link . '"' . $style . '>' . $text . '</a>', $link, $text);
}

function cancel_comment_reply_link($text = '') {
	
}

function get_comment_id_fields( $id = 0 ) {
	return apply_filters('comment_id_fields', $result, $id, $replytoid);
}

function comment_id_fields( $id = 0 ) {
	
}

function comment_form_title( $noreplytext = false, $replytext = false, $linktoparent = true ) {
	
}




function wp_list_comments($args = array(), $comments = null ) {
	
}


function comment_form( $args = array(), $post_id = null ) {
	
}

function trackback_rdf( $deprecated = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.5' );

	if ( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator') )
		return;

	echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
		<rdf:Description rdf:about="';
	the_permalink();
	echo '"'."\n";
	echo '    dc:identifier="';
	the_permalink();
	echo '"'."\n";
	echo '    dc:title="'.str_replace('--', '&#x2d;&#x2d;', wptexturize(strip_tags(get_the_title()))).'"'."\n";
	echo '    trackback:ping="'.get_trackback_url().'"'." />\n";
	echo '</rdf:RDF>';
}

function comments_open( $post_id = null ) {

	return apply_filters( 'comments_open', $open, $post_id );
}

function pings_open( $post_id = null ) {
	return apply_filters( 'pings_open', $open, $post_id );
}

function wp_comment_form_unfiltered_html_nonce() {
	
}



function comments_popup_script($width=400, $height=400, $file='') {
	
}



function get_comment_reply_link($args = array(), $comment = null, $post = null) {
	return apply_filters('comment_reply_link', $before . $link . $after, $args, $comment, $post);
}

function comment_reply_link($args = array(), $comment = null, $post = null) {
	
}


function wp_logout_url($redirect = '') {
	
	return apply_filters('logout_url', $logout_url, $redirect);
}

function wp_login_url($redirect = '', $force_reauth = false) {
	
	return apply_filters('login_url', $login_url, $redirect);
}

function wp_login_form( $args = array() ) {
	
	
}

function wp_lostpassword_url( $redirect = '' ) {
	
	return apply_filters( 'lostpassword_url', $lostpassword_url, $redirect );
}







function get_current_blog_id() {
	
}





function post_type_archive_title( $prefix = '', $display = true ) {
	
	$title = apply_filters('post_type_archive_title', $post_type_obj->labels->name );

}

function single_cat_title( $prefix = '', $display = true ) {
	
}

function single_tag_title( $prefix = '', $display = true ) {

}



function single_month_title($prefix = '', $display = true ) {
	
}

function get_archives_link($url, $text, $format = 'html', $before = '', $after = '') {
	
	$link_html = apply_filters( 'get_archives_link', $link_html );

	
}



function calendar_week_mod($num) {
	
}

function get_calendar($initial = true, $echo = true) {
		
	if ( $echo )
		echo apply_filters( 'get_calendar',  $calendar_output );
	else
		return apply_filters( 'get_calendar',  $calendar_output );

}

function delete_get_calendar_cache() {

}


function allowed_tags() {
	
}

function the_date_xml() {

}



function the_modified_date($d = '', $before='', $after='', $echo = true) {

	
	$the_modified_date = apply_filters('the_modified_date', $the_modified_date, $d, $before, $after);

}

function get_the_modified_date($d = '') {
	
	return apply_filters('get_the_modified_date', $the_time, $d);
}


function get_the_time( $d = '', $post = null ) {
	
	return apply_filters('get_the_time', $the_time, $d, $post);
}

function get_post_time( $d = 'U', $gmt = false, $post = null, $translate = false ) { // returns timestamp
	
	return apply_filters('get_post_time', $time, $d, $gmt);
}

function the_modified_time($d = '') {
	echo apply_filters('the_modified_time', get_the_modified_time($d), $d);
}

function get_the_modified_time($d = '') {
	
	return apply_filters('get_the_modified_time', $the_time, $d);
}

function get_post_modified_time( $d = 'U', $gmt = false, $post = null, $translate = false ) {
	return apply_filters('get_post_modified_time', $time, $d, $gmt);
}

function the_weekday() {
	
	$the_weekday = apply_filters('the_weekday', $the_weekday);
}

function the_weekday_date($before='',$after='') {
	
	$the_weekday_date = apply_filters('the_weekday_date', $the_weekday_date, $before, $after);
	
}


function feed_links( $args = array() ) {
	
}

function feed_links_extra( $args = array() ) {
}

function rsd_link() {
	echo '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="' . get_bloginfo('wpurl') . "/xmlrpc.php?rsd\" />\n";
}

function wlwmanifest_link() {
	echo '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="'
		. get_bloginfo('wpurl') . '/wp-includes/wlwmanifest.xml" /> ' . "\n";
}

function noindex() {
	
}

function wp_no_robots() {
	echo "<meta name='robots' content='noindex,nofollow' />\n";
}

function rich_edit_exists() {
	
}

function user_can_richedit() {
	return apply_filters('user_can_richedit', $wp_rich_edit);
}

function wp_default_editor() {
	return apply_filters( 'wp_default_editor', $r ); // filter
}

function wp_editor( $content, $editor_id, $settings = array() ) {
	
}

function get_search_query( $escaped = true ) {
	$query = apply_filters( 'get_search_query', get_query_var( 's' ) );
	
}

function the_search_query() {
	echo esc_attr( apply_filters( 'the_search_query', get_search_query( false ) ) );
}



function paginate_links( $args = '' ) {
	
	
	
}

function wp_admin_css_color($key, $name, $url, $colors = array()) {
	
}

function register_admin_color_schemes() {
	
}

function wp_admin_css_uri( $file = 'wp-admin' ) {
	return apply_filters( 'wp_admin_css_uri', $_file, $file );
}

function wp_admin_css( $file = 'wp-admin', $force_echo = false ) {
	
	echo apply_filters( 'wp_admin_css', "<link rel='stylesheet' href='" . esc_url( wp_admin_css_uri( $file ) ) . "' type='text/css' />\n", $file );
	if ( function_exists( 'is_rtl' ) && is_rtl() )
		echo apply_filters( 'wp_admin_css', "<link rel='stylesheet' href='" . esc_url( wp_admin_css_uri( "$file-rtl" ) ) . "' type='text/css' />\n", "$file-rtl" );
}

function add_thickbox() {
	
}

function wp_generator() {
	the_generator( apply_filters( 'wp_generator_type', 'xhtml' ) );
}

function the_generator( $type ) {
	echo apply_filters('the_generator', get_the_generator($type), $type) . "\n";
}

function get_the_generator( $type = '' ) {
	if ( empty( $type ) ) {

		$current_filter = current_filter();
		if ( empty( $current_filter ) )
			return;

		switch ( $current_filter ) {
			case 'rss2_head' :
			case 'commentsrss2_head' :
				$type = 'rss2';
				break;
			case 'rss_head' :
			case 'opml_head' :
				$type = 'comment';
				break;
			case 'rdf_header' :
				$type = 'rdf';
				break;
			case 'atom_head' :
			case 'comments_atom_head' :
			case 'app_head' :
				$type = 'atom';
				break;
		}
	}

	switch ( $type ) {
		case 'html':
			$gen = '<meta name="generator" content="Qool  ' . get_bloginfo( 'version' ) . '">';
			break;
		case 'xhtml':
			$gen = '<meta name="generator" content="Qool ' . get_bloginfo( 'version' ) . '" />';
			break;
		case 'atom':
			$gen = '<generator uri="http://www.qool.gr/" version="' . get_bloginfo_rss( 'version' ) . '">Qool</generator>';
			break;
		case 'rss2':
			$gen = '<generator>http://www.qool.gr/?v=' . get_bloginfo_rss( 'version' ) . '</generator>';
			break;
		case 'rdf':
			$gen = '<admin:generatorAgent rdf:resource="http://www.qool.gr/?v=' . get_bloginfo_rss( 'version' ) . '" />';
			break;
		case 'comment':
			$gen = '<!-- generator="Qool/' . get_bloginfo( 'version' ) . '" -->';
			break;
		case 'export':
			$gen = '<!-- generator="Qool/' . get_bloginfo_rss('version') . '" created="'. date('Y-m-d H:i') . '" -->';
			break;
	}
	return apply_filters( "get_the_generator_{$type}", $gen, $type );
}

function checked( $checked, $current = true, $echo = true ) {
	
}

function selected( $selected, $current = true, $echo = true ) {

}

function disabled( $disabled, $current = true, $echo = true ) {

}

function __checked_selected_helper( $helper, $current, $echo, $type ) {
	
}

function user_trailingslashit($string, $type_of_url = '') {
	
	$string = apply_filters('user_trailingslashit', $string, $type_of_url);
	
}

function get_attachment_link($id = false) {
	return apply_filters('attachment_link', $link, $id);
}

function get_year_link($year) {
	
	if ( !empty($yearlink) ) {
		$yearlink = str_replace('%year%', $year, $yearlink);
		return apply_filters('year_link', home_url( user_trailingslashit($yearlink, 'year') ), $year);
	} else {
		return apply_filters('year_link', home_url('?m=' . $year), $year);
	}
}

function get_month_link($year, $month) {
	
	
		return apply_filters('month_link', home_url( '?m=' . $year . zeroise($month, 2) ), $year, $month);
	
}

function get_day_link($year, $month, $day) {
	
		return apply_filters('day_link', home_url( user_trailingslashit($daylink, 'day') ), $year, $month, $day);
	
}

function the_feed_link( $anchor, $feed = '' ) {
	echo apply_filters( 'the_feed_link', $link, $feed );
}

function get_feed_link($feed = '') {
	
	return apply_filters('feed_link', $output, $feed);
}

function get_post_comments_feed_link($post_id = 0, $feed = '') {
	return apply_filters('post_comments_feed_link', $url);
}

function post_comments_feed_link( $link_text = '', $post_id = '', $feed = '' ) {
	echo apply_filters( 'post_comments_feed_link_html', "<a href='$url'>$link_text</a>", $post_id, $feed );
}

function get_author_feed_link( $author_id, $feed = '' ) {
	
	$link = apply_filters('author_feed_link', $link, $feed);

	return $link;
}

function get_category_feed_link($cat_id, $feed = '') {

}

function get_term_feed_link( $term_id, $taxonomy = 'category', $feed = '' ) {
	if ( 'category' == $taxonomy )
		$link = apply_filters( 'category_feed_link', $link, $feed );
	elseif ( 'post_tag' == $taxonomy )
		$link = apply_filters( 'category_feed_link', $link, $feed );
	else
		$link = apply_filters( 'taxonomy_feed_link', $link, $feed, $taxonomy );

	return $link;
}

function get_tag_feed_link($tag_id, $feed = '') {

}

function get_edit_tag_link( $tag_id, $taxonomy = 'post_tag' ) {
	return apply_filters( 'get_edit_tag_link', get_edit_term_link( $tag_id, $taxonomy ) );
}

function edit_tag_link( $link = '', $before = '', $after = '', $tag = null ) {
	$link = edit_term_link( $link, '', '', false, $tag );
	echo $before . apply_filters( 'edit_tag_link', $link ) . $after;
}

function get_edit_term_link( $term_id, $taxonomy, $object_type = '' ) {
	
	return apply_filters( 'get_edit_term_link', $location, $term_id, $taxonomy, $object_type );
}

function edit_term_link( $link = '', $before = '', $after = '', $term = null, $echo = true ) {
	
	$link = $before . apply_filters( 'edit_term_link', $link, $term->term_id ) . $after;

}

function get_search_link( $query = '' ) {
	

	return apply_filters( 'search_link', $link, $search );
}

function get_search_feed_link($search_query = '', $feed = '') {
	
	$link = apply_filters('search_feed_link', $link, $feed, 'posts');

	return $link;
}

function get_search_comments_feed_link($search_query = '', $feed = '') {
	
	$link = apply_filters('search_feed_link', $link, $feed, 'comments');

	return $link;
}

function get_post_type_archive_link( $post_type ) {
	return apply_filters( 'post_type_archive_link', $link, $post_type );
}

function get_post_type_archive_feed_link( $post_type, $feed = '' ) {
	return apply_filters( 'post_type_archive_feed_link', $link, $feed );
}

function get_edit_post_link( $id = 0, $context = 'display' ) {
	return apply_filters( 'get_edit_post_link', admin_url( sprintf($post_type_object->_edit_link . $action, $post->ID) ), $post->ID, $context );
}

function edit_post_link( $link = null, $before = '', $after = '', $id = 0 ) {
	echo $before . apply_filters( 'edit_post_link', $link, $post->ID ) . $after;
}

function get_delete_post_link( $id = 0, $deprecated = '', $force_delete = false ) {
	return apply_filters( 'get_delete_post_link', wp_nonce_url( $delete_link, "$action-{$post->post_type}_{$post->ID}" ), $post->ID, $force_delete );
}

function get_edit_comment_link( $comment_id = 0 ) {
	return apply_filters( 'get_edit_comment_link', $location );
}

function edit_comment_link( $link = null, $before = '', $after = '' ) {
	echo $before . apply_filters( 'edit_comment_link', $link, $comment->comment_ID ) . $after;
}

function get_edit_bookmark_link( $link = 0 ) {
	return apply_filters( 'get_edit_bookmark_link', $location, $link->link_id );
}

function edit_bookmark_link( $link = '', $before = '', $after = '', $bookmark = null ) {
	echo $before . apply_filters( 'edit_bookmark_link', $link, $bookmark->link_id ) . $after;
}

function get_previous_post($in_same_cat = false, $excluded_categories = '') {

}

function get_next_post($in_same_cat = false, $excluded_categories = '') {
	
}

function get_adjacent_post( $in_same_cat = false, $excluded_categories = '', $previous = true ) {
	

	}

function get_adjacent_post_rel_link($title = '%title', $in_same_cat = false, $excluded_categories = '', $previous = true) {
	
	return apply_filters( "{$adjacent}_post_rel_link", $link );
}

function adjacent_posts_rel_link($title = '%title', $in_same_cat = false, $excluded_categories = '') {
	
}

function adjacent_posts_rel_link_wp_head() {
	
}

function next_post_rel_link($title = '%title', $in_same_cat = false, $excluded_categories = '') {
	
}

function prev_post_rel_link($title = '%title', $in_same_cat = false, $excluded_categories = '') {

}

function get_boundary_post( $in_same_cat = false, $excluded_categories = '', $start = true ) {

}

function previous_post_link($format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '') {
	
}

function next_post_link($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {

}

function adjacent_post_link($format, $link, $in_same_cat = false, $excluded_categories = '', $previous = true) {
	
	echo apply_filters( "{$adjacent}_post_link", $format, $link );
}

function get_pagenum_link($pagenum = 1, $escape = true ) {
	
	$result = apply_filters('get_pagenum_link', $result);

}

function get_next_posts_page_link($max_page = 0) {
	
}

function next_posts( $max_page = 0, $echo = true ) {
	
}

function get_next_posts_link( $label = null, $max_page = 0 ) {
	
		$attr = apply_filters( 'next_posts_link_attributes', '' );
	
}

function next_posts_link( $label = null, $max_page = 0 ) {

}

function get_previous_posts_page_link() {

}

function previous_posts( $echo = true ) {
	
}

function get_previous_posts_link( $label = null ) {
	
	if ( !is_single() && $paged > 1 ) {
		$attr = apply_filters( 'previous_posts_link_attributes', '' );
		return '<a href="' . previous_posts( false ) . "\" $attr>". preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';
	}
}

function previous_posts_link( $label = null ) {
	
}

function get_posts_nav_link( $args = array() ) {
	

}

function posts_nav_link( $sep = '', $prelabel = '', $nxtlabel = '' ) {
	
}

function get_comments_pagenum_link( $pagenum = 1, $max_page = 0 ) {
	$result = apply_filters('get_comments_pagenum_link', $result);
}

function get_next_comments_link( $label = '', $max_page = 0 ) {
	
}

function next_comments_link( $label = '', $max_page = 0 ) {

}

function get_previous_comments_link( $label = '' ) {
	
	return '<a href="' . esc_url( get_comments_pagenum_link( $prevpage ) ) . '" ' . apply_filters( 'previous_comments_link_attributes', '' ) . '>' . preg_replace('/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label) .'</a>';
}

function previous_comments_link( $label = '' ) {
	
}

function paginate_comments_links($args = array()) {
	
}

function get_shortcut_link() {
	return apply_filters('shortcut_link', $link);
}



function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
	$url = qoolinfo('home',0);
	return apply_filters( 'home_url', $url, $path, $orig_scheme, $blog_id );
}


function get_site_url( $blog_id = null, $path = '', $scheme = null ) {
		return apply_filters( 'site_url', $url, $path, $orig_scheme, $blog_id );
}

/**
 * Retrieve the url to the admin area for the current site.
 *
 * @package WordPress
 * @since 2.6.0
 *
 * @param string $path Optional path relative to the admin url.
 * @param string $scheme The scheme to use. Default is 'admin', which obeys force_ssl_admin() and is_ssl(). 'http' or 'https' can be passed to force those schemes.
 * @return string Admin url link with optional path appended.
*/
function admin_url( $path = '', $scheme = 'admin' ) {

}

function get_admin_url( $blog_id = null, $path = '', $scheme = 'admin' ) {
	return apply_filters('admin_url', $url, $path, $blog_id);
}

function includes_url($path = '') {
	return apply_filters('includes_url', $url, $path);
}

function content_url($path = '') {
	return apply_filters('content_url', $url, $path);
}

function plugins_url($path = '', $plugin = '') {
	$url = qoolinfo('home',0)."/";
	return apply_filters('plugins_url', $url, $path, $plugin);
}

function network_site_url( $path = '', $scheme = null ) {
	
	return apply_filters('network_site_url', $url, $path, $orig_scheme);
}

function network_home_url( $path = '', $scheme = null ) {
	
	return apply_filters( 'network_home_url', $url, $path, $orig_scheme);
}

function network_admin_url( $path = '', $scheme = 'admin' ) {
	
	return apply_filters('network_admin_url', $url, $path);
}

function user_admin_url( $path = '', $scheme = 'admin' ) {
	
	return apply_filters('user_admin_url', $url, $path);
}

function self_admin_url($path = '', $scheme = 'admin') {
}

function set_url_scheme( $url, $scheme = null ) {
	
	return apply_filters( 'set_url_scheme', $url, $scheme, $orig_scheme );
}

function get_dashboard_url( $user_id, $path = '', $scheme = 'admin' ) {
	return apply_filters( 'user_dashboard_url', $url, $user_id, $path, $scheme);
}

function get_edit_profile_url( $user, $scheme = 'admin' ) {
	return apply_filters( 'edit_profile_url', $url, $user, $scheme);
}

function rel_canonical() {
	
}

function wp_get_shortlink($id = 0, $context = 'post', $allow_slugs = true) {
	return apply_filters('get_shortlink', $shortlink, $id, $context, $allow_slugs);
}

function wp_shortlink_wp_head() {
	
}

function wp_shortlink_header() {
	
}

function the_shortlink( $text = '', $title = '', $before = '', $after = '' ) {
	
	if ( !empty( $shortlink ) ) {
		$link = '<a rel="shortlink" href="' . esc_url( $shortlink ) . '" title="' . $title . '">' . $text . '</a>';
		$link = apply_filters( 'the_shortlink', $link, $shortlink, $text, $title );
		echo $before, $link, $after;
	}
}


function _wp_menu_item_classes_by_context( &$menu_items ) {
	
}

function walk_nav_menu_tree( $items, $depth, $r ) {
	
}

function _nav_menu_item_id_use_once( $id, $item ) {
	
}
add_filter( 'nav_menu_item_id', '_nav_menu_item_id_use_once', 10, 2 );



function get_the_ID() {
	
}






function the_guid( $id = 0 ) {
	
}

function get_the_guid( $id = 0 ) {
	
	return apply_filters('get_the_guid', $post->guid);
}





function _convert_urlencoded_to_entities( $match ) {
	
}



function has_excerpt( $id = 0 ) {
	
}



function get_post_class( $class = '', $post_id = null ) {
	
	return apply_filters('post_class', $classes, $class, $post->ID);
}



function get_body_class( $class = '' ) {
	
	return apply_filters( 'body_class', $classes, $class );
}



function sticky_class( $post_id = null ) {
	
}



function _wp_link_page( $i ) {
	
}

function post_custom( $key = '' ) {
	
}



function wp_dropdown_pages($args = '') {
	
	$output = apply_filters('wp_dropdown_pages', $output);

}



function wp_page_menu( $args = array() ) {
	
	$menu = apply_filters( 'wp_page_menu', $menu, $args );
	
}

function walk_page_tree($pages, $depth, $current_page, $r) {
	
}

function walk_page_dropdown_tree() {
	
}


function the_attachment_link( $id = 0, $fullsize = false, $deprecated = false, $permalink = false ) {
}

function wp_get_attachment_link( $id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false ) {
	
	return apply_filters( 'wp_get_attachment_link', "<a href='$url' title='$post_title'>$link_text</a>", $id, $size, $permalink, $icon, $text );
}

function prepend_attachment($content) {
	
	$p = apply_filters('prepend_attachment', $p);

	return "$p\n$content";
}

function get_the_password_form() {
	
	return apply_filters('the_password_form', $output);
}



function get_page_template_slug( $post_id = null ) {
	
}

function wp_post_revision_title( $revision, $link = true ) {
	
}

function wp_list_post_revisions( $post_id = 0, $args = null ) {
	
}



function get_post_thumbnail_id( $post_id = null ) {
	
}



function update_post_thumbnail_cache( $wp_query = null ) {
	
}

function get_the_post_thumbnail( $post_id = null, $size = 'post-thumbnail', $attr = '' ) {
	
	return apply_filters( 'post_thumbnail_html', $html, $post_id, $post_thumbnail_id, $size, $attr );
}

function is_feed(){
	return false;
}
?>