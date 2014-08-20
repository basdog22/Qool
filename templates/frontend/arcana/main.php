<!-- Banner -->
<div class="row">
	<div class="10u">
		
		 <div id="wrapper">
			 <div class="slider-wrapper theme-default">
				<div id="banner">
					<?php $banners = get_random('Banner',5); foreach ($banners as $banner):?>
					<a href="<?php echo $banner['url']?>"><img src="<?php qoolinfo('home')?>/<?php echo $banner['image']?>" title=" <?php echo $banner['alt']?>" alt="" /></a>
					<?php endforeach;?>
				</div>
				
			</div>
		</div>
	</div>
	<div class="2u">
		<section>
		<?php widget('right_main_top')?>
		</section>
	</div>
</div>
<!---->
<!-- Features -->

<div class="row">
	<div class="3u">
		<section class="first">
			<?php widget('top_box1')?>
		</section>							
	</div>
	<div class="3u">
		<section>
			<?php widget('top_box2')?>
		</section>							
	</div>
	<div class="3u">
		<section>
			<?php widget('top_box3')?>
		</section>							
	</div>
	<div class="3u">
		<section class="last">
			<?php widget('top_box4')?>
		</section>							
	</div>
</div>

<!-- Divider -->

<div class="row">
	<div class="12u">
		<div class="divider divider-top"></div>
	</div>
</div>

<!-- Highlight Box -->

<div class="row">
	<div class="12u">
		<div class="highlight-box">
			<?php widget('highlight')?>
		</div>
	</div>
</div>





<!-- Divider -->

<div class="row">
	<div class="12u">
		<div class="divider divider-top"></div>
	</div>
</div>

