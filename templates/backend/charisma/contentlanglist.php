<?php message()?>
<div class="sortable">
<?php $list = the_list();?>
<div class="row-fluid ">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-bullhorn"></i> <?php t('Content Languages')?></h2>
			<div class="box-icon">
				<a href="#" class="btn btn-minimize"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t('Title')?></th>
		<th colspan="4"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach ($list['available']['language'] as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="40%"><?php t(ucfirst($v['name']))?></td>
	<td><span class="badge badge-<?php if($v['name']==$list['language']):$m = t('Default',0)?>success<?php else:$m = t('Available',0)?><?php endif;?>"><?php echo $m?></span></td>
	<td>
		<?php if($v['name']!=$list['language']):?>
		<a class="btn btn-success" href='<?php qoolinfo('home')?>/admin/setdefaultcontentlang?id=<?php echo $v['name']?>'><i class="icon-star"> </i> <?php t("Default")?></a>
		<?php endif;?>
	</td>

	<td>
	<?php if($v['name']!=$list['language']):?>
	<a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/delcontentlang?id=<?php echo $v['name']?>'><i class=" icon-remove"> </i><?php t("Delete")?></a>
	<?php endif;?></td>
</tr>
<?php endforeach;?>
</table>
		</div>
	</div><!--/span-->

</div><!--/row-->

</div>