<div class="page-header">
    <h1><?php echo $SESSION['name']; ?></h1>
</div>
<!-- Nav tabs -->
<ul class="nav nav-tabs" style="">
	<li<?php echo $tab == 'account' ? ' class="active"' : ''; ?>><a href="#account" data-toggle="tab" onclick="window.history.pushState(null, null, '<?php echo $ALIASES['members']; ?>/account')">Account</a></li>
	<li<?php echo $tab == 'servers' ? ' class="active"' : ''; ?>><a href="#servers" data-toggle="tab" onclick="window.history.pushState(null, null, '<?php echo $ALIASES['members']; ?>/servers')">My Servers</a></li>
	<li<?php echo $tab == 'mcpoints' ? ' class="active"' : ''; ?>><a href="#mcpoints" data-toggle="tab" onclick="window.history.pushState(null, null, '<?php echo $ALIASES['members']; ?>/mcpoints')">Get McPoints</a></li>
</ul>
<!-- Tab panes -->
<div class="tab-content" style="padding:15px;border-bottom-left-radius:4px;border-bottom-right-radius:4px;border: solid 1px #ddd;border-top-width:0px;">
	<div class="tab-pane fade<?php echo $tab == 'account' ? ' in active' : ''; ?>" id="account">
		<dl class="col-lg-6" style="padding:0">
			<dt><label for="name">Minecraft Username</label></dt>
			<dd>
				<div class="input-group">
					<input type="text" class="form-control" id="minecraft_username" value="<?php echo $get_current_user()["minecraft_username"]; ?>" disabled>
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" style="border-left-width:0">Change</button>
					</span>
				</div>
			</dd><br/>
			<dt><label for="name">Alias/Nickname</label></dt>
			<dd>
				<div class="input-group">
					<input type="text" class="form-control" id="alias" value="<?php echo $get_current_user()["alias"]; ?>" disabled>
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" style="border-left-width:0">Change</button>
					</span>
				</div>
			</dd><br/>
			<dt><label for="name">Email</label></dt>
			<dd>
				<div class="input-group">
					<input type="text" class="form-control" id="email" value="<?php echo $get_current_user()["email"]; ?>" disabled>
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" style="border-left-width:0">Change</button>
					</span>
				</div>
			</dd><br/>
			<dt><label for="name">Display Name</label></dt>
			<dd>
				<div class="input-group">
					<div class="btn-group" id="nametouse" style="display: table-cell;width:100%" value="<?php echo $get_current_user()["name_to_use"]; ?>">
						<button type="button" class="btn btn-default dropdown-toggle" style="background: #fff;border-bottom-right-radius:0;border-top-right-radius:0;width:100%;text-align: left;" data-toggle="dropdown" disabled><?php echo $get_current_user()["name_to_use"]; ?><span class="caret pull-right" style="margin-top: 8px"></span></button>
						<ul class="dropdown-menu pull-right" style="width:100%">
							<li><a href="javascript:;" onclick="$('#nametouse').attr('value','name');">Registered Name</a></li>
							<li><a href="javascript:;" onclick="$('#nametouse').attr('value','alias');">Alias/Nickname</a></li>
							<li><a href="javascript:;" onclick="$('#nametouse').attr('value','minecraft_username');">Minecraft Username</a></li>
						</ul>
					</div>
					<span class="input-group-btn">
						<button class="btn btn-default has-dropdown" type="button" style="border-left-width:0">Change</button>
					</span>
				</div>
			</dd>
		</dl>
		<div style="clear:both;"></div>
	</div>
	<div class="tab-pane fade<?php echo $tab == 'servers' ? ' in active' : ''; ?>" id="servers">
		<div class="list-group" style="margin-bottom:0">
	<?php foreach (($get_my_servers()?:array()) as $item): ?>
		<a href="<?php echo $ALIASES['info']; ?>/<?php echo $item['identifier']; ?>-<?php echo $item['name']; ?>" class="list-group-item">
			<div style="border-radius: 50%;width: 23px;height: 23px;background:<?php echo $item['is_online'] ? "green" : "red"; ?>;float:left;margin-top: 5px;margin-right: 5px;"></div>
			<h2 style="margin-top:0px;float:left;display: inline"><?php echo $item['name']; ?></h2>
			<p style="padding:0px;float:left;margin-top:10px;margin-left:10px;"><?php echo $item['address']; ?>:<?php echo $item['port']; ?></p>
			<div class="btn-group pull-right" role="toolbar">
				<button class="btn btn-default" onclick="alert('PROMOTED!!!   jk... not implemented yet...');return false;">Promote</button>
			</div>
			<div style="clear:both;"></div>
			<p class="col-xs-4" style="padding:0px;text-align:right;">
				<?php echo $calc_uptime($item['times_online'], $item['times_checked']); ?>% uptime
			</p>
			<p class="col-xs-12" style="padding:0px;text-align:right;">
				<?php echo $item['current_players']; ?>/<?php echo $item['max_players']; ?> players online
			</p>
			<div style="clear:both;"></div>
		</a>
	<?php endforeach; ?>
		
		</div>
		<div style="clear:both;"></div>
	</div>
	<div class="tab-pane fade<?php echo $tab == 'mcpoints' ? ' in active' : ''; ?>" id="mcpoints" style="position:relative;">
		<p>McPoints are the virtual currency used on McLister to do everything from getting an extra vote on a server to promoting your server and even going ad free!</p>
		<p>You currently have <?php echo $get_current_user()['mcpoints']; ?> McPoints.</p>
		<div class="col-lg-4">
			<h3>Bonus Bundles <small>All prices in USD</small></h3>
			<ul class="list-group">
				<a href="#buybundledpts" data-points="10" class="list-group-item">
					10 McPoints for $5.00
				</a>
				<a href="#buybundledpts" data-points="35" class="list-group-item">
					<span class="badge">+5 McPoints!</span>
					35 McPoints for $15.00
				</a>
				<a href="#buybundledpts" data-points="65" class="list-group-item">
					<span class="badge">+15 McPoints!</span>
					65 McPoints for $25.00
				</a>
				<a href="#buybundledpts" data-points="150" class="list-group-item">
					<span class="badge">+50 McPoints!</span>
					150 McPoints for $50.00
				</a>
			</ul>
		</div>
		<div class="col-lg-4">
			<h3>Custom Amount <small>50¢ each</small></h3>
			<form action='/paypal/checkout' METHOD='POST'>
				<dl>
					<dt><label for="amount">Amount of McPoints</label> <span class="label label-default">min. 4 ($2.00)</span></dt>
					<dd><input type="number" name="amount" id="amount" min="4" class="form-control" value="" placeholder="Number of McPoints" /></dd><br/>
					<input type='image' class="pull-right" id="paypal" src='https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=en_US' border='0' alt='Pay with PayPal'/>
			</form>
		</div>
		<div class="col-lg-4">
			<a href="https://www.paypal.com/webapps/mpp/paypal-popup" class="pull-right" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/bdg_payments_by_pp_2line.png" border="0" alt="Payments by PayPal"></a>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
<script>
$(window).load(function() {
	setTimeout(function() {
		var caret = $('#nametouse button span').detach(),text = '';
		if($('#nametouse button').text() == 'name') text = 'Registered Name';
		if($('#nametouse button').text() == 'alias') text = 'Alias/Nickname';
		if($('#nametouse button').text() == 'minecraft_username') text = 'Minecraft Username';
		$('#nametouse button').html(text);
		caret.appendTo('#nametouse button');
	}, 500);
});

$('#nametouse .dropdown-menu').find('a').click(function() {
	var caret = $('#nametouse button span').detach();
	$('#nametouse button').html($(this).text());
	caret.appendTo('#nametouse button');
});

$('a[href="#buybundledpts"]').click(function() {
	var form = $('<form action="<?php echo $ALIASES['paypal_checkout']; ?>" method="post">' +
		'<input type="hidden" name="bundle" value="true" />' +
		'<input type="hidden" name="amount" value="' + $(this).attr('data-points') + '" />' +
		'</form>');
	$('body').append(form);
	$(form).submit();
	return false;
});

$('#account').click(function(){$('#edit-server-form').keydown()});
$('#account').keyup(function(){$('#edit-server-form').keydown()});
$('dd .input-group-btn .btn').bind('click.change', function() {
	if($(this).hasClass('save-ready')) {
		var datr = {};
		if($(this).hasClass('has-dropdown')) {
			datr["change"] = 'name_to_use';
			datr["value"] = $('#nametouse').attr('value');
		} else {
			datr["change"] = $(this).parent().parent().find('input').attr('id');
			datr["value"] = $(this).parent().parent().find('input').val();
		}
		McListerAPI.toggleLoading(true);
		$(this).parent().parent().find('input,.btn-group button').attr('disabled','disabled');
		$(this).text('Change');
		$(this).removeClass('save-ready');
		McListerAPI.toggleLoading(true);
		$.post("<?php echo $ALIASES['update_account_info']; ?>", datr, function( msg ) {
			McListerAPI.toggleLoading(false);
			if(msg == 'ok') {
				$(this).parent().parent().find('input,.btn-group button').attr('disabled','disabled');
				$(this).text('Change');
				$(this).removeClass('save-ready');
			} else if(msg == 'not logged in') {
				window.location.href = '/login';
			} else {
				
			}
		});
	} else {
		$(this).parent().parent().find('input,.btn-group button').removeAttr('disabled');
		$(this).text('save');
		$(this).addClass('save-ready');
	}
});
$(window).bind("popstate", function(e) {
	$('a[href="#' + document.location.pathname.substr(4) + '"]').tab('show');
	console.log(document.location.pathname);
});
</script>