<?php

function qoolinfo($value,$echo=true){
	if (Zend_Registry::isRegistered($value)){
		if($echo){
			echo Zend_Registry::get($value);
		}else{
			return Zend_Registry::get($value);
		}
	}
}

function site($value,$echo=true){
	$config = Zend_Registry::get('config');
	if($echo){
		echo $config->site->$value;
	}else{
		return $config->site->$value;
	}
}

function yesorno($int){
	if($int>0){
		t("Yes");
	}else{
		t("No");
	}
}

function themecss($echo=true){
	$css = '<link rel="stylesheet" href="'.qoolinfo('home',0).'/lib/js/css.js" />';
	$css = '<link rel="stylesheet" href="'.qoolinfo('home',0).'/qoolcss" />';
	if($echo){
		queedStyles();
		echo $css;
		return ;
	}
	return $css;
}

function queedScripts($handle=false){
	$qool = &get_array('qool');
	if(!$handle){
		foreach ($qool->scriptQuee as $k=>$v){
			echo $v.PHP_EOL;
			unset($qool->scriptQuee[$k]);
		}
	}else{
		echo $qool->scriptQuee[$handle].PHP_EOL;
		unset($qool->scriptQuee[$handle]);
	}

}

function queedStyles($handle=false){
	$qool = &get_array('qool');
	if(!$handle){
		foreach ($qool->styleQuee as $k=>$v){
			echo $v.PHP_EOL;
			unset($qool->styleQuee[$k]);
		}
	}else{
		echo $qool->styleQuee[$handle].PHP_EOL;
		unset($qool->styleQuee[$handle]);
	}

}

function twitter_bootstrap_css($responsive=false){
	$css = '<link rel="stylesheet" href="'.qoolinfo('home',0).'/lib/css/bootstrap.css" />';
	if($responsive){
		$css.="<link href='".qoolinfo('home',0)."/lib/css/bootstrap-responsive.css' rel='stylesheet'>";
	}
	echo $css;
}

function showHelp($for){
	if(site('help',0)=='on'){
		$help = get_array('help');
		?>
		data-content='<?php echo $help[$for]['content']?>' data-original-title='<?php echo $help[$for]['title']?>'
		<?php
	}
}

function twitter_bootstrap_js($load=false){
	if(!$load){
		$js = '<script type="text/javascript" src="'.qoolinfo('home',0).'/lib/js/bootstrap.min.js"></script>';
	}else{
		foreach ($load as $k=>$v){
			$js .= '<script type="text/javascript" src="'.qoolinfo('home',0).'/lib/js/bootstrap-'.$v.'.js"></script>';
		}
	}
	echo $js;
}

function qool_jquery($v='1.8.0'){
	echo '<script type="text/javascript" src="'.qoolinfo('home',0).'/lib/js/jquery-'.$v.'.min.js"></script>';
}

function themejs($echo=true){
	$js = '<script src="'.qoolinfo('home',0).'/qooljs"></script>';
	if($echo){
		queedScripts();
		echo $js;
		return ;
	}
	return $js;
}

function t($value,$echo=true){
	$lang = Zend_Registry::get('language');
	$dirs = Zend_Registry::get('dirs');
	if(!$lang[$value]){

		keepTranslationStrings($value,$dirs);
	}else{
		cleanTranslationStrings($value,$dirs);
	}
	if($echo){
		if($lang[$value]){
			echo $lang[$value];
			return ;
		}
		echo $value;
	}else{
		if($lang[$value]){
			return  $lang[$value];
		}
		return $value;
	}
}

function showFileName($filepath){
	$filepath = explode(DIR_SEP,$filepath);
	echo end($filepath);
}

function get_the_include(){
	global $tpl;

	$dirs = Zend_Registry::get('dirs');
	$controller = Zend_Registry::get('controller');
	if($controller=='index'){
		if(file_exists( APPL_PATH.template_path(0).DIR_SEP.$tpl->tpl->tplObject['theInclude'].".".Zend_Registry::get('tplExt'))){
			return APPL_PATH.template_path(0).DIR_SEP.$tpl->tpl->tplObject['theInclude'].".".Zend_Registry::get('tplExt');
		}
		return $tpl->tpl->tplObject['theInclude'].".".Zend_Registry::get('tplExt');
	}
	return APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$controller.DIR_SEP."templates/".$tpl->tpl->tplObject['theInclude'].".".Zend_Registry::get('tplExt');
}

function load_the_include(){
	$include = get_the_include();
	if(file_exists($include)){
		include($include);
	}else{
		$include = str_replace(Zend_Registry::get('tplExt'),"php",$include);
		include($include);
	}
}

function is_nophp_include(){
	$include = get_the_include();
	$include = explode(".",$include);
	$include = array_reverse($include);
	if($include[0]==Zend_Registry::get('tplExt')){
		return true;
	}
	return false;
}

function user($value,$echo=true){
	if($echo){
		echo $_SESSION['user'][$value];
	}else{
		return $_SESSION['user'][$value];
	}
}

function get_array($name){
	global $tpl;
	return $tpl->tpl->tplObject[$name];
}

function showForm($form){
	global $tpl;

	$form = $tpl->tpl->tplObject[$form];

	if($tpl->tpl->tplObject['formTitle']){

		echo "<h1>".$tpl->tpl->tplObject['formTitle']."</h1>";
	}
	message();
	print $form;
}

function qool_header(){
	if(user('level',0)==1){
		$dirs = Zend_Registry::get('dirs');

		?><link href="<?php qoolinfo('home')?>/lib/css/navbar.css" rel="stylesheet"><?php
	}
}

function qool_footer($position='top'){

	if(user('level',0)==1){
		$dirs = Zend_Registry::get('dirs');

		include_once($dirs['structure']['templates'].DIR_SEP."backend/default/navbar.php");
	}
}

function message(){
	global $tpl;
	if($msg = $tpl->tpl->tplObject['message']){
		echo "<div id='generatedalert' class='alert alert-{$msg['type']}'>{$msg['message']}<a class='close' data-dismiss='alert' href='#'>&times;</a></div>";
	}
	$_SESSION['message'] = null;
}

function the_list(){
	global $tpl;
	$list = $tpl->tpl->tplObject['theList'];
	return $list;
}

function tpl($value,$echo=true){
	global $tpl;

	if($echo){
		echo $tpl->tpl->tplObject[$value];
	}else{
		return $tpl->tpl->tplObject[$value];
	}
}

function isActive($val){
	global $tpl;
	if($tpl->tpl->tplObject[$val]){
		return true;
	}
	return false;
}

function apager($bar=false){
	global $tpl;
	$class = 'inline';
	if(!$bar){
		$class= 'pagination-centered';
	}
	$current = $tpl->tpl->tplObject['curpage']+1;

	if($current<=1){
		$current=1;
	}
	if($tpl->tpl->tplObject['pager']['pages']>0){
		echo "<div class='pagination $class'><ul>";
		if($current>1){
			echo "<li><a rel='start' href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=1'>&laquo;</a></li>";
			echo "<li><a rel='prev' href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=".($current-1)."'>&lsaquo;</a></li>";
		}
		foreach ($tpl->tpl->tplObject['pager']['pager'] as $k=>$v){
			if($current==$v){
				echo "<li class='active'><a href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=".$v."'>{$v}</a></li>";
			}else{
				echo "<li><a href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=".$v."'>{$v}</a></li>";
			}
		}
		if($current<$tpl->tpl->tplObject['pager']['pages']){
			echo "<li><a rel='next' rel='start' href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=".($current+1)."'>&rsaquo;</a></li>";
			echo "<li><a rel='last' href='".qoolinfo('home',0).'/admin/'.$tpl->tpl->tplObject['qoolrequest']['action']."?id=".$tpl->tpl->tplObject['qoolrequest']['id']."&page=".$tpl->tpl->tplObject['pager']['pages']."'>&raquo;</a></li>";
		}
		echo "</ul> ";

		echo "</div>";
	}
}

function pager($bar=false){
	global $tpl;
	$class = 'inline';
	if(!$bar){
		$class= 'pagination-centered';
	}
	$current = $tpl->tpl->tplObject['curpage']+1;

	if($current<=1){
		$current=1;
	}
	if($tpl->tpl->tplObject['pager']['pages']>0){
		echo "<div class='pagination $class'><ul>";
		if($current>1){
			echo "<li><a rel='start' href='".tpl('current_href',0)."?page=1'>&laquo;</a></li>";
			echo "<li><a rel='prev' href='".tpl('current_href',0)."?page=".($current-1)."'>&lsaquo;</a></li>";
		}
		foreach ($tpl->tpl->tplObject['pager']['pager'] as $k=>$v){
			if($current==$v){
				echo "<li class='active'><a href='".tpl('current_href',0)."?page=".$v."'>{$v}</a></li>";
			}else{
				echo "<li><a href='".tpl('current_href',0)."?page=".$v."'>{$v}</a></li>";
			}
		}
		if($current<$tpl->tpl->tplObject['pager']['pages']){
			echo "<li><a rel='next' rel='start' href='".tpl('current_href',0)."?page=".($current+1)."'>&rsaquo;</a></li>";
			echo "<li><a rel='last' href='".tpl('current_href',0)."?page=".$tpl->tpl->tplObject['pager']['pages']."'>&raquo;</a></li>";
		}
		echo "</ul> ";

		echo "</div>";
	}
}

function breadcrumbs(){
	global $tpl;
	?>
	<ul class="breadcrumb">
	<li>
	<a href="<?php qoolinfo('home')?>/admin/"><?php t("Dashboard")?></a> <span class="divider">/</span>
	</li>
	<?php foreach (get_array('breadcrumbs') as $k=>$v): if(is_array($v)):?>
    <li>
    <a href="<?php qoolinfo('home')?>/admin/<?php echo $v[0]?><?php if($v[2]):?>?id=<?php echo $v[2]?><?php if($v[3]):?>&type_id=<?php echo $v[3]?><?php endif;?><?php endif;?>"><?php echo $v[1]?></a> <span class="divider">/</span>
    </li>
    <?php else:?>
     <li>
   		<?php echo $v?>
    </li>
    <?php endif; endforeach; ?>
	</ul>
	<?php
}

function template_path($echo=true){
	global $tpl;
	if($echo){
		echo $tpl->tpl->tplObject['tplpath'];
	}else{
		return  $tpl->tpl->tplObject['tplpath'];
	}
}

function widget($name,$title_wrap='h2',$content_wrap=false,$echo=true){
	global $tpl;

	$request = $tpl->tpl->tplObject['qoolrequest'];
	$widget = $tpl->tpl->tplObject['builtwidgets'][$name];
	if($widget['type']=='menu'){
		$widget['contents'] = menu($widget['name'],array('wrap_menu'=>'ul','wrap_link'=>'li','current_class'=>'current_page_item','class'=>'menu_item','wrap_menu_class'=>'link-list'),0);
	}
	if($echo){
		if($widget){
			echo '<'.$title_wrap.'>'.$widget['title'].'</'.$title_wrap.'>';
			if($content_wrap){
				echo '<'.$content_wrap.'>'.$widget['contents'].'<'.$content_wrap.'>';
				return ;
			}
			echo $widget['contents'];
			return ;
		}
		echo "<!--".$name."-->";
	}else{
		if($widget){
			$w['title'] = '<'.$title_wrap.'>'.$tpl->tpl->tplObject['builtwidgets'][$name]['title'].'</'.$title_wrap.'>';
			$w['contents'] =  '<'.$content_wrap.'>'.$tpl->tpl->tplObject['builtwidgets'][$name]['contents'].'<'.$content_wrap.'>';
			return $w;
		}
		return "<!--".$name."-->";
	}

}

function mywidget($name,$title_wrap='h2',$content_wrap=false,$echo=true){
	global $tpl;
	$request = $tpl->tpl->tplObject['qoolrequest'];
	$widget = $tpl->tpl->tplObject['builtwidgets'][$request['module'].'-'.$name];
	if($widget['type']=='menu'){
		$widget['contents'] = menu($widget['name'],array('wrap_menu'=>'ul','wrap_link'=>'li','current_class'=>'current_page_item','class'=>'menu_item','wrap_menu_class'=>'link-list'),0);
	}
	if($echo){
		if($widget){
			echo '<'.$title_wrap.'>'.$widget['title'].'</'.$title_wrap.'>';
			if($content_wrap){
				echo '<'.$content_wrap.'>'.$widget['contents'].'<'.$content_wrap.'>';
				return ;
			}
			echo $widget['contents'];
			return ;
		}
		echo $name;
	}else{
		if($widget){
			$w['title'] = '<'.$title_wrap.'>'.$tpl->tpl->tplObject['builtwidgets'][$name]['title'].'</'.$title_wrap.'>';
			$w['contents'] =  '<'.$content_wrap.'>'.$tpl->tpl->tplObject['builtwidgets'][$name]['contents'].'<'.$content_wrap.'>';
			return $w;
		}
		return $name;
	}

}

function contentwidget($type,$id){
	try{
		$qool = &get_array('qool');
		$content = $qool->getContent($type,$id);
		return $content;
	}catch (Exception $e){
		echo "No content";
	}
}

//the_tags($post['tags'],array('class'=>'tag','wrap_text'=>'span','wrap_link'=>'li')
function the_tags($tags,$attr,$echo=true){
	$defaults = array(
	'class'=>'tag',
	'wrap_text'=>'span',
	'wrap_link'=>'li',
	'type'=>'tag',
	'append'=> false
	);

	$attr = array_merge($defaults,$attr);
	$html = '';
	foreach ($tags as $k=>$v){
		$tag = $v['title'];
		if($attr['wrap_text']){
			$tag = "<".$attr['wrap_text'].">".$tag."</".$attr['wrap_text'].">";
		}
		$tag = "<a class='{$attr['class']}' href='".qoolinfo('home',0)."/taxonomy/{$attr['type']}/{$v['title']}/' rel='tag'>$tag</a>";
		if($attr['wrap_link']){
			$tag = "<".$attr['wrap_link']." class='{$attr['class']}'>".$tag."</".$attr['wrap_link'].">";
		}
		if($attr['append']){
			$tag.= $attr['append'];
		}
		$html .= $tag;
	}
	if($echo){
		echo $html;
	}else{
		return $html;
	}
}
//the_category($post['category'],array('class'=>'category','wrap_text'=>'span','wrap_link'=>'p'),true)
function the_category($tags,$attr,$echo=true){
	$defaults = array(
	'class'=>'category',
	'wrap_text'=>'span',
	'wrap_link'=>'p',
	'type'=>'category'
	);

	$attr = array_merge($defaults,$attr);
	$html = '';
	if(count($tags['previous'])>1 && $attr['breadcrumb']){
		foreach ($tags['previous'] as $k=>$v){
			$tag = $v['title'];
			if($attr['wrap_text']){
				$tag = "<".$attr['wrap_text'].">".$tag."</".$attr['wrap_text'].">";
			}
			$tag = "<a href='".qoolinfo('home',0)."/taxonomy/{$attr['type']}/{$v['title']}/' rel='category'>$tag</a>";
			if($attr['wrap_link']){
				$tag = "<".$attr['wrap_link']." class='{$attr['class']}'>".$tag."</".$attr['wrap_link'].">";
			}
			$html .= $tag;
		}
	}else{
		if(!is_array($tags)){
			global $tpl;
			$single = $tpl->tpl->tplObject['single'];
			$tags = $single['category'];
		}
		$v = $tags;
		$tag = $v['title'];
		if($attr['wrap_text']){
			$tag = "<".$attr['wrap_text'].">".$tag."</".$attr['wrap_text'].">";
		}
		$tag = "<a href='".qoolinfo('home',0)."/taxonomy/category/{$v['title']}/' rel='category'>$tag</a>";
		if($attr['wrap_link']){
			$tag = "<".$attr['wrap_link']." class='{$attr['class']}'>".$tag."</".$attr['wrap_link'].">";
		}
		$html .= $tag;
	}

	if($echo){
		echo $html;
	}else{
		return $html;
	}
}

//menu('Main',array('wrap_menu'=>'ul','wrap_link'=>'li','current_class'=>'current_page_item','class'=>'menu_item')
function menu($menu,$attr=array(),$echo=true){
	try{
		global $tpl;
		$defaults = array(
		'wrap_menu'=>'ul',
		'current_class'=>'current_page_item',
		'wrap_link'=>'li',
		'class'=>'menu_item'
		);

		$attr = array_merge($defaults,$attr);
		$qool = &get_array('qool');
		$html = '';
		$target = '';
		$config = qoolinfo('config',0);
		if(!is_array($menu)){
			$menu = $tpl->tpl->tplObject['menus'][$menu];
		}
		if($menu){
			if($menu['taxonomy']>0 || $menu['taxonomy_type']>0){
				if($menu['taxonomy']>0){
					$tax_link = $qool->getTaxonomyType($menu['taxonomy']);
				}else{
					$tax_link = $qool->getTaxonomyType($menu['taxonomy_type']);
				}
				$tax = $config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder.'/taxonomy/'.strtolower($tax_link).'/';

			}
			foreach ($menu['items'] as $k=>$v){
				$sub = '';
				$rel = '';
				$target = '';
				$class = '';
				if($v['objectlink']>0){
					$link = qoolinfo('home',0).'/'.$qool->getFullSlugById($v['objectlink']).'/';
				}else{
					$link = $v['link'];
				}
				if($link==''){
					$link = $tax.$v['title']."/";
				}
				if($v['link_target']>0){
					$target = "target='_blank'";
				}
				if($v['link_rel']){
					$rel = "rel='{$v['link_rel']}'";
				}
				if($attr['class']){
					$class = $attr['class'];
				}
				if($tpl->tpl->tplObject['current_href'].'/'==$link){
					$class .= " ".$attr['current_class'];
				}
				$item = "<a title='{$v['link_title']}' $target href='".$link."'>".$v['title']."</a>";

				if(count($v['items'])>0){
					$sub = menu($v,$attr,0);
				}
				if(!$menu['items'][$k+1]){
					$class .=" lastmenu";
				}
				if($attr['wrap_link']){
					$item = "<".$attr['wrap_link']." class='{$class}'>".$item.' '.$sub."</".$attr['wrap_link'].">";
				}
				if($attr['append']){
					$item .=$attr['append'];
				}
				if($attr['prepend']){
					$item =$attr['prepend'].$item;
				}
				$html.=$item;

			}
			if($attr['wrap_append']){
				$html .=$attr['wrap_append'];
			}
			$class = '';
			if($attr['wrap_menu_class']){
				$class = "class='{$attr['wrap_menu_class']}'";
			}
			if($attr['wrap_menu']){
				$html = "<".$attr['wrap_menu']." $class>".$html."</".$attr['wrap_menu'].">";
			}

		}else{
			$html = $menu;
		}
		if($echo){
			echo $html;
		}else{
			return $html;
		}
	}catch (Exception $e){
		echo "No content";
	}
}

function site_title($echo=true){
	global $tpl;
	$qool = &get_array('qool');
	$default = site('frontend_title',0).' - '.site('slogan',0);
	//check if we have a single
	if($single = $tpl->tpl->tplObject['single']){
		$title = $single['title']." | ".site('frontend_title',0);

	}
	//check if it is a taxonomy we are looking
	if($qool->data['tax']){
		$title = $qool->data['tax']." | ".site('frontend_title',0);
	}

	//check if there is a module title available
	if($t = $tpl->tpl->tplObject['module_title']){
		$title = $t." | ".site('frontend_title',0);
	}

	if(!$title){
		$title = $default;
	}
	if($echo){
		echo $title;
		return ;
	}
	return $default;
}

function recent($type,$num=10,$start=0){
	try{
		$qool = &get_array('qool');
		$content = $qool->getRecent($type,$num,$start);
		return $content;
	}catch (Exception $e){
		echo "No content";
	}
}

function list_content($args=array('echo'=>1)){
	try{
		$qool = &get_array('qool');
		$content = $qool->getContentList(10);
		foreach ($content as $k=>$v){
			$id = $qool->getAllBySlug($v['slug']);
			$list .= "<li><a href='".qoolinfo('home',0)."/".$qool->getFullSlugById($id['id'])."'>{$v['title']}</a></li>";
		}
		if(!0==$args['echo']){
			return $list;
		}
		echo $list;
	}catch (Exception $e){
		echo "No content";
	}
}

function get_random($type,$num=10,$start=0){
	try{
		$qool = &get_array('qool');
		$content = $qool->getRandom($type,$num,$start);
		return $content;
	}catch (Exception $e){
		echo "No content";
	}
}

function taxonomy_recent($taxonomy,$type,$num=10,$start=0){
	$qool = &get_array('qool');
	$content = $qool->getRecentByTaxonomy($taxonomy,$type,$num,$start);
	return $content;
}

function theme_settings(){
	global $tpl;
	return $tpl->tpl->tplObject['theme_settings'];
}

function the_meta($echo=true){
	global $tpl;
	$meta = $tpl->tpl->tplObject['head_meta'];
	if($echo){
		echo $meta;
		return ;
	}
	return $meta;
}

function setSingle($post){
	global $tpl;
	$tpl->tpl->tplObject['single'] = $post;
}

function the($name,$echo=true){
	global $tpl;
	$single = $tpl->tpl->tplObject['single'];
	if($echo){
		echo $single[$name];
		return ;
	}else{
		return $single[$name];
	}
}

//Google CDN libraries. Might be useful for designers


function google_AngularJS($v='1.0.2'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/angularjs/'.$v.'/angular.min.js"></script>';
}

function google_chromeFrame($v='1.0.3'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/'.$v.'/CFInstall.min.js"></script>';
}

function google_DojoLib($v='1.8.0'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/dojo/'.$v.'/dojo/dojo.js"></script>';
}

function google_ExtCore($v='3.1.0'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/ext-core/'.$v.'/ext-core.js"></script>';
}

function google_jQuery($v='1.8.1'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/'.$v.'/jquery.min.js"></script>';
}

function google_jQueryUI($v='1.8.23'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/'.$v.'/jquery-ui.min.js"></script>';
}

function google_MooTools($v='1.4.5'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/mootools/'.$v.'/mootools-yui-compressed.js"></script>';
}

function google_Prototype($v='1.7.1.0'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/prototype/'.$v.'/prototype.js"></script>';
}

function google_scriptaculous($v='1.9.0'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/scriptaculous/'.$v.'/scriptaculous.js"></script>';
}

function google_SWFObject($v='2.2'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/swfobject/'.$v.'/swfobject.js"></script>';
}

function google_WebFont($v='1.0.30'){
	echo '<script src="//ajax.googleapis.com/ajax/libs/webfont/'.$v.'/webfont.js"></script>';
}

function google_map($params){
	$default = array(
	'latitude' 	=>	'',
	'longitude' 	=>	'',
	'map_id'	=>	'map_canvas',
	'width'		=>	'100%',
	'height'	=>	'300px',
	'zoom'		=>	8,
	'maptype'	=>	'ROADMAP',
	'tilt'		=>	0,
	'lang'		=>	'en',
	'sensor'	=>	'false',
	'content'	=>	''
	);

	$params = array_merge($default,$params);
	$latlng = $params['latitude'].",".$params['longitude'];
	?>
	 <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=<?php echo $params['sensor']?>&language=<?php echo $params['language']?>"></script>
    <script>
    var map;
    function initialize() {
    	var latlng = new google.maps.LatLng(<?php echo $latlng?>);
    	var mapOptions = {
    		zoom: <?php echo $params['zoom']?>,
    		center: new google.maps.LatLng(<?php echo $params['latitude']?>, <?php echo $params['longitude']?>),
    		mapTypeId: google.maps.MapTypeId.<?php echo $params['maptype']?>
    	};
    	map = new google.maps.Map(document.getElementById('<?php echo $params['map_id']?>'),
    	mapOptions);
    	var marker = new google.maps.Marker({
    		position: latlng,
    		map: map,
    		title:'<?php echo $params['title']?>'});
    		var contentString = '<div style="float:left;padding:10px"><img width="200" src="<?php echo $params['image_url']?>" /></div><?php echo $params['content']?>';
    		var infowindow = new google.maps.InfoWindow({
    			content: contentString
    		});
    		google.maps.event.addListener(marker, 'click', function() {
    			infowindow.open(map,marker);
    		});
    }
    google.maps.event.addDomListener(window, 'load', initialize);

    </script>
    <div id="<?php echo $params['map_id']?>" style="width:<?php echo $params['width']?>;height:<?php echo $params['height']?>"></div>
    <?php
}
?>