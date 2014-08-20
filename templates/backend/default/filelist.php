<?php message()?>
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
