
	<h1 style="white-space:nowrap;text-overflow: ellipsis;overflow: hidden;">Edit <?php echo $result['0']["name"]; ?></h1>
	<a href="#new-owner" class="btn btn-info" onclick="return false;" >Transfer Ownership</a>
	<form method="post" id="edit-server-form">
		<dl>
		<dt><label for="name">Server Name</label> <span class="label label-warning hidden">*</span></dt>
		<dd><input type="text" name="name" id="name" class="form-control" value="<?php echo $result['0']['name']; ?>" placeholder="Minecraft Server" /></dd><br/>
		
		<dt><label for="server-address">Server Address</label></dt>
		<dd><input type="text" id="server-address" class="form-control" placeholder="example.com" value="<?php echo $result['0']['address']; ?>" disabled /></dd><br/>
		
		<dt><label for="server-port">Server Port</label></dt>
		<dd><input type="number" min="0" step="1" id="server-port" class="form-control" value="25565" value="<?php echo $result['0']['port']; ?>" placeholder="25565" disabled /></dd><br/>
		
		<dt><label for="website">Website</label> <span class="label label-warning hidden">*</span></dt>
		<dd><input type="text" name="website" id="website" class="form-control" value="<?php echo $result['0']['website']; ?>" placeholder="http://example.com/" /></dd><br/>
		
		<dt><label for="queryport">Query Port (aka GS4)</label> <span class="label label-warning hidden">*</span></dt>
		<dd><div class="input-group"><input type="number" name="queryport" id="queryport" min="0" step="1" class="form-control" value="<?php echo $result['0']['query_port']; ?>" placeholder="25565" /><span class="input-group-btn"><button class="btn btn-default" type="button" for="queryport" data-loading-text="..." disabled>Check</button></span></div></dd><br/>
		
		<dt><label for="showplugins">Show Plugins (if available)</label> <span class="badge" data-toggle="tooltip" data-placement="right" title="Server Query may list plugins">?</span> <span class="label label-warning hidden">*</span></dt>
		<dd><input type="checkbox" name="showplugins" id="showplugins" <?php echo $result['0']['show_plugins'] ? 'checked' : ''; ?> value="true"></dd><br/>
		
		<dt><label for="tags[]">Tags</label> <span class="label label-warning hidden">*</span></dt>
		<dd data-toggle="buttons">
			<?php foreach (($all_tags?:array()) as $tag): ?>
			<label class="btn btn-primary tag-btn<?php echo $has_tag($tag) ? ' active' : ''; ?>"><input type="checkbox" name="tags[]"<?php echo $has_tag($tag) ? ' checked' : ''; ?> id="tags" value="<?php echo $tag; ?>"><?php echo $tag; ?></label>
			<?php endforeach; ?>
		</dd><br/>
		
		<dt><label for="votifierport">Votifier Port</label> <span class="label label-warning hidden">*</span></dt>
		<dd><div class="input-group"><input type="number" name="votifierport" id="votifierport" min="0" step="1" class="form-control" value="<?php echo $result['0']['votifier_port']; ?>" placeholder="8192" /><span class="input-group-btn"><button class="btn btn-default" type="button" for="votifierport" data-loading-text="..." disabled>Check</button></span></div></dd><br/>
		
		<dt><label for="votifierkey">Votifier Public Key</label> <span class="label label-warning hidden">*</span></dt>
		<dd><textarea name="votifierkey" rows="4" cols="50" id="votifierkey" class="form-control" placeholder="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AM..." ><?php echo $result['0']['votifier_key']; ?></textarea></dd><br/>
		
		<dt><label for="description">Server Description/Info</label> <span class="badge" data-toggle="tooltip" data-placement="right" title="Allowed Tags: <b><a><i><u><center><h1><h2><h3><h4><h5><h6><em>">?</span> <span class="label label-warning hidden">*</span></dt>
		<dd><textarea name="description" rows="8" id="description" class="form-control" placeholder="<h1>Something about your server...</h1>" ><?php echo $result['0']['description']; ?></textarea></dd><br/>
		
		<a href="#close" onclick="$('#edit-modal').modal('hide');return false;" class="btn btn-default pull-right">Close</a>
		<button type="submit" class="btn btn-primary pull-right" id="submit-edits-btn" disabled>Save</button><br/>
			
	</form>
<script>

McListerAPI.SE = {
	PostData: {},
	OrigVals: {},
	PrevVals: {},
	Confirms: new Array(),
	Changes: new Array(),
	isShowPluginsChecked: false
};
window.addEventListener("load", function(e) {
	$('.badge').tooltip();
	$('.tag-btn').button();
	$('.btn').button()
	McListerAPI.SE.OrigVals = $('#edit-server-form').serializeObject();
	McListerAPI.SE.OrigVals.showplugins = $('#showplugins').is(":checked");
	if($('#queryport').val() == '') $('#showplugins').attr('disabled', true);
	else $('#showplugins').removeAttr('disabled');
	if($('#votifierport').val() == '') $('#votifierkey').attr('disabled', true);
	else $('#votifierkey').removeAttr('disabled');
	
	$('#edit-server-form').click(function(){$('#edit-server-form').keydown()});
	$('#edit-server-form').keyup(function(){$('#edit-server-form').keydown()});
	$('input[type=\'checkbox\']').change(function(){$('#edit-server-form').keydown()});
	$('#edit-server-form').keydown(function() {
		McListerAPI.SE.Changes = new Array();
		$.each($('#edit-server-form').serializeObject(), function( key, value ) {
			if($.isArray(value)) {
				if(JSON.stringify(value) != JSON.stringify(McListerAPI.SE.OrigVals[key])) {
					if(value != McListerAPI.SE.PrevVals[key] && $.inArray(key, McListerAPI.SE.Confirms) == -1) {
						McListerAPI.SE.Confirms.push(key);
					}
					McListerAPI.SE.Changes.push(key);
					McListerAPI.SE.PrevVals[key] = value;
					$('label[for=\'' + key + '\']').parent().children().last().removeClass('hidden');
				} else {
					delete McListerAPI.SE.PrevVals[key];
					McListerAPI.SE.Confirms = $.grep(McListerAPI.SE.Confirms, function(item) {return item != key;});
					$('label[for=\'' + key + '\']').parent().children().last().addClass('hidden');
				}
			} else if(key == 'showplugins') {
				McListerAPI.SE.isShowPluginsChecked = true;
				if($('#'+key).is(":checked") != McListerAPI.SE.OrigVals[key]) {
					if(value != McListerAPI.SE.PrevVals[key] && $.inArray(key, McListerAPI.SE.Confirms) == -1) {
						McListerAPI.SE.Confirms.push(key);
					}
					McListerAPI.SE.Changes.push(key);
					McListerAPI.SE.PrevVals[key] = $('#'+key).is(":checked");
					$('label[for=\'' + key + '\']').parent().children().last().removeClass('hidden');
				} else {
					delete McListerAPI.SE.PrevVals[key];
					McListerAPI.SE.Confirms = $.grep(McListerAPI.SE.Confirms, function(item) {return item != key;});
					$('label[for=\'' + key + '\']').parent().children().last().addClass('hidden');
				}
			} else {
				if(value != McListerAPI.SE.OrigVals[key]) {
					if(value != McListerAPI.SE.PrevVals[key]) {
						if($.inArray(key, McListerAPI.SE.Confirms) == -1) {
							if(key != 'queryport' && key != 'votifierport') {
								McListerAPI.SE.Confirms.push(key);
							} else {
								if(value == '') {
									$('button[for=\''+key+'\']').attr('disabled', true);
									$('button[for=\''+key+'\']').text('Check');
									$('button[for=\''+key+'\']').addClass('btn-default');
									$('button[for=\''+key+'\']').removeClass('btn-danger');
									$('button[for=\''+key+'\']').removeClass('btn-success');
									McListerAPI.SE.Confirms.push(key);
								} else {
									$('button[for=\''+key+'\']').removeAttr('disabled');
									$('button[for=\''+key+'\']').text('Check');
									$('button[for=\''+key+'\']').addClass('btn-default');
									$('button[for=\''+key+'\']').removeClass('btn-danger');
									$('button[for=\''+key+'\']').removeClass('btn-success');
									McListerAPI.SE.Confirms = $.grep(McListerAPI.SE.Confirms, function(item) {return item != key;});
								}
							}
						}
					}
					McListerAPI.SE.Changes.push(key);
					McListerAPI.SE.PrevVals[key] = value;
					$('label[for=\'' + key + '\']').parent().children().last().removeClass('hidden');
				} else {
					delete McListerAPI.SE.PrevVals[key];
					McListerAPI.SE.Confirms = $.grep(McListerAPI.SE.Confirms, function(item) {return item != key;});
					$('label[for=\'' + key + '\']').parent().children().last().addClass('hidden');
					if(key == 'queryport' || key == 'votifierport') {
						$('button[for=\''+key+'\']').attr('disabled', true);
						$('button[for=\''+key+'\']').text('Check');
						$('button[for=\''+key+'\']').addClass('btn-default');
						$('button[for=\''+key+'\']').removeClass('btn-danger');
						$('button[for=\''+key+'\']').removeClass('btn-success');
					}
				}
			}
		});
		if(!McListerAPI.SE.isShowPluginsChecked) {
			if($('#showplugins').is(":checked") != McListerAPI.SE.OrigVals.showplugins) {
				if($('#showplugins').is(":checked") != McListerAPI.SE.PrevVals['showplugins'] && $.inArray('showplugins', McListerAPI.SE.Confirms) == -1) {
					McListerAPI.SE.Confirms.push('showplugins');
				}
				McListerAPI.SE.Changes.push('showplugins');
				McListerAPI.SE.PrevVals.showplugins = $('#showplugins').is(":checked");
				$('label[for=\'showplugins\']').parent().children().last().removeClass('hidden');
			} else {
				delete McListerAPI.SE.PrevVals['showplugins'];
				McListerAPI.SE.Confirms = $.grep(McListerAPI.SE.Confirms, function(item) {return item != 'showplugins';});
				$('label[for=\'showplugins\']').parent().children().last().addClass('hidden');
			}
		}
		McListerAPI.SE.isShowPluginsChecked = false;
		if($.isEmptyObject(McListerAPI.SE.Changes)) {
			$('#submit-edits-btn').attr('disabled', true);
		} else {
			$('#submit-edits-btn').removeAttr('disabled');
		}
		$.each(McListerAPI.SE.Changes, function(index, field) {
			if($.inArray(field, McListerAPI.SE.Confirms) == -1) {
				$('#submit-edits-btn').attr('disabled', true);
			}
		});
		if($('#votifierport').val() == '' || $('#votifierport').val() == '0') $('#votifierkey').attr('disabled', true);
		else $('#votifierkey').removeAttr('disabled');
		if($('#queryport').val() == '' || $('#queryport').val() == '0') $('#showplugins').attr('disabled', true);
		else $('#showplugins').removeAttr('disabled');
	});
	
	$('button[for=\'queryport\'').click(function() {
		var btn = $(this);
		btn.button('loading');
		if($.isNumeric($('#queryport').val())) {
			$.ajax({
				type: "POST",
				url: "<?php echo $ALIASES['minecraft_query_ping']; ?>",
				data: { host: '<?php echo $result['0']['address']; ?>', port: $('#queryport').val().trim() }
			}).done(function( msg ) {
				btn.button('reset');
				if(msg) {
					btn.text('Ok');
					btn.removeClass('btn-default');
					btn.removeClass('btn-danger');
					btn.addClass('btn-success');
					btn.attr('disabled', true);
					McListerAPI.SE.Confirms.push('queryport');
					$('#edit-server-form').keydown();
				} else {
					btn.text('Try again');
					btn.removeClass('btn-success');
					btn.removeClass('btn-default');
					btn.addClass('btn-danger');
				}
			});
		} else {
			alert('Ports can only have number');
		}
	});
	$('button[for=\'votifierport\'').click(function() {
		var btn = $(this);
		btn.button('loading');
		if($.isNumeric($('#votifierport').val())) {
			$.ajax({
				type: "POST",
				url: "<?php echo $ALIASES['low_level_ping']; ?>",
				data: { host: '<?php echo $result['0']['address']; ?>', port: $('#votifierport').val().trim() }
			}).done(function( msg ) {
				btn.button('reset');
				if(msg) {
					btn.text('Ok');
					btn.removeClass('btn-default');
					btn.removeClass('btn-danger');
					btn.addClass('btn-success');
					btn.attr('disabled', true);
					McListerAPI.SE.Confirms.push('votifierport');
					$('#edit-server-form').keydown();
				} else {
					btn.text('Try again');
					btn.removeClass('btn-success');
					btn.removeClass('btn-default');
					btn.addClass('btn-danger');
				}
			});
		} else {
			alert('Ports can only have number');
		}
	});
	
	$('#edit-server-form').submit(function() {
		McListerAPI.SE.PostData = McListerAPI.SE.PrevVals;
		McListerAPI.SE.PostData.server_id = '<?php echo $result['0']['identifier']; ?>';
		var btn = $('#submit-edits-btn');
		btn.button('loading');
		$.ajax({
			type: "POST",
			url: "<?php echo $ALIASES['edit']; ?>",
			data: McListerAPI.SE.PostData
		}).done(function( msg ) {
			btn.button('reset');
			if(msg == 'not logged in') {
				McListerAPI.showNotif('Wait a minute...', 'You\' not even logged in, silly!', 'warning');
			} else if(msg == 'no server found') {
				McListerAPI.showNotif('What\'s this?', 'Someone has been tampering with the javascript! D:', 'danger');
			} else if(msg == 'bad owner') {
				McListerAPI.showNotif('Go Away!', 'You\'re not the owner of this server! Come back when you\'re the owner...', 'warning');
			} else {
				var json = $.parseJSON( msg );
				if( json.response === 'ok' ) {
					window.location.reload();
				} else {
					if(json.info == 'offline') McListerAPI.showNotif('Thats odd...', 'Why is your server offline?', 'warning');
					if(json.info == 'no data') alert('Ehh...? We didn\'t receive any data from your server... But it looks just fine...');
				}
			}
		});
		return false;
	});
}, true);
var pingServer = function(addr, loca, callback) {
	if($.isNumeric(loca)) {
		$.ajax({
			type: "POST",
			url: "<?php echo $ALIASES['low_level_ping']; ?>",
			data: { host: addr.trim().toLowerCase(), port: loca }
		}).done(function( msg ) {
			callback(msg);
		});
	} else {
		callback(false);
	}
}
McListerAPI.sendAlert = function(where, title, body, type) {
	type = typeof type !== 'undefined' ? type : 'warning';
	where.prepend('<div class="alert alert-'+type+' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>'+title+'</strong> '+body+'</div>');

}

</script>