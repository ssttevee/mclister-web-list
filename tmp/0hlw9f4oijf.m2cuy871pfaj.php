<div class="page-header">
    <h1><?php echo $result['0']["name"]; ?><div style="border-radius: 50%;width: 28px;height: 28px;background:<?php echo $result['0']['is_online'] ? "green" : "red"; ?>;float:left;margin-top: 5px;margin-right: 5px;"></div> <span class="label label-default" style="font-size:12px"><?php echo $result['0']["server_version"]; ?></span></h1>
</div>
<br/>
<!-- Nav tabs -->
<ul class="nav nav-tabs" style="">
	<li class="active"><a href="#info" data-toggle="tab" id="info-tab">General</a></li>
	<li><a href="#stats" data-toggle="tab">Statistics</a></li>
	<li><a href="#embed" data-toggle="tab">Embed</a></li>
	<li><a href="#vote" data-toggle="tab">Vote</a></li>
	<?php if ($result['0']['owner']==$get_current_user()['id']): ?>
		<li><a href="#delete" data-toggle="tab">Delete</a></li>
		<li><a href="#edit" data-toggle="modal" data-target="#edit-modal">Edit</a></li>
	<?php endif; ?>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div class="tab-pane fade in active" id="info">
	<div class="detail-list-group no-bot-margin chop-top">
		<div class="detail-list-set"><div class="detail-list-title">Owner</div><div class="detail-list-detail"><?php echo $owner[$owner["name_to_use"]]; ?></div></div>
		<div class="detail-list-set"><div class="detail-list-title">Address</div><div class="detail-list-detail"><?php echo $result['0']["address"]; ?><?php if ($result['0']['port']==25565): ?><?php else: ?>:<?php echo $result['0']['port']; ?><?php endif; ?></div></div>
		<div class="detail-list-set"><div class="detail-list-title">MoTD</div><div class="detail-list-detail"><?php echo $result['0']["motd"]; ?></div></div>
		<?php if ($result['0']['is_online']): ?><?php else: ?><div class="detail-list-set"><div class="detail-list-title">Last Seen</div><div class="detail-list-detail"><?php echo $get_pretty_time($result['0']['last_online']); ?></div></div><?php endif; ?>
		<div class="detail-list-set"><div class="detail-list-title">Website</div><div class="detail-list-detail"><?php echo $result['0']["website"]; ?></div></div>
		<div class="detail-list-set"><div class="detail-list-title">Monthly Votes</div><div class="detail-list-detail"><?php echo $result['0']["votes"]; ?></div></div>
	</div>
	<?php if (trim($result['0']['description']) == ''): ?><?php else: ?><div class="detail-list-desc"><?php echo $result['0']["description"]; ?></div><?php endif; ?>
  </div>
  <div class="tab-pane fade" id="stats">
	  
	<div class="detail-list-group chop-top">
		<?php if ($result['0']['is_online']): ?>
			
		<div class="detail-list-set"><div class="detail-list-title">Player Slots</div><div class="detail-list-detail"><?php echo $result['0']["current_players"]; ?>/<?php echo $result['0']["max_players"]; ?></div></div>
		<?php if (!empty($players_list)): ?><div class="detail-list-set"><div class="detail-list-title">Player List</div><div class="detail-list-detail">
			<?php foreach (($players_list?:array()) as $player): ?>
			<span class="label label-default"><?php echo $player; ?></span>
			<?php endforeach; ?>
		</div></div><?php endif; ?>
			
		<?php endif; ?>
		<div class="detail-list-set"><div class="detail-list-title">Uptime</div><div class="detail-list-detail"><?php echo $calc_uptime($result['0']['times_online'], $result['0']['times_checked']); ?>%</div></div>
		<?php if ($result['0']['is_online']): ?>
			
		<div class="detail-list-set"><div class="detail-list-title">Latency</div><div class="detail-list-detail"><?php echo $result['0']["latency"]; ?>ms</div></div>
			
		<?php endif; ?>
		<div class="detail-list-set"><div class="detail-list-title">Last Checked</div><div class="detail-list-detail"><?php echo $get_pretty_time($result['0']['last_checked']); ?></div></div>
		<?php if ($result['0']['is_online']): ?>
			
		<div class="detail-list-set"><div class="detail-list-title">Server Mod</div><div class="detail-list-detail"><?php echo $result['0']["server_wrapper"]; ?></div></div>
		<?php if (!empty($plugins_list)): ?><?php if ($result['0']['show_plugins']): ?><div class="detail-list-set"><div class="detail-list-title">Plugins</div><div class="detail-list-detail">
			<?php foreach (($plugins_list?:array()) as $plugin): ?>
			<span class="label label-default"><?php echo str_replace('&quot;','',$plugin); ?></span>
			<?php endforeach; ?>
		</div></div><?php endif; ?><?php endif; ?>
			
		<?php endif; ?>
		<div class="detail-list-set"><div class="detail-list-title">Tags</div><div class="detail-list-detail">
			<?php foreach (($tags_list?:array()) as $tag): ?>
			<span class="label label-default"><?php echo $tag; ?></span>
			<?php endforeach; ?>
		</div></div>
	</div>
  </div>
	<div class="tab-pane fade" id="embed">
		<div class="panel panel-default" style="margin-top:-1px;border-top-left-radius:0;border-top-right-radius:0">
			<div class="panel-body" style="padding:15px 0">
				<div class="col-lg-6">
					<p><div class="btn-group-justified">
						<div class="btn-group" style="display:table-row">
							<button type="button" class="btn btn-default" onclick="changeEmbedCode('small');" style="width: 33%">Small</button>
							<button type="button" class="btn btn-default" onclick="changeEmbedCode('medium');" style="width: 34%">Medium</button>
							<button type="button" class="btn btn-default" onclick="changeEmbedCode('big');" style="width: 33%">Big</button>
						</div>
					</div></p>
					<p><img src="http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/big.png" id="embed-demo" style="max-width:100%;max-height:100%" /></p>
					<p><div class="input-group">
						<span class="input-group-addon">BBCode</span>
						<input type="text" id="embed-bbcode" class="form-control" readonly value="[url=http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>][img]http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/big.png[/img][/url]">
					</div></p>
					<p><div class="input-group">
						<span class="input-group-addon">HTML</span>
						<input type="text" id="embed-html" class="form-control" readonly value='<a href="http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>"><img src="http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/big.png" alt="<?php echo $result[0]["name"]; ?>"></a>'>
					</div></p>
				</div>
			</div>
		</div>
	</div>
  <div class="tab-pane fade" id="vote"><?php echo $this->render('templates/vote.html',$this->mime,get_defined_vars()); ?></div>
  <?php if ($result['0']['owner']==$get_current_user()['id']): ?>
	<div class="tab-pane" id="delete">
		<div class="detail-list-desc">
			<div class="col-lg-6">
				<p>Do you really want to delete <?php echo $result['0']["name"]; ?> from McLister?</p>
				<a href="#delete" class="btn btn-danger pull-right" id="delete-btn" >Yes, delete</a>
				<a href="#info" class="btn btn-primary" onclick="$('#info-tab').click();return false;">Nope</a>
			</div>
			<div style="clear:both"></div>
		</div>
	</div>
  <?php endif; ?>
</div>
<?php if ($result['0']['owner']==$get_current_user()['id']): ?>
<!-- Edit Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="edit-modal" aria-labelledby="editModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
    	<?php echo $this->render('templates/edit_server.html',$this->mime,get_defined_vars()); ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
while($('.nav.nav-tabs').height() > 42) {
	McListerAPI.moveTabToMore()
}
$('#delete-btn').click(function() {
	var btn = $('#submit-edits-btn');
	btn.button('loading');
	$.ajax({
		type: "POST",
		url: "<?php echo $ALIASES['delete']; ?>",
		data: {server_id: <?php echo $result['0']['identifier']; ?>}
	}).done(function( msg ) {
		btn.button('reset');
		if(msg == 'not logged in') {
			McListerAPI.showNotif('Wait a minute...', 'You\'re not even logged in, silly!', 'warning');
		} else if(msg == 'no server found') {
			McListerAPI.showNotif('What\'s this?', 'Someone has been tampering with the javascript! D:', 'danger');
		} else if(msg == 'bad owner') {
			McListerAPI.showNotif('Go Away!', 'You\'re not the owner of this server! Come back when you\'re the owner...', 'warning');
		} else {
			var json = $.parseJSON( msg );
			if( json.response === 'ok' ) {
				window.location.reload();
			} else {
				McListerAPI.showNotif('Uh oh...', 'An unknown error has occurred...?  This may be a bug, please report it soon.', 'danger');
			}
		}
	});
	return false;
});
$('#embed-demo').load(function(){
	McListerAPI.toggleLoading(false);
});
$("#embed-bbcode").mouseover(function(){ this.select(); });
$("#embed-html").mouseover(function(){ this.select(); });
$("#embed-bbcode").click(function(){ this.select(); });
$("#embed-html").click(function(){ this.select(); });
var changeEmbedCode = function(size) {
	McListerAPI.toggleLoading(true);
	$('#embed-demo').attr('src', 'http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/'+size+'.png');
	$('#embed-bbcode').val('[url=http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>][img]http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/'+size+'.png[/img][/url]');
	$('#embed-html').val('<a href="http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>"><img src="http://<?php echo $JAR['domain']; ?><?php echo $PATH; ?>/'+size+'.png" alt="<?php echo $result[0]["name"]; ?>"></a>');
};
</script>
