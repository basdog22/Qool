<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php t(qoolinfo('module',1))?> - <?php site('backend_title')?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Le styles -->
    <link href="<?php qoolinfo('home')?>/lib/css/bootstrap.css" rel="stylesheet">
    <link href="<?php qoolinfo('home')?>/lib/css/qool.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="<?php qoolinfo('home')?>/lib/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <?php if(isActive('elfinder')):?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php qoolinfo('home')?>/lib/css/jquery-ui.smoothness.css">
	<link rel="stylesheet" type="text/css" media="screen" href="<?php qoolinfo('home')?>/lib/css/elfinder.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="<?php qoolinfo('home')?>/lib/css/theme.css">
	<?php endif;?>
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php qoolinfo('home')?>/admin/"><?php site('backend_title')?></a>
          <div class="btn-group pull-right">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-user"></i> <?php user('username')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="<?php qoolinfo('home')?>/profiles/me"><?php t('Profile')?></a></li>
              <li class="divider"></li>
              <li><a href="<?php qoolinfo('home')?>/logout"><?php t('Logout')?></a></li>
            </ul>
          </div>
          <?php if(count(get_array('addonCreationActions'))>0):?>
          <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-plus"></i> <?php t("New")?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php foreach(get_array('addonCreationActions') as $k=>$v):?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php echo t($v)?></a></li>
              <?php endforeach;?>
            </ul>
          </div>
          <?php endif;?>
          <?php $menus = get_array('adminmenus')?>
		   <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-file"></i> <?php t('Content')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <?php $i = 0; foreach ($menus['content'] as $k=>$v):$i++?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
              <?php if(count($menus['content'])>$i):?><li class="divider"></li><?php endif;?>
           	<?php endforeach;?>
            </ul>
          </div>
		  <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-wrench"></i> <?php t('System')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
             <?php $i = 0; foreach ($menus['system'] as $k=>$v):$i++?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
              <?php if(count($menus['system'])>$i):?><li class="divider"></li><?php endif;?>
           	<?php endforeach;?>
            </ul>
          </div>
          <?php if(isActive('moduleMenu')):?>
          <div class="btn-group pull-left">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-cog icon-white"></i> <?php t('Options')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <?php foreach (get_array('moduleMenu') as $k=>$v):?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v); ?> </a></li>
            <?php endforeach;?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if(isActive('hasForm')):?>
          <a id="formSubmiter" class="btn btn-primary" href="#"><?php t("Save")?></a>
          <?php endif;?>
          <?php if(isActive('isEditor')):?>
          <a id="toggler" class="btn btn-primary" href="#"><?php t("HTML")?></a>
          <?php endif;?>
          
		  <form action="<?php qoolinfo('home')?>/admin/search" class="navbar-search pull-right">
			<input name="q" type="text" class="search-query span2" placeholder="<?php t('Search')?>">
		  </form>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span2">
          <div class="well sidebar-nav">
			<ul class="nav nav-list">
			<li class="nav-header"><?php t('Content')?></li>
			<?php foreach (get_array('contentAvailable') as $k=>$v):?>
			<li class="dropdown">
				<a class="dropdown-toggle"data-toggle="dropdown" href="#">
					<?php t($v['title'])?>
					<b class="caret"></b>
				</a>
				<ul class="dropdown-menu">
					<li><a href="<?php qoolinfo('home')?>/admin/contentnew?id=<?php echo $v['id']?>"><?php echo t("New")?></a></li>
					<li><a href="<?php qoolinfo('home')?>/admin/itemlist?id=<?php echo $v['id']?>"><?php echo t("List")?></a></li>
				</ul>
			</li>
			<?php endforeach;?>  
			<li class="divider"></li>
			 <li class="nav-header"><?php t('Addon Options')?></li>
			 <?php foreach(get_array('addonMenuActions') as $k=>$v):?>
			 <?php 
			 $i = explode("/",$k);
			 $i = ucfirst($i[0]);
			 ?>
			 <li class="dropdown">
				<a class="dropdown-toggle"data-toggle="dropdown" href="#">
					<?php t($i)?>
					<b class="caret"></b>
				</a>
				<ul class="dropdown-menu">
				<?php foreach ($v as $ki=>$vi):?>
					<li><a href="<?php qoolinfo('home')?>/admin/<?php echo $ki?>"><?php echo t($vi)?></a></li>
				<?php endforeach; ?>						
				</ul>
			</li>
             <?php endforeach;?>
			</ul>
		</div>
		<?php if(isActive('objectfiles')):?>
		 <div class="well sidebar-nav">
		 	<ul class="nav nav-list">
			<li class="nav-header"><?php t('Object Files')?></li>
			<?php foreach (get_array('objectfiles') as $k=>$v):?>
			<li class="dropdown">
				<a class="dropdown-toggle"data-toggle="dropdown" href="#">
					<?php t(ucfirst($k))?>
					<b class="caret"></b>
				</a>
				
				<ul class="dropdown-menu">
				<?php foreach ($v as $file):?>
					<li>
					<a class="ajaxdelete" id="<?php echo $file['id']?>" rev="object_data" href="#" title="<?php t('Delete')?> <?php showFileName($file['value'])?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>">
						 &times; <?php showFileName($file['value'])?>
					</a>
					</li>
				<?php endforeach;?>
				</ul>
				
			</li>
			<?php endforeach;?>
			</ul>
			<div class="alert hide" id="msg"></div>
		 </div>
		<?php endif;?>
        </div><!--/span-->
        <div class="span10">
         
          <div class="row-fluid">
           <?php breadcrumbs()?>
           <?php include(get_the_include())?>
          </div><!--/row-->
          
        </div><!--/span-->
      </div><!--/row-->



      <footer>
        
      </footer>

    </div><!--/.fluid-container-->
     <div class="navbar navbar-fixed-bottom">
      <div class="navbar-inner">
        <div class="container-fluid">
        	 <div class="btn-group dropup pull-left">
	            <a class="btn dropdown-toggle" data-toggle="dropdown" >
	              <i class="icon-home"> </i> 
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a target="_blank" href="<?php qoolinfo('home')?>/admin/"><?php echo t("New Dashboard Window")?></a></li>
	              <li><a target="_blank" href="<?php qoolinfo('home')?>/"><?php echo t("View Site")?></a></li>
	              <li class="divider"></li>
	              <li><a target="_blank" href="http://www.qoolsoft.gr/">QoolSoft</a></li>
	              
	            </ul>
	          </div>
	           <div class="btn-group dropup pull-left">
	            <a class="btn dropdown-toggle" data-toggle="dropdown" >
	              <i class="icon-heart"> </i> 
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a data-target="#myModal" title="<?php echo t("New Shortcut")?>" data-toggle="modal" id="addNewShortCut" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newshortcut"><i class="icon-plus"> </i> <?php echo t("New Shortcut")?></a></li>
	              <li class="divider"></li>
	              <?php $shortcuts = get_array('general_data'); foreach ($shortcuts['shortcuts'] as $k=>$v):?>
	              <?php if($v['username']==user('username',0)):?>
	              
	              <li><a class="inline-block" <?php if($v['target']==1):?>target="_blank"<?php endif;?> href="<?php echo $v['link']?>"><i class="<?php echo $v['icon']?>"> </i> <?php echo $v['title']?></a><a href="#" class="ajaxdelete pull-right inline-block-small alert-error" title="<?php t("Delete")?>" id="<?php echo $k?>" rev="general_data">&times;</a></li>
	              <?php endif; ?>
	              <?php endforeach; ?>
	            </ul>
	          </div>
	          <div class="btn-group dropup pull-left">
	            <a class="btn dropdown-toggle" data-toggle="dropdown" >
	              <i class="icon-list-alt"> </i> 
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a data-target="#myModal" title="<?php echo t("New Task")?>" data-toggle="modal" id="addNewTask" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newtask"><i class="icon-plus"> </i> <?php echo t("New Task")?></a></li>
	              <li class="divider"></li>
	              <?php /*No need to pull data again*/ foreach ($shortcuts['tasks'] as $k=>$v):?>
	              <?php if($v['username']==user('username',0)):?>
	              
	              <li><a data-original-title="Task Contents" data-content="<?php echo $v['task']?>" class="inline-block poptop"   href="#"><?php echo $v['title']?></a><a href="#" class="ajaxdelete pull-right inline-block-small alert-error" title="<?php t("Delete")?>" id="<?php echo $k?>" rev="general_data">&times;</a></li>
	              <?php endif; ?>
	              <?php endforeach; ?>
	            </ul>
	          </div>
        	<p class="pull-right navbar-text">&copy; <?php site('backend_title')?> 2012</p>
        </div>
      </div>
     </div>
<div class="modal hide" id="myModal">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="modaltitle"></h3>
    </div>
    <div id="qoolmodal" class="modal-body">
	    Loading...
    </div>
    <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal"><?php t("Close")?></a>
    <a id="delbutton"  class="btn btn-danger hide" href="#"><?php t("Delete")?></a>
    </div>
</div>

	<script type="text/javascript">
	var qool_url = '<?php qoolinfo('home')?>';
	
	var domain_url = '<?php qoolinfo('domain')?>';
	var widgets_dir = '<?php $dir = qoolinfo('dirs',0); echo $dir['structure']['widgets']?>';
	var libs_dir = '<?php echo $dir['structure']['lib']?>';
	var cms_dir = '<?php echo $_SERVER['DOCUMENT_ROOT'].DIR_SEP.$dirs['special']['folder']?>';
	</script>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <?php if(isActive('elfinder')):?>
    
	<script src="<?php qoolinfo('home')?>/lib/js/jquery.1.7.2.min.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/jquery-ui-1.8.23.custom.min.js"></script>
	<script type="text/javascript" src="<?php qoolinfo('home')?>/lib/js/elfinder/elfinder.min.js"></script>
	<?php if(qoolinfo('langcode',0)!='en'):?>
	<script type="text/javascript" src="<?php qoolinfo('home')?>/lib/js/elfinder/i18n/elfinder.<?php qoolinfo('langcode')?>.js"></script>
	<?php endif;?>
	<script type="text/javascript" charset="utf-8">
	$().ready(function() {
		var elf = $('#elfinder').elfinder({
			<?php if(qoolinfo('langcode',0)!='en'):?>lang: '<?php qoolinfo('langcode')?>',<?php endif;?>
			url : '<?php qoolinfo('home')?>/lib/php/connector.php',
			height: 500,
			resizable: true,
			nlyMimes: ["image",'text']
		}).elfinder('instance');
	});
	</script>
	<?php else:?>
    <script src="<?php qoolinfo('home')?>/lib/js/jquery-1.8.0.min.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/jquery-ui-1.8.23.custom.min.js"></script>
    <?php endif;?>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-transition.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-alert.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-modal.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-dropdown.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-scrollspy.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-tab.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-tooltip.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-popover.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-button.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-collapse.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-carousel.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-typeahead.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/qool.js"></script>

<?php if(isActive('editarea')):?>
<script src="<?php qoolinfo('home')?>/lib/js/editarea/edit_area_full.js"></script>
<script type="text/javascript">
editAreaLoader.init({
	id: "editarea"
	,start_highlight: true
	,allow_resize: "both"
	,allow_toggle: false
	,word_wrap: true
	,language: "<?php qoolinfo('langcode')?>"
	,syntax: "<?php tpl('syntax')?>"
});
</script>
<?php endif;?>
<?php if(isActive('filelist')):?>
<ul class="hidden" id="filelist"></ul>
<?php endif; ?>
<?php if(isActive('hiddenEditor')):?>
<script type="text/javascript" src="<?php qoolinfo('home')?>/lib/js/editor/jquery.tinymce.js"></script>	
<script type="text/javascript">
$().ready(function() {
	editor_id = $("hr.editor").attr('id');
	$('hr.editor').tinymce({
		script_url : '<?php qoolinfo('home')?>/lib/js/editor/tiny_mce.js',
		language : "<?php qoolinfo('langcode')?>",
		remove_script_host : false,
		relative_urls : false,
		theme : "simple",
		width:'1px',
		height:'1px',
	});
	
	$("#"+editor_id).hide();
});
</script>
<?php endif;?>
<?php if(isActive('loadEditor')):?>
<script type="text/javascript" src="<?php qoolinfo('home')?>/lib/js/editor/jquery.tinymce.js"></script>	
<script type="text/javascript">

$().ready(function() {
	
	$('textarea.editor').tinymce({
		// Location of TinyMCE script
		script_url : '<?php qoolinfo('home')?>/lib/js/editor/tiny_mce.js',
		width:'900px',
		language : "<?php qoolinfo('langcode')?>",
		remove_script_host : false,
		relative_urls : false,
		file_browser_callback : 'elFinderBrowser',
		// General options
		theme : "advanced",
		plugins : "autolink,lists,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,imgmanager",

		// Theme options
		theme_advanced_buttons1 : "cut,copy,paste,pastetext,pasteword,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,insertdate,inserttime,preview,|,forecolor,backcolorhr,sub,sup,|,charmap,iespell,fullscreen,|,ltr,rtl,|,print,imgmanager",
		theme_advanced_buttons3 : "<?php foreach (get_array('editorBtns') as $k=>$v):?><?php echo $v['name']?>,<?php endforeach;?>",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "<?php qoolinfo('home')?>/admin/ajaxgettemplatecss",

		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "<?php qoolinfo('home')?>/admin/ajaxgeteditortemplates",
		external_link_list_url : "<?php qoolinfo('home')?>/admin/ajaxgeteditorlinks",
		external_image_list_url : "<?php qoolinfo('home')?>/admin/ajaxgeteditorimages",
		//media_external_list_url : "<?php qoolinfo('home')?>/admin/ajaxgeteditormedia",
		setup : function(ed) {
			// Add all custom buttons
			<?php foreach ($btns = get_array('editorBtns') as $k=>$v):?>
			ed.addButton('<?php echo $v['name']?>', {
				title : '<?php echo $v['title']?>',
				image : '<?php echo $v['image']?>',
				onclick : function() {
					<?php echo $v['onclick']?>;
				}
			});
			<?php endforeach;?>
		}
	});
});

</script>
<!-- /TinyMCE -->
	<?php endif;?>
  </body>
</html>