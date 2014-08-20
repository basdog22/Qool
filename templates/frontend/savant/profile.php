<div id="main">
	<div class="5grid">
		<div class="main-row">
			<div class="4u-first">

				<section>
					<?php widget('highlight','h2')?>
				</section>
			
				<section>
					<h2>About being Qool</h2>
					<div class="6u-first">
						<?php widget('top_box2','h2')?>
					</div>
					<div class="6u">
						<?php widget('top_box3','h2')?>
					</div>
				</section>
				
			</div>
			<div class="8u">

				<section class="right-content">
					<div class="6u-first">
					<?php $user =$this->user?>
						<h3><?php echo $user['username']?></h3>
						<dl>
						<?php foreach ($user['data'] as $k=>$v):?>
						<dt><?php echo $k?></dt>
						<dd><?php echo $v?></dd>
						<?php endforeach;?>
						</dl>
					</div>
						<div class="6u">					
						<?php echo $this->theForm?>
						</div>
				</section>
			
			</div>
		</div>
	</div>
</div>