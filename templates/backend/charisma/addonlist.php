<?php message()?>
<div class="sortable">
<?php foreach ($list = the_list() as $ko=>$vo):?>
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-list-alt"></i> <?php t(ucfirst($ko));?></h2>
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
		<th><?php t("Settings")?></th>
		<th><?php t("User Level")?></th>
		<th><?php t("Admin Level")?></th>
		<th><?php t("Cache Time")?></th>
		<th><?php t("State")?></th>
		<th><?php t("Activate/Deactivate")?></th>
		<th><?php t("Config")?></th>
	</tr>
</thead>
<?php foreach ($vo['addon'] as $k=>$v):?>
<?php if($v['@attributes']['name']):?>
<tr>
	<td><?php echo ($k)?></td>
	<td width="27%">
	<?php if($v['@attributes']['parent']=='none'):?>
	<a class="btn btn-info qoolmodal" data-toggle="modal" title="<?php echo ucfirst($v['@attributes']['name'])?> <?php t("Information")?>" data-target="#myModal" href="<?php qoolinfo('home')?>/admin/<?php echo $ko?>info?id=<?php echo $v['@attributes']['name']?>"><i class="icon-info-sign icon-white"> </i> (<?php echo $v['@attributes']['name']?>) <?php echo ucfirst($v['@attributes']['title'])?></strong></a>
	<?php else:?>
	<a class="btn btn-info disabled"><i class="icon-info-sign icon-white"> </i> (<?php echo $v['@attributes']['name']?>) <?php echo ucfirst($v['@attributes']['title'])?></strong></a>
	<?php endif;?>
	</td>
	<td>
	<?php if($v['@attributes']['state']=='installed'):?>
		<a class="btn btn-info qoolmodal" data-toggle="modal" title="<?php echo ucfirst($v['@attributes']['name'])?> <?php t("Addon Settings")?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/<?php echo $ko?>settings?id=<?php echo $v['@attributes']['name']?>&lvl=<?php echo $v['@attributes']['level'];?>&alvl=<?php echo $v['@attributes']['adminlevel'];?>&cache=<?php echo $v['@attributes']['cachetime'];?>'><i class="icon-edit"> </i> <?php t("Settings")?></a>
	<?php endif;?>
	</td>
	<td><span class="badge"><?php echo $v['@attributes']['level'];?></span></td>
	<td><span class="badge"><?php echo $v['@attributes']['adminlevel'];?></span></td>
	<td><span class="badge"><?php echo $v['@attributes']['cachetime'];?></span></td>
	<td><span class="badge badge-<?php if($v['@attributes']['state']=='installed'):$m = t('Active',0)?>success<?php else:$m = t('Inactive',0)?><?php endif;?>"><?php echo $m?></span></td>
	<td>
		<?php if($v['@attributes']['parent']=='none'):?>
		<?php if($v['@attributes']['state']!='installed'):?>
		<a class="btn btn-success" href='<?php qoolinfo('home')?>/admin/activate<?php echo $ko?>?id=<?php echo $v['@attributes']['name']?>'><i class="icon-star"> </i> <?php t("Activate")?></a>
		<?php else:?>
		<a class="btn btn-danger" href='<?php qoolinfo('home')?>/admin/deactivate<?php echo $ko?>?id=<?php echo $v['@attributes']['name']?>'><i class="icon-remove"> </i> <?php t("Deactivate")?></a>
		<?php endif;?>
		<?php else:?>
		<span class="badge badge-info pop" data-content="<?php t("Child of an addon or a module.Activated when parent is active")?>" data-original-title="<?php t("Parent of addon")?> '<?php echo $v['@attributes']['parent']?>'"><?php t("Parent")?>: <?php echo $v['@attributes']['parent']?></span>
		<?php endif;?>
	</td>
	<td>
	<?php if($v['@attributes']['parent']=='none'):?>
	<?php if($v['@attributes']['state']=='installed'):?>
		<a class="btn btn-warning qoolmodal" data-toggle="modal" data-target="#myModal" title="<?php echo ucfirst($v['@attributes']['name'])?> <?php t("Configuration")?>" href='<?php qoolinfo('home')?>/admin/<?php echo $ko?>config?id=<?php echo $v['@attributes']['name']?>'><i class=" icon-edit"> </i> <?php t("Config")?></a>
	<?php endif;?>	
	<?php endif;?>	
	</td>	
</tr>
<?php endif;?>
<?php endforeach;?>

</table>
		</div>
	</div><!--/span-->

</div><!--/row-->

<?php endforeach;?>

</div>