<?php message()?>
<?php $widgets = get_array('boardwidgets');?>
<div class="tabbable tabs-left"> <!-- Only required for left/right tabs -->
    <ul class="nav nav-tabs">
    <?php $i = 0; foreach ($widgets as $k=>$v):$i++?>
    <li  <?php if($i===1):?>class="active"<?php endif;?>><a href="#<?php echo $k?>" data-toggle="tab"><?php t($k)?></a></li>
    <?php endforeach;?>
    </ul>
    <div class="tab-content">
    
     <?php $i = 0; foreach ($widgets as $a=>$b):$i++?>
     	<div class="tab-pane <?php if($i===1):?>active<?php endif;?>" id="<?php echo $a?>">
     	<?php if($a=='feeds'):?>
     	<a data-target="#myModal" title="<?php echo t("New Shortcut")?>" data-toggle="modal" id="addNewShortCut" class="qoolmodal" href="<?php qoolinfo('home')?>/admin/newfeed"><i class="icon-plus"> </i> <?php echo t("New Feed")?></a>

     	<?php endif;?>
	    <?php foreach ($widgets[$a] as $k=>$v):?>
	    <div class="boardwidget span4">
	    	<h4 class="navbar-inner miniheight"><?php echo $v['title']?><?php if($v['type']=='userfeed'):?>
	    	<div class="btn-group pull-right">
	    	<a rev="general_data" id="<?php echo $v['id']?>" title="Διαγραφή" class="btn ajaxdelete  btn-inverse" href="#">×</a>
	    	</div>
	    	<?php endif;?>
	    	</h4>
	    	<div class="wellin"><?php echo $v['content']?></div>
	    </div>
	    <?php endforeach;?> 
	 </div>
	 <?php endforeach;?>
    </div>
</div>