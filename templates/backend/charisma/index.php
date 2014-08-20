<?php ob_start ("ob_gzhandler");?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php t(qoolinfo('module',1))?> - <?php site('backend_title')?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php if(isActive('calendar')):?>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/fullcalendar.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/fullcalendar.print.css' rel='stylesheet'  media='print'>
	<?php endif;?>
	<link id="bs-css" href="<?php qoolinfo('home')?>/templates/backend/charisma/css/bootstrap-united.css" rel="stylesheet">
	<style type="text/css">
	  body {
		padding-bottom: 40px;
		padding-top: 60px;
	  }
	  .sidebar-nav {
		padding: 9px 0;
	  }
	</style>
	<link href="<?php qoolinfo('home')?>/templates/backend/charisma/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="<?php qoolinfo('home')?>/templates/backend/charisma/css/charisma-app.css" rel="stylesheet">
	<link href="<?php qoolinfo('home')?>/templates/backend/charisma/css/jquery-ui-1.8.21.custom.css" rel="stylesheet">
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/chosen.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/uniform.default.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/colorbox.css' rel='stylesheet'>
	<?php if(isActive('isRTE')):?>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/jquery.cleditor.css' rel='stylesheet'>
	<?php endif;?>
	<?php if(isActive('elfinder')):?>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/elfinder.min.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/elfinder.theme.css' rel='stylesheet'>
	<?php endif;?>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/jquery.iphone.toggle.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/opa-icons.css' rel='stylesheet'>
	<link href='<?php qoolinfo('home')?>/templates/backend/charisma/css/uploadify.css' rel='stylesheet'>

	<!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->		
</head>

<body>
		<!-- topbar starts -->
	<div id="topbar" class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".top-nav.nav-collapse,.sidebar-nav.nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="<?php qoolinfo('home')?>/admin/"><?php site('backend_title')?></a>
				
				
				
				 <div class="btn-group pull-right">
		            <a class="btn dropdown-toggle openedpop" data-toggle="dropdown">
		              <i class="icon-user"></i> <?php user('username')?>
		              <span class="caret"></span>
		            </a>
		            <ul class="dropdown-menu">
		              <li><a href="<?php qoolinfo('home')?>/profiles/me"><?php t('Profile')?></a></li>
		              <?php if(!$_COOKIE['ALLOW_NOTIFICATIONS']):?>
		              <li><a id="allownotifications" href="#"><?php t('Allow Notifications')?></a></li>
		              <?php endif;?>
		              <li class="divider"></li>
		              <li><a href="<?php qoolinfo('home')?>/logout"><?php t('Logout')?></a></li>
		            </ul>
		          </div>
				
				
				<div class="btn-group dropdown pull-left">
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
	           <div class="btn-group dropdown pull-left">
	            <a class="btn dropdown-toggle" data-toggle="dropdown" >
	              <i class="icon-heart"> </i> 
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a data-target="#myModal" title="<?php echo t("New Shortcut")?>" data-toggle="modal" id="addNewShortCut" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newshortcut"><i class="icon-plus"> </i> <?php echo t("New Shortcut")?></a></li>
	              <li class="divider"></li>
	              <?php $shortcuts = get_array('general_data'); foreach ($shortcuts['shortcuts'] as $k=>$v):?>
	              <?php if($v['username']==user('username',0)):?>
	              
	              <li><a class="inline-block" <?php if($v['target']==1):?>target="_blank"<?php endif;?> href="<?php echo $v['link']?>"><i class="<?php echo $v['icon']?>"> </i> <?php echo $v['title']?></a><a href="#" class="btn ajaxdelete btn-danger pull-right btn-closesmall btn-round" title="<?php t("Delete")?>" id="<?php echo $k?>" rev="general_data"><i class="icon-remove icon-white"></i></a></li>
	              <?php endif; ?>
	              <?php endforeach; ?>
	            </ul>
	          </div>
	          <div class="btn-group dropdown pull-left">
	            <a class="btn dropdown-toggle" data-toggle="dropdown" >
	              <i class="icon-list-alt"> </i> 
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a data-target="#myModal" title="<?php echo t("New Task")?>" data-toggle="modal" id="addNewTask" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newtask"><i class="icon-plus"> </i> <?php echo t("New Task")?></a></li>
	              <li class="divider"></li>
	              <?php /*No need to pull data again*/ foreach ($shortcuts['tasks'] as $k=>$v):?>
	              <?php if($v['username']==user('username',0)):?>
	              
	              <li><a data-original-title="Task Contents" data-content="<?php echo $v['task']?>" class="inline-block poptop"   href="#"><?php echo $v['title']?></a><a href="#" class="btn ajaxdelete btn-danger pull-right btn-closesmall btn-round" title="<?php t("Delete")?>" id="<?php echo $k?>" rev="general_data"><i class="icon-remove icon-white"></i></a></li>
	              <?php endif; ?>
	              <?php endforeach; ?>
	            </ul>
	          </div>
	           <?php if(isActive('moduleMenu')):?>
	           <ul class="nav">
		           <li class="dropdown">
		            <a class="dropdown-toggle"data-toggle="dropdown" href="#">
								<i class="icon-plus icon-white"></i> <?php t('Options')?>
								<b class="caret"></b>
		              
		            </a>
		            <ul class="dropdown-menu">
		            <?php foreach (get_array('moduleMenu') as $k=>$v):?>
		              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v); ?> </a></li>
		            <?php endforeach;?>
		            </ul>
		          </li>
		         </ul>
		          <?php endif; ?>
	         
	          <div class="top-nav nav-collapse">
					<ul class="nav">
						<li>
							<form action="<?php qoolinfo('home')?>/admin/search" class="navbar-search pull-left">
								<input placeholder="<?php t('Search')?>" class="search-query span2" name="q" type="text">
							</form>
						</li>
					</ul>
					
				</div><!--/.nav-collapse -->
				 <?php if(isActive('hasForm')):?>
		          <a id="formSubmiter" class="btn btn-primary" href="#"><?php t("Save")?></a>
		          <?php endif;?>
		          <?php if(isActive('isEditor')):?>
		          <a id="toggler" class="btn btn-primary" href="#"><?php t("HTML")?></a>
		          <?php endif;?>
			</div>
		</div>
	</div>
	<!-- topbar ends -->
		<div class="container-fluid">
		<div class="row-fluid">
				
			<!-- left menu starts -->
			<div  class="span2 main-menu-span">
				<div class="well nav-collapse sidebar-nav" id="leftmenu">
				 <ul class="nav nav-tabs nav-stacked main-menu">
				 <?php if(count(get_array('addonCreationActions'))>0):?>
					
					<li class="nav-header">
						<i class="icon-plus"></i> <?php t("New")?>
					</li><li>
							
						<ul class="nav nav-tabs nav-stacked">
		              <?php foreach(get_array('addonCreationActions') as $k=>$v):?>
		              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php echo t($v)?></a></li>
		              <?php endforeach;?>
		            </ul>
					</li>
				
				 <?php endif;?>
				 <?php $menus = get_array('adminmenus')?>
				  <li class="nav-header emulatelink" rel="contentMenu">
					<i class="icon-file"></i> <?php t('Content')?>
		          </li>
		          <li id="contentMenu" style="display:none">  
		            <ul class="nav nav-tabs nav-stacked">
		            <?php $i = 0; foreach ($menus['content'] as $k=>$v):$i++?>
		              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
		     
		           	<?php endforeach;?>
		            </ul>
		          </li>
				  <li class="nav-header emulatelink" rel="systemMenu">
						<i class="icon-wrench"></i> <?php t('System')?>
					</li>
					<li id="systemMenu"  style="display:none">
		            <ul class="nav nav-tabs nav-stacked">
		             <?php $i = 0; foreach ($menus['system'] as $k=>$v):$i++?>
		              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
		              
		           	<?php endforeach;?>
		            </ul>
		          </li>
		         		          
		          </ul>
				</div>
				<div class="divider">&nbsp;</div>
				<div class="well nav-collapse sidebar-nav">
					<ul class="nav nav-tabs nav-stacked main-menu">
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
						 <li class="nav-header closedpop"><?php t('Addon Options')?></li>
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
					</div><!--/.well -->
				<?php if(isActive('objectfiles')):?>
				 <div class="well nav-collapse sidebar-nav " >
				 	<ul class="nav nav-tabs nav-stacked main-menu closedpop">
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
			<!-- left menu ends -->
			
			<div id="themaincontent" class="span10">
			<!-- content starts -->
			 <?php breadcrumbs()?>
           <?php include(get_the_include())?>

			
			</div><!--/#content.span10-->
				</div><!--/fluid-row-->
				
		<hr>

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

		<footer>
			<?php if(site('help',0)=='on'):?>
	          <a id="showhelp" class="pull-right btn btn-info btn-mini tour" href="#" onclick="return false;"><i class="icon-question-sign icon-white"></i></a>
	          <?php endif;?>
			<p class="pull-right navbar-text">&copy; <?php site('backend_title')?> <?php echo  date("Y")?></p>
			
		</footer>
		
	</div><!--/.fluid-container-->
	<script type="text/javascript">
	var twitterUsername = '<?php qoolconfig('social','twitterusername')?>';
	var qool_url = '<?php qoolinfo('home')?>';
	var domain_url = '<?php qoolinfo('domain')?>';
	var widgets_dir = '<?php $dir = qoolinfo('dirs',0); echo $dir['structure']['widgets']?>';
	var libs_dir = '<?php echo $dir['structure']['lib']?>';
	var cms_dir = '<?php echo $_SERVER['DOCUMENT_ROOT'].DIR_SEP.$dir['special']['folder']?>';
	</script>
	<script src="<?php qoolinfo('home')?>/lib/js/basket.js"></script>
	<script type="text/javascript" src="https://www.dropbox.com/static/api/1/dropins.js" id="dropboxjs" data-app-key="<?php qoolconfig('social','dropboxchooser')?>"></script>
	<?php if(isActive('elfinder')):?>
	<script type="text/javascript">
	//load scripts with basket.js
	basket.require(
	{ url: '<?php qoolinfo('home')?>/lib/js/jquery.1.7.2.min.js' },
	{ url: '<?php qoolinfo('home')?>/lib/js/jquery-ui-1.8.23.custom.min.js' },
	{ url: '<?php qoolinfo('home')?>/lib/js/elfinder/elfinder.min.js' }<?php if(qoolinfo('langcode',0)!='en'):?>,
	{ url: '<?php qoolinfo('home')?>/lib/js/elfinder/i18n/elfinder.<?php qoolinfo('langcode')?>.js' }<?php endif;?>
	);
	</script>
	<script type="text/javascript" charset="utf-8">
	$().ready(function() {
		var elf = $('#elfinder').elfinder({
			<?php if(qoolinfo('langcode',0)!='en'):?>lang: '<?php qoolinfo('langcode')?>',<?php endif;?>
			url : '<?php qoolinfo('home')?>/lib/php/connector.php',
			height: 500,
			resizable: true,
			onlyMimes: ["image",'text']
		}).elfinder('instance');
	});
	</script>
	<?php else:?>
	<script type="text/javascript">
	//load scripts with basket.js
	basket.require(
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery-1.7.2.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery-ui-1.8.21.custom.min.js' }
	);
	</script>
	<?php endif;?>
	<script type="text/javascript">
	//load scripts with basket.js
	basket.require(
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-transition.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-alert.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-modal.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-dropdown.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-scrollspy.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-tab.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-tooltip.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-popover.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-button.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-collapse.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-carousel.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-typeahead.js' }<?php if(site('help',0)=='on'):?>,
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/bootstrap-tour.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/qool-tour.js' }<?php endif;?>,
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.cookie.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/fullcalendar.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/gcal.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.dataTables.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/excanvas.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.flot.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.flot.pie.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.flot.stack.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.flot.resize.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.chosen.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.uniform.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.colorbox.min.js' }<?php if(isActive('isRTE')):?>,
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.cleditor.min.js' }<?php endif; ?>,
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.iphone.toggle.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.autogrow-textarea.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.uploadify-3.1.min.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/jquery.history.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/charisma.js' },
	{ url: '<?php qoolinfo('home')?>/templates/backend/charisma/js/resample.js' },
	{ url: '<?php qoolinfo('home')?>/lib/js/qool.js' }
	);
	</script>

	<?php if(isActive('isRTE')):?>
	<script type="text/javascript">
	//rich text editor
	$('.cleditor').cleditor();
	</script>
	<?php endif; ?>
	<?php if(isActive('calendar')):?>
	<script type="text/javascript">
	//initialize the calendar
	$(document).ready(function(){
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today prevYear,nextYear',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			buttonText: {
				prev:     '&lsaquo;',
				next:     '&rsaquo;',
				prevYear: '&laquo;',
				nextYear: '&raquo;',
				today:    '<?php t('Today')?>',
				month:    '<?php t('Month view')?>',
				week:     '<?php t('Week view')?>',
				day:      '<?php t('Day view')?>'
			},
			eventClick: function(event) {
				if(!event.url){
					$(this).attr('data-toggle','#mymodal');
					$("#modaltitle").html(event.title);
					$("#qoolmodal").html("<?php t("Starting at: ")?>" + $.fullCalendar.formatDate( new Date(event.start), 'yyyy,MM,dd' ) + " - <?php t("Ending at: ")?>" + $.fullCalendar.formatDate( new Date(event.end), 'yyyy,MM,dd' ));
					$(this).addClass('qoolmodal');
					$("#myModal").modal();
					return false;
				}
			},
			selectable: true,
			selectHelper: true,
			select: function(start, end, allDay) {
				var title = prompt('<?php t('Event Title')?>:');
				if (title) {
					$('#calendar').fullCalendar('renderEvent',{
						title: title,
						start: start,
						end: (end)?end:start,
						allDay: allDay
					},true);

					$.post(qool_url+"/admin/addgeneraldata", {
						title: title,
						datestart: $.fullCalendar.formatDate( new Date(start), 'MM/dd/yyyy' ),
						dateend: $.fullCalendar.formatDate( new Date((end)?end:start), 'MM/dd/yyyy' ),
						task: title,
						data_type: 'tasks'
					},function(data) {});
				}
				$('#calendar').fullCalendar('unselect');
			},
			events: [<?php foreach ($tasks = get_array('tasks') as $k=>$v):?><?php   ?>{
				title: '<?php echo $v['title']?>',
				start: $.fullCalendar.formatDate( new Date('<?php echo $v['datestart']?>'), 'yyyy,MM,dd' ),
				end: $.fullCalendar.formatDate( new Date('<?php echo $v['dateend']?>'), 'yyyy,MM,dd' ),
				className: 'label label-info',
				id: '<?php echo $v['id']?>'
			}<?php if($tasks[$k+1]):?>,<?php endif?><?php endforeach;?>]<?php if(isActive('hasGoogleCal')):?>,
			eventSources: [<?php foreach ($cals = get_array('googleCalendars') as $k=>$v):?>
			{
				url: "<?php echo $v['calendar_address']?>",
				className: 'label label-<?php echo $v['calendar_class']?>'
			}<?php if($cals[$k+1]):?>,<?php endif;?><?php endforeach;?>
			]<?php endif;?>
		});
	});
</script>
	<?php endif; ?>
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
		plugins : "autolink,lists,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,gallerycon,visualchars,nonbreaking,xhtmlxtras,template,advlist,imgmanager",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,gallerycon,imgmanager,media,|,link,unlink,anchor,|,cut,copy,paste,pastetext,pasteword",
		theme_advanced_buttons2 : "styleselect,formatselect,fontselect,fontsizeselect,|,search,replace,|,cleanup,|,insertdate,inserttime,|,forecolor,backcolorhr,sub,sup,|,charmap,iespell,fullscreen,|,ltr,rtl,|,print,|,preview",
		theme_advanced_buttons3 : "<?php foreach (get_array('editorBtns') as $k=>$v):?><?php echo $v['name']?>,<?php endforeach;?>",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		gallerycon_settings :{
			urls :{
				galleries : 'https://picasaweb.google.com/data/feed/api/user/<?php qoolconfig('social','googleusername')?>?kind=album&access=public&alt=json',
				images : '{gallery_id}',
				image : '{image_id}',
				img_src: '{image_id}'
			},
			sizes :[{
				id : 'event_thumb',
				name : 'Tiny thumbnail'
			}],
			default_size : 'thumbnail',
			default_alignment : 'left',
			link :{
				rel : 'lightbox-{gallery_id}', // can_have {image_id}, {gallery_id} and {size_id} placeholders
				class : 'gallery', // can_have {image_id}, {gallery_id} and {size_id} placeholders
				size : 'litebox', // Either size or href should be set
				href : '{image_id}'
			}
		},
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
