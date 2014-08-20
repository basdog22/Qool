<div class="row tumbnails">
	<div class="3u">
		<?php mywidget('top_box1')?>
	</div>
	<div class="9u maintext">
	<?php $i=0?>
	<div class="3u">
	<ul>
		<?php foreach (get_array('content') as $post):?>
		<?php if($i==10):?></ul></div><div class="4u"><ul><?php $i=0; endif;?>
			<li><a href="<?php qoolinfo('home')?>/hooks/<?php echo $post['slug']?>"><?php echo $post['title']?></a></li>
		<?php $i++; endforeach;?>
	</ul>
	</div>
	</div>
</div>
<?php pager();?>