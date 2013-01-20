<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Qool CMS v2.0 Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Le styles -->
    <link href="lib/css/bootstrap.css" rel="stylesheet">
    <link href="lib/css/qool.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
	  .active{
		font-weight:bold;
	  }
    </style>
    <link href="lib/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span2">
          <div class="well sidebar-nav">
			<ul class="nav nav-list">
				<li class="nav-header">Installation</li>
				<li <?php if($step=='init'):?>class="active"<?php endif;?>>Host Settings</li>
				<li <?php if($step=='db'):?>class="active"<?php endif;?>>Database Settings</li>
				<li <?php if($step=='site'):?>class="active"<?php endif;?>>Site Settings</li>
				<li <?php if($step=='user'):?>class="active"<?php endif;?>>User Settings</li>
				<li <?php if($step=='complete'):?>class="active"<?php endif;?>>Installation Summary</li>
			</ul>
		</div>
        </div><!--/span-->
        <div class="span10">
         
          <div class="row-fluid">
          	<div class="well span12">
           	<?php switch ($step): case "init":?>
				<form action="./" method="POST" class="form-horizontal">
				<input type="hidden" name="formstep" value="host"/>
				<input type="hidden" name="separator" value="/"/>
				<fieldset>
					<legend>Host settings</legend>
					<div class="control-group">
						<label class="control-label" for="http">Protocol</label>
						<div class="controls">
							<select class="span2" id="http" name="http">
				                <option selected value="http://">HTTP</option>
				                <option value="https://">HTTPS</option>
				            </select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="subdomain">Sub Domain</label>
						<div class="controls">
							<input type="text" class="span3" id="subdomain" name="subdomain" value="" />
							<p class="help-block">The subdomain. You can use "www." here. Append a dot (.) to the subdomain</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="domain">Domain</label>
						<div class="controls">
							<input type="text" class="span3" id="domain" name="domain" value="" />
							<p class="help-block">Fill in your domain here. No trailing slash</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="folder">Folder</label>
						<div class="controls">
							<input type="text" class="span3" id="folder" name="folder" value="" />
							<p class="help-block">If installing anywhere else than your root (public_html) folder (prepend your folder with "/")</p>
						</div>
					</div>
					<div class="form-actions">
			            <button class="btn btn-primary" type="submit">Go to next step</button>
			            <button class="btn">Cancel</button>
			          </div>
				</fieldset>
				</form>
			<?php break;?>
			<?php case "db":?>
			<form action="./" method="POST" class="form-horizontal">
				<input type="hidden" name="formstep" value="db"/>
				<fieldset>
					<legend>Database settings</legend>
					<div class="control-group">
						<label class="control-label" for="type">SQL Type</label>
						<div class="controls">
							<select class="span2" id="type" name="type">
				                <option selected value="mysql">MySQL</option>
				                <option value="sqlite">SQLite</option>
				            </select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="host">Host</label>
						<div class="controls">
							<input type="text" class="span3" id="host" name="host" value="localhost" />
							<p class="help-block">Most of the time it is 'localhost'</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="username">DB User</label>
						<div class="controls">
							<input type="text" class="span3" id="username" name="username" value="" />
							<p class="help-block">Your database user</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="password">DB Password</label>
						<div class="controls">
							<input type="text" class="span3" id="password" name="password" value="" />
							<p class="help-block">Your database user password</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="db">Database Name</label>
						<div class="controls">
							<input type="text" class="span3" id="db" name="db" value="qool_cms" />
							<p class="help-block">Your database name</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="prefix">Tables prefix</label>
						<div class="controls">
							<input type="text" class="span3" id="prefix" name="prefix" value="qool_" />
							<p class="help-block">Please change this to something else. (lowercase letters and '_' only)</p>
						</div>
					</div>
					
					<div id="accordion2" class="accordion">
			            <div class="accordion-group">
			              <div class="accordion-heading">
			                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle">
			                  Simple Database Installation
			                </a>
			              </div>
			              <div class="accordion-body in collapse" id="collapseOne" style="height: auto;">
			                <div class="accordion-inner">
			                  This will install Qool with the default database table names. Use this if you need less security and easier setup.
			                </div>
			              </div>
			            </div>
			            <div class="accordion-group">
			              <div class="accordion-heading">
			                <a href="#collapseTwo" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle">
			                  Advanced Database Installation.
			                </a>
			              </div>
			              <div class="accordion-body collapse" id="collapseTwo" style="height: 0px;">
			                <div class="accordion-inner">
							<?php foreach ($tables as $k=>$v):?>
							<div class="control-group">
								<label class="control-label" for="<?php echo $v['name']?>">Table: <?php echo $v['name']?></label>
								<div class="controls">
									<input type="text" class="span3" id="<?php echo $v['name']?>" name="<?php echo $v['name']?>" value="<?php echo $v['name']?>" />
									<p class="help-block">Lowercase only!</p>
								</div>
							</div>
							<?php endforeach;?>
			                </div>
			              </div>
			            </div>
			          </div>
					<div class="form-actions">
			            <button class="btn btn-primary" type="submit">Go to next step</button>
			            <button class="btn">Cancel</button>
			          </div>
				</fieldset>
				</form>
			<?php break;?>
			<?php case "site":?>
			<form action="./" method="POST" class="form-horizontal">
				<input type="hidden" name="formstep" value="site"/>
				<fieldset>
					<legend>Site settings</legend>
					<div class="control-group">
						<label class="control-label" for="frontend_title">Site Title</label>
						<div class="controls">
							<input type="text" class="span3" id="frontend_title" name="frontend_title" value="My new site is powered by Qool CMS v2.0" />
							<p class="help-block">Choose a nice title for your site</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="slogan">Slogan</label>
						<div class="controls">
							<input type="text" class="span3" id="slogan" name="slogan" value="and i am proud!" />
							<p class="help-block">Use this line of text as a slogan</p>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<p class="help-block">There are much more options to configure in the admin panel. These are enough for now</p>
						</div>
					</div>
					<div class="form-actions">
			            <button class="btn btn-primary" type="submit">Go to next step</button>
			            <button class="btn">Cancel</button>
			          </div>
				</fieldset>
				</form>
			<?php break;?>
			<?php case "user":?>
			<form action="./" method="POST" class="form-horizontal">
				<input type="hidden" name="formstep" value="user"/>
				<fieldset>
					<legend>User settings</legend>
					<div class="control-group">
						<label class="control-label" for="username">Username</label>
						<div class="controls">
							<input type="text" class="span3" id="username" name="username" value="Admin" />
							<p class="help-block">This is the admin username. Please choose something else than admin</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="password">User Password</label>
						<div class="controls">
							<input type="text" class="span3" id="password" name="password" value="<?php echo $salt;?>" />
							<p class="help-block">Please choose a password for this user. The hardest the best. We have created one for you already. Write this down if you plan to use it.</p>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="email">Your email</label>
						<div class="controls">
							<input type="text" class="span3" id="email" name="email" value="admin@<?php echo $domain?>" />
							<p class="help-block">This will be the email account for this user</p>
						</div>
					</div>
					<div class="form-actions">
			            <button class="btn btn-primary" type="submit">Go to next step</button>
			            <button class="btn">Cancel</button>
			          </div>
				</fieldset>
				</form>
			<?php break;?>
			<?php case "complete":?>
			    <div class="hero-unit">
				    <h1>Congratulations!!!</h1>
				    <p>You have just installed Qool CMS v2.0<br/>
				    You can now choose what to do. You can click on the red button which will give you the default Qool homepage or you can click on the blue button
				    to go to the login screen and start adding content</p>
				    <p>
				    <a href="http://<?php echo $admin?>" class="btn btn-primary btn-large">
				    I will take the Blue pill
				    </a>
				    <a href="http://<?php echo $domain?>" class="btn btn-danger btn-large">
				    I will take the Red pill
				    </a>
				    </p>
				</div>
			<?php break;?>
			<?php endswitch;?>
			</div>
          </div><!--/row-->
          
        </div><!--/span-->
      </div><!--/row-->



     <footer>
        
     </footer>

</div><!--/.fluid-container-->
<script src="lib/js/jquery-1.8.0.min.js"></script>
<script src="lib/js/jquery-ui-1.8.23.custom.min.js"></script>
<script src="lib/js/bootstrap-transition.js"></script>
<script src="lib/js/bootstrap-alert.js"></script>
<script src="lib/js/bootstrap-modal.js"></script>
<script src="lib/js/bootstrap-dropdown.js"></script>
<script src="lib/js/bootstrap-scrollspy.js"></script>
<script src="lib/js/bootstrap-tab.js"></script>
<script src="lib/js/bootstrap-tooltip.js"></script>
<script src="lib/js/bootstrap-popover.js"></script>
<script src="lib/js/bootstrap-button.js"></script>
<script src="lib/js/bootstrap-collapse.js"></script>
<script src="lib/js/bootstrap-carousel.js"></script>
<script src="lib/js/bootstrap-typeahead.js"></script>
<script src="lib/js/qool.js"></script>
</body>
</html>