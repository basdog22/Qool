<?php message()?>
<?php $boxes = get_array('boardboxes');?>
<div class="sortableboxes row-fluid">
<?php foreach ($boxes as $k=>$v):?>
	<a id="<?php echo $k?>" data-rel="tooltip" title="<?php echo $v['content']['new']?> <?php t($v['string'])?>" class="well span3 top-block" href="<?php qoolinfo('home')?>/admin/<?php echo $v['link']?>">
		<span class="<?php echo $v['class']?>"></span>
		<div><?php echo $v['title']?></div>
		<div><?php echo $v['content']['all']?></div>
		<span class="notification"><?php echo $v['content']['new']?></span>
	</a>
<?php endforeach; ?>
</div>
<?php $widgets = get_array('boardwidgets');?>
<div class="tabbable tabs-left"> <!-- Only required for left/right tabs -->
    <ul id="navtabs" class="nav nav-tabs">
    <?php $i = 0; foreach ($widgets as $k=>$v):$i++?>
    <li  <?php if($i===1):?>class="active"<?php endif;?>><a href="#<?php echo $k?>" data-toggle="tab"><?php t(ucfirst($k))?></a></li>
    <?php endforeach;?>
    </ul>
    <div class="tab-content">
    
     <?php $i = 0; foreach ($widgets as $a=>$b):$i++?>
     	<div class="tab-pane sortablewidgets <?php if($i===1):?>active<?php endif;?>" id="<?php echo $a?>">
     	<?php if($a=='feeds'):?>
     	<a data-target="#myModal" title="<?php echo t("New Shortcut")?>" data-toggle="modal" id="addNewShortCut" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newfeed"><i class="icon-plus"> </i> <?php echo t("New Feed")?></a>

     	<?php endif;?>
	    <?php foreach ($widgets[$a] as $k=>$v):?>
	    <div class="box span4" id="<?php echo $a."_".$k?>">
	    	<div class="box-header well">
				<h2><i class="icon-th"></i><?php echo $v['title']?></h2>
				<div class="box-icon">
					<a href="#" class="btn btn-minimize "><i class="icon-chevron-up"></i></a>
					 <?php if($v['type']=='userfeed'):?><a rev="general_data" id="<?php echo $v['id']?>" title="Διαγραφή" href="#" class="btn ajaxdelete btn-close"><i class="icon-remove"></i></a><?php endif;?>
				</div>
			</div>
			<div class="box-content">
			<?php echo $v['content']?>
			</div>
	    	
	    </div>
	    <?php endforeach;?> 
	 </div>
	 <?php endforeach;?>
    </div>
</div>
			