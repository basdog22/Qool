<?php message()?>
<?php foreach ($list = the_list() as $ko=>$vo):?>
<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t(ucfirst($ko))?></th>
		<th><?php t("Template Engine")?></th>
		<th colspan="3"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach ($vo as $k=>$v):?>
<?php if($v['engine']!=''):?>
<tr>
	<td><?php echo ($k)?></td>
	<td width="50%"><a class="btn btn-info qoolmodal" data-toggle="modal" title="<?php echo ucfirst($v['title'])?> <?php t("Information")?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/themeinfo?id=<?php echo $v['title']?>'><?php echo ucfirst($v['title'])?></a></td>
	<td><span class="badge"><?php echo $v['engine']?></span></td>
	<td>
		<?php if($v['title']!=tpl('default',0)):?>
		<a class="btn btn-success" href='<?php qoolinfo('home')?>/admin/setdefaulttheme?id=<?php echo $v['title']?>&engine=<?php echo $v['engine']?>'><i class="icon-star"> </i> <?php t("Default")?></a>
		<?php endif;?>
	</td>
	<td><a class="btn btn-warning" href='<?php qoolinfo('home')?>/admin/edittheme?id=<?php echo $v['title']?>'><i class=" icon-edit"> </i> <?php t("Edit")?></a></td>
	<td>
	<a class="btn btn-primary qoolmodal" data-toggle="modal" title="<?php t("Template Settings")?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/themeconfig?id=<?php echo $v['title']?>'><i class="icon-list-alt icon-white"> </i> <?php t("Config")?></a>
	</td>
</tr>
<?php endif;?>
<?php endforeach;?>
</table>
<?php endforeach;?>