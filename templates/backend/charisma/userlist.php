<?php message()?>
<div class="row-fluid sortable">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-user"></i> <?php t('Users List');?></h2>
			<div class="box-icon">
				<a href="#" class="btn btn-minimize"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			
<table class="table table-striped table-bordered table-condensed">
<thead>
	<tr>
		<th>#</th>
		<th><?php t("Username")?></th>
		<th><?php t("Email")?></th>
		<th><?php t("Role")?></th>
		<th colspan="2"><?php t("Actions")?></th>
	</tr>
</thead>
<?php foreach (the_list() as $k=>$v):?>
<tr>
	<td><?php echo ($k+1)?></td>
	<td width="40%"><a class="qoolmodal" data-toggle="modal" title="<?php t("Edit User")?> <?php echo $v['username']?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><?php echo $v['username']?></a></td>
	<td><a class="btn btn-info qoolmodal" data-size="800" data-toggle="modal" title="<?php t("Email User")?> <?php echo $v['username']?>" data-target="#myModal"  href="<?php qoolinfo('home')?>/admin/mailto?mail=<?php echo $v['email']?>"><i class="icon-envelope icon-white"> </i> <?php echo $v['email']?></a></td>
	<td><?php echo $v['title']?></td>
	<td ><a class="btn btn-warning qoolmodal" data-toggle="modal" title="<?php t("Edit User")?> <?php echo $v['username']?>" data-target="#myModal" href='<?php qoolinfo('home')?>/admin/edit<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-edit"> </i> <?php t("Edit")?></a></td>
	<td ><a class="btn btn-danger warnme" title="<?php t('Delete')?>"  rel="<?php t('Please revise your action. Are you sure you want to do this?')?>" href='<?php qoolinfo('home')?>/admin/del<?php qoolinfo('theaction')?>?id=<?php echo $v['id']?>'><i class=" icon-remove"> </i> <?php t("Delete")?></a></td>
</tr>
<?php endforeach;?>
</table>

		</div>
	</div><!--/span-->

</div><!--/row-->
