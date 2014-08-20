<div class="row-fluid">
	<div class="box span12">
	  <div class="box-header well" data-original-title>
		  <h2><i class="icon-calendar"></i><?php t('Calendar')?></h2>
		  <div class="box-icon">
			  <a class="qoolmodal btn btn-round" href="<?php qoolinfo('home')?>/admin/addcalendar" data-target="#myModal" data-toggle="modal" title="<?php t('Add Calendar')?>" ><i class="icon-plus"></i></a>
			  <a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
			</div>
	  </div>
	  <div class="box-content">
		<div id="external-events" class="well">
			<h4><?php t("Calendars")?></h4>
			<div class="divider">&nbsp;</div>
			<div class="label label-info"><?php t('Qool Tasks')?></div>
			<?php foreach (get_array('googleCalendars') as $k=>$v):?>
			<div class="divider">&nbsp;</div>
			<div class="calendarSource label label-<?php echo $v['calendar_class']?>" data-source="<?php echo $v['calendar_address']?>"><?php echo $v['calendar_title']?> <a rev="general_data" id="<?php echo $v['id']?>" title="<?php t("Delete")?>" href="#" class="btn ajaxdelete btn-danger btn-closesmall btn-round"><i class="icon-remove icon-white"></i></a></div>
			
			<?php endforeach;?>
			
			</div>

			<div id="calendar"></div>

			<div class="clearfix"></div>
		</div>
	</div>
</div><!--/row-->
