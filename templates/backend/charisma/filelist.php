<?php message()?>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-picture"></i> <?php t('Theme Files');?></h2>
			<div class="box-icon">
				<a href="#" class="btn btn-minimize"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t("Title")?></th>
		<th ><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach (the_list() as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="80%"><a href='<?php qoolinfo('home')?>/admin/edit<?php echo $v['type']?><?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><?php echo $v['title']?></a></td>
	<td ><a class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php echo $v['type']?><?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-edit"> </i> 
	<?php if($v['type']=='file'):?>
	<?php t("Edit")?>
	<?php else:?>
	<?php t("Browse")?>
	<?php endif;?>
	</a></td>
</tr>
<?php endforeach;?>
</table>

		</div>
	</div><!--/span-->

</div><!--/row-->
