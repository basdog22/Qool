<?php message()?>
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
	<td width="80%"><?php echo $v['title']?></td>
	<td ><a class="btn btn-info" target="_blank" href='<?php echo $v['link']?>'><i class=" icon-edit"> </i><?php t("Visit")?></a></td>
</tr>
<?php endforeach;?>
</table>
