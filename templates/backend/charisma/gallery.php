<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-picture"></i> <?php t('Image Gallery')?></h2>
			<div class="box-icon">
				<a href="<?php qoolinfo('home')?>/admin/gallerysettings" title="<?php t('Gallery Settings')?>" data-toggle="modal" class="qoolmodal btn btn-setting btn-round"><i class="icon-cog"></i></a>
				<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content1">
			<p class="center">
				<button id="toggle-fullscreen" class="btn btn-large btn-primary visible-desktop" data-toggle="button"><?php t('Toggle Fullscreen')?></button>
			</p>
			<br/>
			<ul id="newitemsholder" class="thumbnails gallery">
			<?php $i=0; foreach (get_array('galleryimages') as $k=>$v): $i++?>
			<li id="image-<?php echo $i?>" class="thumbnail" data-src="<?php echo $k?>">
				<a style="background:url('<?php echo $v?>')" class="clboxer" href="<?php echo $v?>">
					<img class="grayscale" src="<?php echo $v?>" alt="Image <?php echo $i?>" />
				</a>
			</li>
			<?php endforeach;?>
			</ul>
		</div>
	</div><!--/span-->

</div><!--/row-->	
<div class="row-fluid">
	<div class="box span12">
		<div class="box-header well">
			<h2><i class="icon-picture"></i> <?php t('Image Upload')?></h2>
			<div class="box-icon">
				<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
			</div>
		</div>
		<div class="box-content">
			<p class="center">
				<label for="uploader"><?php t("Upload file")?></label>
				<input type="file" id="uploader">
			</p>
			<br/>
			<div class="uploadlistener">
				<div class="well"><?php t('Drop files here to upload')?></div>
			</div>
		</div>
	</div><!--/span-->
</div>