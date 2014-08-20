<?php message()?>
<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t("Title")?></th>
		<th><?php t("Data Type")?></th>
		<th colspan="2"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach (the_list() as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="70%"><a class="qoolmodal" data-toggle="modal" title="<?php t("Edit User Field")?> <?php echo $v['title']?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><?php echo $v['title']?></a></td>
	<td><?php echo $v['field_type']?></td>
	<td ><a data-toggle="modal" title="<?php t("Edit User Field")?> <?php echo $v['title']?>" data-target="#myModal" class="btn btn-warning qoolmodal" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php endforeach;?>
</table>
