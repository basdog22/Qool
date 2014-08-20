<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-th-large"></i> <?php t('Widgets');?></h2>
			<div class="box-icon">
				<a href="#" class="btn btn-minimize"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			<div class="span2 ">
	<div class="">
		<h6><?php t("Available widgets")?></h6>	
	</div>
	<div class="well well-small span12" style="min-height:350px">
	<?php foreach (get_array('widgetList') as $k=>$v):?>
		<div class="drg span12  dspan">
			<div id="<?php echo $v['name']?>" class="label label-inverse"><i class="icon-move icon-white"> </i> <?php echo $v['title']?></div>
		</div>
	<?php endforeach; ?>
	</div>
</div>
<div class="span10 span9-minushalf">
	<div class="span12">
		<p><?php t("Available slots")?></p>	
	</div>
	<?php foreach (get_array('slotList') as $k=>$v):?>
		<div  class="span3 tspan">
			<h6><?php echo $v['title']?></h6>
			<div class="drop well well-small minheightwell" id="<?php echo $v['id']?>">
				<?php foreach (get_array('widgetList') as $ko=>$vo):?>
				<?php if($vo['name']==$v['widget']):?>
					<div class="alert"><div id="<?php echo $vo['name']?>" class="label label-inverse drg placed"><i class="icon-move icon-white"> </i> <?php echo $vo['title']?></div><a data-id="<?php echo $vo['name']?>" class='close' data-dismiss='alert' href='#'>&times;</a></div>
				<?php endif;?>
				<?php endforeach;?>
			</div>
		</div>
		
	<?php endforeach;?>
</div>
		</div>
	</div><!--/span-->

</div><!--/row-->
<div class="alert hide" id="msg"></div>