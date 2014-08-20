<?php message()?>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-filter"></i> <?php t('Menu Items');?></h2>
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
		<th colspan="2"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach (the_list() as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="70%"><a title="<?php t("Edit Menu Item")?> <?php echo $v['title']?>" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><?php echo $v['title']?></a></td>
	<td ><a title="<?php t("Edit Menu Item")?> <?php echo $v['title']?>" class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>&menu_id=<?php echo $v['menu_id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php foreach ($v['kids'] as $b):?>
<tr>
	<td></td>
	<td width="70%"><a class="indent1" title="<?php t("Edit Menu Item")?> <?php echo $b['title']?>" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $b['id']?>'><?php echo $b['title']?></a></td>
	<td ><a title="<?php t("Edit Menu Item")?> <?php echo $b['title']?>" class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $b['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $b['id']?>&menu_id=<?php echo $v['menu_id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php foreach ($b['kids'] as $a):?>
<tr>
	<td></td>
	<td width="70%"><a class="indent2" title="<?php t("Edit Menu Item")?> <?php echo $a['title']?>" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $a['id']?>'><?php echo $a['title']?></a></td>
	<td ><a title="<?php t("Edit Menu Item")?> <?php echo $a['title']?>" class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $a['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $a['id']?>&menu_id=<?php echo $v['menu_id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php foreach ($a['kids'] as $s):?>
<tr>
	<td></td>
	<td width="70%"><a class="indent3" title="<?php t("Edit Menu Item")?> <?php echo $s['title']?>" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $s['id']?>'><?php echo $s['title']?></a></td>
	<td ><a title="<?php t("Edit Menu Item")?> <?php echo $s['title']?>" class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $s['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $s['id']?>&menu_id=<?php echo $v['menu_id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php foreach ($s['kids'] as $d):?>
<tr>
	<td></td>
	<td width="70%"><a class="indent4" title="<?php t("Edit Menu Item")?> <?php echo $d['title']?>" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $d['id']?>'><?php echo $d['title']?></a></td>
	<td ><a title="<?php t("Edit Menu Item")?> <?php echo $d['title']?>" class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $d['id']?>'><i class=" icon-edit"> </i><?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $d['id']?>&menu_id=<?php echo $v['menu_id']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a></td>
</tr>
<?php endforeach;?>
<?php endforeach;?>
<?php endforeach;?>
<?php endforeach;?>
<?php endforeach;?>
</table>

		</div>
	</div><!--/span-->

</div><!--/row-->

