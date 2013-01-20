<div id="qoolbar" class="navbar navbar-fixed-<?php echo $position?>">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php qoolinfo('home')?>/admin/"><?php t('Dashboard')?></a>
          <div class="btn-group pull-right">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-user"></i> <?php user('username')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="<?php qoolinfo('home')?>/profiles/me"><?php t('Profile')?></a></li>
              <li class="divider"></li>
              <li><a href="<?php qoolinfo('home')?>/logout"><?php t('Logout')?></a></li>
            </ul>
          </div>
          <?php if(count(get_array('addonCreationActions'))>0):?>
          <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-plus"></i> <?php t("New")?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php foreach(get_array('addonCreationActions') as $k=>$v):?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php echo t($v)?></a></li>
              <?php endforeach;?>
            </ul>
          </div>
          <?php endif;?>
          <?php $menus = get_array('adminmenus')?>
		   <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-file"></i> <?php t('Content')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <?php $i = 0; foreach ($menus['content'] as $k=>$v):$i++?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
              <?php if(count($menus['content'])>$i):?><li class="divider"></li><?php endif;?>
           	<?php endforeach;?>
            </ul>
          </div>
		  <div class="btn-group pull-left">
            <a class="btn dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-wrench"></i> <?php t('System')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
             <?php $i = 0; foreach ($menus['system'] as $k=>$v):$i++?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v);?> </a></li>
              <?php if(count($menus['system'])>$i):?><li class="divider"></li><?php endif;?>
           	<?php endforeach;?>
            </ul>
          </div>
          <?php if(isActive('moduleMenu')):?>
          <div class="btn-group pull-left">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown"  >
              <i class="icon-cog icon-white"></i> <?php t('Options')?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
            <?php foreach (get_array('moduleMenu') as $k=>$v):?>
              <li><a href="<?php qoolinfo('home')?>/admin/<?php echo $k?>"><?php t($v); ?> </a></li>
            <?php endforeach;?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if(isActive('hasForm')):?>
          <a id="formSubmiter" class="btn btn-primary" href="#"><?php t("Save")?></a>
          <?php endif;?>
          <?php if(isActive('isEditor')):?>
          <a id="toggler" class="btn btn-primary" href="#"><?php t("HTML")?></a>
          <?php endif;?>
          
		  <form action="<?php qoolinfo('home')?>/admin/search" class="navbar-search pull-right">
			<input name="q" type="text" class="search-query span2" placeholder="<?php t('Search')?>">
		  </form>
        </div>
      </div>
    </div>
    <script src="<?php qoolinfo('home')?>/lib/js/jquery-1.8.0.min.js"></script>
	<script src="<?php qoolinfo('home')?>/lib/js/bootstrap-dropdown.js"></script>