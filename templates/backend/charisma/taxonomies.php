<?php message()?>
<?php if(isActive('previous_taxonomy')):$prev=tpl('previous_taxonomy',0);?>
<ul class="breadcrumb">
	<li>
	<a href="<?php qoolinfo('home')?>/admin/taxonomies"><?php t("Taxonomies")?></a> <span class="divider">/</span>
	</li>
	<?php foreach ($prev as $k=>$v):?>
    <li>
    <a href="<?php qoolinfo('home')?>/admin/taxonomies?id=<?php echo $v['id']?>"><?php echo $v['title']?></a> <span class="divider">/</span>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif;?>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-tags"></i> <?php t('Taxonomies');?></h2>
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
		<th><?php t("Type")?></th>
		<th colspan="2"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach (the_list() as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="40%"><a href='<?php qoolinfo('home')?>/admin/<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><?php echo $v['title']?></a></td>
	<td><?php echo $v['type_name']?></td>
	<td ><a class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php endforeach;?>
</table>
		</div>
	</div><!--/span-->

</div><!--/row-->


