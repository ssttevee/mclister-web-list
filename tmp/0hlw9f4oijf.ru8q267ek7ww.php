
		
	<?php foreach (($result?:array()) as $item): ?>
			<div class="col-lg-4">
				<h2><?php echo $item['name']; ?><div style="border-radius: 50%;width: 23px;height: 23px;background:<?php echo $item['is_online'] ? "green" : "red"; ?>;float:left;margin-top: 5px;margin-right: 5px;"></div></h2>
				<p class="col-xs-8" style="padding:0px;"><?php echo $item['address']; ?><?php if ($item['port']==25565): ?><?php else: ?>:<?php echo $item['port']; ?><?php endif; ?></p>
				<p class="col-xs-4" style="padding:0px;text-align:right;">
					<?php echo $calc_uptime($item['times_online'], $item['times_checked']); ?>% uptime
				</p>
				<p class="col-xs-12" style="padding:0px;text-align:right;">
					<?php echo $item['current_players']; ?>/<?php echo $item['max_players']; ?> players online
				</p>
				<div style="clear:both;"></div>
				<p style="float:right;"><a class="btn btn-xs btn-default" href="<?php echo $ALIASES['info']; ?>/<?php echo $item['identifier']; ?>-<?php echo $item['name']; ?>">More Info &raquo;</a></p>
			</div>
	<?php endforeach; ?>