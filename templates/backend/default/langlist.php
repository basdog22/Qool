<?php message()?>
<?php foreach ($list = the_list() as $ko=>$vo):?>
<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t(ucfirst($ko))?></th>
		<th colspan="4"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach ($vo['available']['language'] as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="40%"><a href='<?php qoolinfo('home')?>/admin/editlang?id=<?php echo $v?>'><?php echo ucfirst($v)?></a></td>
	<td><span class="badge badge-<?php if($v==$vo['language']):$m = t('Default',0)?>success<?php else:$m = t('Available',0)?><?php endif;?>"><?php echo $m?></span></td>
	<td>
		<?php if($v!=$vo['language']):?>
		<a class="btn btn-success" href='<?php qoolinfo('home')?>/admin/setdefault<?php echo $ko?>lang?id=<?php echo $v?>'><i class="icon-star"> </i> <?php t("Default")?></a>
		<?php endif;?>
	</td>
	<td><a class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/editlang?id=<?php echo $v?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td>
	<?php if($v!=$vo['language']):?>
	<a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php echo $ko?>lang?id=<?php echo $v?>'><i class=" icon-remove"> </i><?php t("Delete")?></a>
	<?php endif;?></td>
</tr>
<?php endforeach;?>
</table>
<?php endforeach;?>

