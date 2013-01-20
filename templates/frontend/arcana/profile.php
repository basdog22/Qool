<div class="row thumbnails">
	<div class="6u">
	<?php $user =get_array('user')?>
		<h3><?php echo $user['username']?></h3>
		<dl>
		<?php foreach ($user['data'] as $k=>$v):?>
		<dt><?php echo $k?></dt>
		<dd><?php echo $v?></dd>
		<?php endforeach;?>
		</dl>
	</div>
	<?php if(isActive('formTitle')):?>
	<div class="6u">
		<?php showForm('theForm')?>
	</div>
	<?php endif;?>
</div>