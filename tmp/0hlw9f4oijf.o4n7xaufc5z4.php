
	<div class="navbar navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar navbar-inverse"></span>
					<span class="icon-bar navbar-inverse"></span>
					<span class="icon-bar navbar-inverse"></span>
				</button>
				<a class="navbar-brand" href="<?php echo $ALIASES['home']; ?>">McLister</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li<?php echo $PATH==$ALIASES['add_server']?' class="active"':''; ?>><a href="<?php echo $ALIASES['add_server']; ?>">Add Server</a></li>
					<li<?php echo $PATH==$ALIASES['random']?' class="active"':''; ?>><a href="<?php echo $ALIASES['random']; ?>">Random</a></li>
				</ul>
				<?php if ($is_logged_in==true): ?>
				
				<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $get_current_user()['name']; ?><b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php echo $ALIASES['members']; ?>/account">My Account</a></li>
						<li><a href="<?php echo $ALIASES['members']; ?>/servers">My Servers</a></li>
						<li><a href="<?php echo $ALIASES['members']; ?>/mcpoints">McPoints: <?php echo $get_current_user()['mcpoints']; ?></a></li>
						<li class="divider"></li>
						<li><a href="<?php echo $ALIASES['logout']; ?>">Log out</a></li>
					</ul>
				</li>
				</ul>
				
				<?php else: ?>
				<form class="navbar-form navbar-right" method="get" action="https://clef.io/iframes/qr">
					<input type="hidden" name="app_id" value="<?php echo $clef_public_key; ?>" />
					<input type="hidden" name="redirect_url" value="http://<?php echo $JAR['domain']; ?><?php echo $ALIASES['clef']; ?>?next_page=<?php echo $PATH; ?>" />
					<button type="submit" class="clef btn btn-success">Log in with Clef</button>
				</form>
				
				<?php endif; ?>
			</div><!--/.navbar-collapse -->
		</div>
	</div>