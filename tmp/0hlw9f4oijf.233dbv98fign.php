<div class="panel panel-default" style="margin-top:-1px;border-top-left-radius:0;border-top-right-radius:0">
	<div class="panel-body" style="padding:15px 0">
		<script type="text/javascript">
			var RecaptchaOptions = {
				theme : 'custom',
				custom_theme_widget: 'recaptcha_widget'
			};
		</script>
		<form class="col-lg-4">
			<dl style="margin-top:0">
				<?php if ($result['0']['votifier_port'] != ''): ?>
					<dt><label for="minecraft-username">Minecraft Username</label></dt>
					<?php if ($get_current_user()['minecraft_username'] == ''): ?>
						
							<dd><input type="text" name="mc_name" id="minecraft-username" class="form-control" /></dd></dd><br/>
						
						<?php else: ?>
							<dd><input type="text" name="mc_name" id="minecraft-username" value="<?php echo $get_current_user()['minecraft_username']; ?>" class="form-control" disabled/></dd></dd><br/>
						
					<?php endif; ?>
				<?php endif; ?>
				<dt><label for="recaptcha_response_field">Captcha</label>
					<div id="recaptcha_widget" style="display:none;">
						
						<div id="recaptcha_audio_box">
							<div id="recaptcha_image"></div>
						</div>
						<ul id="recaptcha_links">
							<li class="glyphicon glyphicon-refresh" onclick="Recaptcha.reload()"></li>
							<li class="glyphicon glyphicon-volume-up recaptcha_only_if_image" onclick="Recaptcha.switch_type('audio')"></li>
							<li class="glyphicon glyphicon-font recaptcha_only_if_audio" onclick="Recaptcha.switch_type('image')"></li>
							<li class="glyphicon glyphicon-question-sign" onclick="Recaptcha.showhelp()"></li>
						</ul>
						<div style="clear:both;"></div>
						<div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>
						
					</div></dt>
				<dd><input type="text" name="recaptcha_response_field" id="recaptcha_response_field" class="form-control" /></dd></dd><br/>
			</dl>
			<input type="hidden" name="server_id" value="<?php echo $result['0']['identifier']; ?>" class="form-control" />
			<button type="submit" class="btn btn-primary pull-right" id="vote-btn">Vote!</button>
		</form>
		
		<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=<?php echo $recaptcha_public_key; ?>"></script>
	</div>
</div>
<script>
$('#vote-btn').parent().submit(function() {
	McListerAPI.toggleLoading(true);
	$.ajax({
		type: "POST",
		url: "<?php echo $ALIASES['ajax_vote']; ?>",
		data: $('#vote-btn').parent().serializeObject()
	}).done(function( msg ) {
		McListerAPI.toggleLoading(false);
		if(msg == 'no server found') {
			McListerAPI.showNotif('What\'s this?', 'Someone has been tampering with the javascript! D:', 'danger');
		} else if(msg == 'bad captcha') {
			Recaptcha.reload();
			McListerAPI.showNotif('Wrong Captcha', 'Enter the captcha again', 'warning');
		} else if(msg.substring(0,4) == 'wait') {
			Recaptcha.reload();
			McListerAPI.showNotif('Can\'t vote yet', 'Come back in '+msg.substring(4).toHHMMSS(), 'warning');
		} else if( msg == 'ok' ) {
			McListerAPI.showNotif('Vote Successful!', '', 'success');
			window.location.reload();
		} else {
			Recaptcha.reload();
			console.log(msg);
		}
	});
	return false;
});
$('a[href="#vote"]').click(function() {
	var timer = setInterval(function () {
		if($('#recaptcha_widget').parent().width() != 0 && $('#recaptcha_widget').parent().width() < 325) {
			$('#recaptcha_challenge_image').removeAttr('height').removeAttr('width');
			$('#recaptcha_challenge_image').css('width',$('#recaptcha_widget').parent().width() - 26);
			$('#recaptcha_image').css('width','auto').css('height','auto');
			$('#recaptcha_audio_box').css('width',$('#recaptcha_widget').parent().width() - 24);
			clearInterval(timer);
		}
	}, 250);
});
</script>