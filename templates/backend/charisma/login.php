<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php site('backend_title')?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Le styles -->
    <link href="<?php qoolinfo('home')?>/lib/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
    <link href="<?php qoolinfo('home')?>/lib/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>

  <body>
    <div class="container-fluid">
    <?php message();?>
    <div class="row-fluid">
    <div class="span4"></div>
    </div>
      <div class="row-fluid">
      <div class="span4"></div>
        <div class="span4 well">
        	<?php t("Login")?>
        	<?php showForm('loginForm');?>
        </div><!--/span-->
        <div class="span4"></div>
      </div><!--/row-->
      <hr>
      <footer>
        <p>&copy; <?php site('backend_title')?></p>
      </footer>

    </div><!--/.fluid-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php qoolinfo('home')?>/lib/js/jquery-1.8.0.min.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-transition.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-alert.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-modal.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-dropdown.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-scrollspy.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-tab.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-tooltip.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-popover.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-button.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-collapse.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-carousel.js"></script>
    <script src="<?php qoolinfo('home')?>/lib/js/bootstrap-typeahead.js"></script>

  </body>
</html>
