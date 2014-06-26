<?php

$f3 = require('lib/base.php');
$f3->set('AUTOLOAD','al/');
$f3->set('clef_public_key', '45e95f6cb130770e745671c326e156c3');
$f3->set('clef_private_key', '3e02ef2261f213d08a3ec9e9e4909e04');
$f3->set('recaptcha_public_key', '6LfHxt0SAAAAAAtLhUvB2RJokml8JmbtEQAi24Gh');
$f3->set('recaptcha_private_key', '6LfHxt0SAAAAAPWG9gPsqNMdlIrnwYdAu0T_cfKa');
$f3->set('paypal_is_sandbox', false);
$f3->set('paypal_facilitator_email', ( $f3->get('paypal_is_sandbox') ? 'stevieiswonderful-facilitator_api1.gmail.com' : 'stevieiswonderful_api1.gmail.com'));
$f3->set('paypal_facilitator_passwd', ( $f3->get('paypal_is_sandbox') ? '1396145412' : '758E9DLDBSNPJ26L'));
$f3->set('paypal_facilitator_signature', ( $f3->get('paypal_is_sandbox') ? 'AFcWxV21C7fd0v3bYYYRCpSSRl31ALHFJT7uFMXV4-Fux48GbIQ1jIZn' : 'AhIhM1ZTEs-e2k6LL8hb4FLnTyQ-AUaXdpRT291VNkH0KpzGU8EKMTRr'));
$f3->set('paypal_base_url', 'https://www.' . ( $f3->get('paypal_is_sandbox') ? 'sandbox.' : '') . 'paypal.com');
$f3->set('all_tags', array('survival','pvp','pve','mcmmo','factions','towny','minigames','creative','economy','modded'));
$f3->set('get_pretty_time', function($unparsed_time) { $pt = new PrettyTime(strtotime(substr($unparsed_time,0,strpos($unparsed_time, '.'))));return $pt->toString();});
$f3->set('calc_uptime', function($online, $checked) { return floor($online/$checked*10000)/100; });
$f3->set('get_current_user', function() use ($f3) {
	if($f3->get('is_logged_in')) {
		if(isset($GLOBALS['current_user'])) {
			return $GLOBALS['current_user'];
		} else {
			$db = getDB();
			$result = $db->exec('SELECT * FROM users WHERE clef_id=' . $f3->get('SESSION.user_id'));
			return $GLOBALS['current_user'] = $result[0];
		}
	} else {
		return null;
	}
});
$f3->set('get_my_servers', function() use ($f3) {
	if($f3->get('is_logged_in')) {
		$db = getDB();
		$gcu = $f3->get('get_current_user');
		$result = $db->exec('SELECT * FROM servers WHERE owner=' . $gcu()["id"] . ' ORDER BY identifier ASC');
		return $result;
	} else {
		return null;
	}
});
$f3->set('has_voted', function() use ($f3) {
	$db = getDB();
	$result = $db->exec('SELECT * FROM users WHERE clef_id=' . $f3->get('SESSION.user_id') . '');
	return $result[0];
});
$f3->set('get_user_by_id', function($uid) use ($f3) { 
	$db = getDB();
	$result = $db->exec('SELECT * FROM users WHERE id=' . $uid . '');
	if(!empty($result)) return $result[0];
});

$f3->clef = new ClefMgmt($f3, getDB());
$f3->set('is_logged_in', ($f3->clef->isLoggedIn() ? $f3->clef->validateSession() : false));

$f3->route('GET @home: /',
	function($f3) {
		$db=getDB();
		$f3->set('result',$db->exec('SELECT * FROM servers'));
		
		$f3->set('main_content','templates/home_page.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	}
);

$f3->route('GET @random: /random', function($f3) {
	$db = getDB();
	$result = $db->exec('SELECT identifier FROM servers ORDER BY RANDOM() LIMIT 1');
	rerouteWithName($f3, '@server_info', $result[0]["identifier"], $db);
});

$f3->route('GET @info: /info', function($f3,$params) { $f3->reroute('@home'); });
$f3->route('GET /info/@server_id-', function($f3) { rerouteWithName($f3, '@server_info', $f3->get('PARAMS.server_id')); });
$f3->route('GET /info/@server_id', function($f3) { rerouteWithName($f3, '@server_info', $f3->get('PARAMS.server_id')); });

$f3->route('GET @server_info: /info/@server_id-@encoded_name',
	function($f3, $params) {
		$db=getDB();
		$f3->set('result',$db->exec('SELECT * FROM servers WHERE identifier=\'' . $params['server_id'] . '\''));
		if(empty($f3->get('result'))) {
			$f3->error(404);
		} else if($params['encoded_name'] != preg_replace("![^a-z0-9]+!i", "", $f3->get('result')[0]['name'])) {
			rerouteWithName($f3, '@server_info', $params['server_id']);
		}
		
		$get_user_by_id = $f3->get('get_user_by_id');
		$f3->set('owner', $get_user_by_id($f3->get('result')[0]["owner"]));
		$f3->set('players_list', ($f3->get('result')[0]["players_list"] == '' ?  array(): explode(',',trim($f3->get('result')[0]["players_list"],'{}'))));
		$f3->set('plugins_list', ($f3->get('result')[0]["plugins"] == '' ?  array(): explode(',',trim($f3->get('result')[0]["plugins"],'{}'))));
		$f3->set('tags_list', explode(',',trim($f3->get('result')[0]["tags"],'{}')));
		$f3->set('has_tag', function($tag) use ($f3) { return (array_search($tag, $f3->get('tags_list')) === false ? false : true );});
		$f3->set('edit_url', $f3->get('ALIASES.edit') . "/" . $params['server_id'] . "-" . $params['encoded_name']);
		$f3->set('main_content','templates/server_info.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	}
);

$f3->route('GET @server_banner: /info/@server_id-@encoded_name/@bg/@size.png', function($f3, $params) {
	$db = getDB();
	$f3->set('result',$db->exec('SELECT name FROM servers WHERE identifier=?', $params['server_id']));
	if(empty($f3->get('result'))) {
		$f3->error(404);
	} else if($params['encoded_name'] != preg_replace("![^a-z0-9]+!i", "", $f3->get('result')[0]['name'])) {
		$f3->reroute('@server_banner(@server_id=' . $params['server_id'] . ',@encoded_name=' . preg_replace("![^a-z0-9]+!i", "", $f3->get('result')[0]['name']) . ',@bg' . $params['bg'] . ',@size' . $params['size'] . ')');
	}
	header('Content-type: image/png');
	$banner = new ServerBanner($db, (is_numeric($params["bg"]) && in_array($params["bg"], array("1","2","0")) ? $params["bg"] : null));
	$banner->make(intval($params['server_id']));
	$banner->resize($params['size']);
	echo $banner->getPng();
	$banner->clean();
});

$f3->route('GET @server_banner_nobg: /info/@server_id-@encoded_name/@size.png', function($f3, $params) {
	$db = getDB();
	$f3->set('result',$db->exec('SELECT name FROM servers WHERE identifier=?', $params['server_id']));
	if(empty($f3->get('result'))) {
		$f3->error(404);
	} else if($params['encoded_name'] != preg_replace("![^a-z0-9]+!i", "", $f3->get('result')[0]['name'])) {
		$f3->reroute('@server_banner_nobg(@server_id=' . $params['server_id'] . ',@encoded_name=' . preg_replace("![^a-z0-9]+!i", "", $f3->get('result')[0]['name']) . ',@size' . $params['size'] . ')');
	}
	header('Content-type: image/png');
	$banner = new ServerBanner($db);
	$banner->make(intval($params['server_id']));
	$banner->resize($params['size']);
	echo $banner->getPng();
	$banner->clean();
});

$f3->route('GET @add_server: /new', function($f3) {
	if($f3->get('is_logged_in')) {
		$vs = CodeGenerator::getServerVerifier();
		$f3->set('verify_owner_key', $vs->generateKey());
		$f3->set('verify_owner_hash', $vs->getAnswer($f3->hash('foobar')));
		$f3->set('main_content','templates/add_server.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	} else {
		$f3->set('main_content','templates/login_page.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	}
});

$f3->route('POST /new', function($f3) {
	if(!$f3->get('is_logged_in')) die('not logged in');
	
	$db = getDB();
	if(count($db->exec('SELECT name FROM servers WHERE address=:addr AND port=:pt', array(':addr'=>strtolower(trim($f3->get('POST.address'))), ':pt'=>trim($f3->get('POST.port'))))) == 0) {
		$insert_key = array();
		$insert_val = array();
		
		array_push($insert_key, 'name');
		array_push($insert_val, $db->quote($f3->clean($f3->get('POST.name'))));
		array_push($insert_key, 'address');
		array_push($insert_val, $db->quote(strtolower(trim($f3->clean($f3->get('POST.address'))))));
		array_push($insert_key, 'port');
		array_push($insert_val, $db->quote(trim($f3->clean($f3->get('POST.port'))), \PDO::PARAM_INT));
		array_push($insert_key, 'website');
		array_push($insert_val, $db->quote(fixurl(strtolower(trim($f3->clean($f3->get('POST.website')))))));
		array_push($insert_key, 'query_port');
		array_push($insert_val, $db->quote(trim($f3->clean($f3->get('POST.queryport'))), \PDO::PARAM_INT));
		if(trim($f3->clean($f3->get('POST.queryport'))) != '') {
			array_push($insert_key, 'show_plugins');
			array_push($insert_val, $db->quote(($f3->get('POST.showplugins') ? 'true' : 'false'), \PDO::PARAM_BOOL));
		}
		array_push($insert_key, 'tags');
		array_push($insert_val, $db->quote('{' . implode(',', $f3->get('POST.tags')) . '}'));
		if($f3->get('POST.votifierport')) {
			array_push($insert_key, 'votifier_port');
			array_push($insert_val, $db->quote(trim($f3->clean($f3->get('POST.votifierport'))), \PDO::PARAM_INT));
			array_push($insert_key, 'votifier_key');
			array_push($insert_val, $db->quote(trim($f3->clean($f3->get('POST.votifierkey')))));
		}
		array_push($insert_key, 'description');
		array_push($insert_val, $db->quote($f3->encode($f3->clean($f3->get('POST.description'), 'b,a,i,u,center,h1,h2,h3,h4,h5,h6,em'))));
		array_push($insert_key, 'owner');$currentuser = $f3->get('get_current_user');
		array_push($insert_val, $db->quote($currentuser()["id"], \PDO::PARAM_INT));
		
		$Timer = MicroTime( true );
		if($f3->get('POST.queryport') == '') {
			$Info = false;
			$Exception = null;
			
			minecraftPing($f3->get('POST.address'), $f3->get('POST.port'), $Info, $Exception);
			
			$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
			
			if( !isset( $Exception ) ) {
				if( $Info !== false ) {
					foreach( $Info as $InfoKey => $InfoValue ) {
						if($InfoKey === 'favicon') {
							array_push($insert_key, 'favicon');
							array_push($insert_val, $db->quote($InfoValue));
						} else if($InfoKey === 'version') {
							array_push($insert_key, 'server_version');
							array_push($$insert_val, $db->quote(preg_replace("/[^0-9,.]/", "", $InfoValue["name"])));
							if(strlen($InfoValue["name"])>10) {
								array_push($insert_key, 'server_wrapper');
								array_push($insert_val, $db->quote(trim(preg_replace('/[0-9.,]/', '', $InfoValue["name"]))));
							}
						} else if($InfoKey === 'description') {
							array_push($insert_key, 'motd');
							array_push($insert_val, $db->quote($InfoValue));
						} else if($InfoKey === 'HostName') {
							array_push($insert_key, 'motd');
							array_push($insert_val, $db->quote($InfoValue));
						} else if($InfoKey === 'players') {
							array_push($insert_key, 'max_players');
							array_push($insert_val, $db->quote($InfoValue["max"], \PDO::PARAM_INT));
							array_push($insert_key, 'current_players');
							array_push($insert_val, $db->quote($InfoValue["online"], \PDO::PARAM_INT));
							if($InfoValue["sample"]) {
								$players = array();
								foreach($InfoValue["sample"] as $player) {
									array_push($players, $player["name"]);
								}
								array_push($insert_key, 'players_list');
								array_push($insert_val, $db->quote('{'.implode(',',$players).'}'));
							}
						}
					}
				} else {
					echo "We didn't receive any data from your server...";
				}
				$geo = \Web\Geo::instance()->location(strtolower(trim($f3->clean($f3->get('POST.address')))));
				array_push($insert_key, 'country');
				array_push($insert_val, $db->quote($geo["country_name"]));
				array_push($insert_key, 'country_code');
				array_push($insert_val, $db->quote($geo["country_code"]));
				array_push($insert_key, 'latency');
				array_push($insert_val, $db->quote(floor($Timer*1000), \PDO::PARAM_INT));
				$result = $db->exec('INSERT INTO servers ('.implode(',', $insert_key).') VALUES ('.implode(',', $insert_val).');');
				echo $db->log();
				$laststep = $db->exec('SELECT identifier FROM servers WHERE address=:addr AND port=:pt', array(':addr'=>strtolower(trim($f3->get('POST.address'))), ':pt'=>trim($f3->get('POST.port'))));
				rerouteWithName($f3, '@info', $laststep[0]["identifier"], $db);
			} else {
				// Error - probably offline...
				echo "Why is your server offline?";
			}
		} else {
			$Query = minecraftPing($f3->get('POST.address'), $f3->get('POST.queryport'), $Info, $Exception, true);
			
			$Timer = Number_Format( (MicroTime( true ) - $Timer), 4, '.', '' );
			
			if( !isset( $Exception ) ) {
				if( ( $Info = $Query->GetInfo( ) ) !== false ) {
					$Keys = Array('HostName'   => 'motd', 'Version'    => 'server_version', 'Plugins'    => 'Plugins', 'Players'    => 'current_players', 'MaxPlayers' => 'max_players', 'Software'   => 'server_wrapper');
					foreach( $Info as $InfoKey => $InfoValue ) {
						if( isset($Keys[$InfoKey])) {
								array_push($insert_key, $Keys[$InfoKey]);
							if( Is_Array( $InfoValue ) ) {
								array_push($insert_val, $db->quote('{'.implode(',',$InfoValue).'}'));
							} else {
								array_push($insert_val, $db->quote($InfoValue));
							}
						}
					}
				} else {
					die("We didn't receive any data from your server...");
				}
				if( ( $Players = $Query->GetPlayers( ) ) !== false ) {
					array_push($insert_key, 'players_list');
					array_push($insert_val, $db->quote('{'.implode(',',$Players).'}'));
				}
				$geo = \Web\Geo::instance()->location(strtolower(trim($f3->clean($f3->get('POST.address')))));
				array_push($insert_key, 'country');
				array_push($insert_val, $db->quote($geo["country_name"]));
				array_push($insert_key, 'country_code');
				array_push($insert_val, $db->quote($geo["country_code"]));
				array_push($insert_key, 'latency');
				array_push($insert_val, $db->quote(floor($Timer*1000), \PDO::PARAM_INT));
				$result = $db->exec('INSERT INTO servers ('.implode(',', $insert_key).') VALUES ('.implode(',', $insert_val).');');
				$laststep = $db->exec('SELECT identifier FROM servers WHERE address=:addr AND port=:pt', array(':addr'=>strtolower(trim($f3->get('POST.address'))), ':pt'=>trim($f3->get('POST.port'))));
				rerouteWithName($f3, '@info', $laststep[0]["identifier"], $db);
				echo $db->log();
			} else {
				// Error - probably offline...
				echo "Why is your server offline?";
			}
		}
	} else {
		echo "The server already exists, silly...";
	}
});

$f3->route('GET /test', function($f3) {
$w = stream_get_wrappers();
echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
echo 'wrappers: ', var_dump($w);
});

$f3->route('POST @verify_owner: /dyroi', function($f3) {
	$return_array = array();
	$return_array["post_data"] = $f3->get('POST');
	
	$vs = CodeGenerator::getServerVerifier();
	$vs->setKey($f3->get('POST.challenge'));
	
	$Info = false;
	$Query = null;
	$Exception = null;
	
	try {
		$Query = new MinecraftPing( $f3->get('POST.host'), $f3->get('POST.port'), 10 );
		
		$Info = $Query->Query( );
		
		if( $Info === false ) {
			$Query->Close( );
			$Query->Connect( );
			
			$Info = $Query->QueryOldPre17( );
		}
	} catch( MinecraftPingException $e ) {
		$Exception = $e;
	}
	
	if( $Query !== null ) {
		$Query->Close( );
	}
	
	if( $Exception == null ) {
		if( $Info !== false ) {
			if(isset($Info['HostName'])) {
				if($vs->getAnswer() == trim($Info['HostName'])) {
					$return_array["response"] = 'true';
				} else {
					$return_array["response"] = 'bad motd';
				}
			} elseif(isset($Info['description'])) {
				if($vs->getAnswer() == trim($Info['description'])) {
					$return_array["response"] = 'true';
				} else {
					$return_array["response"] = 'bad motd';
				}
			} else {
				$return_array["response"] = 'no motd received';
			}
		} else {
			$return_array["response"] = 'no data received';
		}
	} else {
		$return_array["response"] = 'unknown error';
	}
	echo json_encode($return_array);
});

$f3->route('POST @edit: /edit', function($f3) {
	if(!$f3->get('is_logged_in')) die('not logged in');
	if(!$f3->get('POST.server_id')) die('no server found');
	
	$db = getDB();
	$result = $db->exec('SELECT address, port, query_port, owner FROM servers WHERE identifier=' . $f3->get('POST.server_id'));
	
	$gcu = $f3->get('get_current_user');
	if($result[0]["owner"] != $gcu()["id"]) die('bad owner');
	
	$update_query = array();
	$return_array = array();
	$return_array["post_data"] = $f3->get('POST');
	
	if($f3->get('POST.name') !== null) array_push($update_query, 'name='.$db->quote($f3->clean($f3->get('POST.name'))));
	if($f3->get('POST.website') !== null) array_push($update_query, 'website='.$db->quote(fixurl(strtolower(trim($f3->clean($f3->get('POST.website')))))));
	if($f3->get('POST.queryport') == '') array_push($update_query, 'query_port=NULL');
	elseif($f3->get('POST.queryport') !== null) array_push($update_query, 'query_port='.$db->quote(trim($f3->clean($f3->get('POST.queryport'))), \PDO::PARAM_INT));
	if($result[0]['query_port'] != '' || $f3->get('POST.queryport') !== null) {
		if($f3->get('POST.showplugins') !== null) array_push($update_query, 'show_plugins='.$db->quote($f3->get('POST.showplugins'), \PDO::PARAM_BOOL));
	}
	if($f3->get('POST.tags') !== null) array_push($update_query, 'tags='.$db->quote('{' . implode(',', $f3->get('POST.tags')) . '}'));
	if($f3->get('POST.votifierport') == '') array_push($update_query, 'votifier_port=NULL');
	elseif($f3->get('POST.votifierport') !== null) array_push($update_query, 'votifier_port='.$db->quote(trim($f3->clean($f3->get('POST.votifierport'))), \PDO::PARAM_INT));
	if($result[0]['query_port'] != '' || $f3->get('POST.queryport') !== null) {
		if($f3->get('POST.votifierkey') !== null) array_push($update_query, 'votifier_key='.$db->quote(trim($f3->clean($f3->get('POST.votifierkey')))));
	}
	if($f3->get('POST.description') !== null) array_push($update_query, 'description='.$db->quote($f3->encode($f3->clean($f3->get('POST.description'), 'b,a,i,u,center,h1,h2,h3,h4,h5,h6,em'))));
	
	$Timer = MicroTime( true );
	if($result[0]['query_port'] == '' || ($f3->get('POST.queryport') !== null && $f3->get('POST.queryport') == '')) {
		$Info = false;
		$Exception = null;
		
		minecraftPing($result[0]['address'], $result[0]['port'], $Info, $Exception);
		
		$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
		
		if( !isset( $Exception ) ) {
			if( $Info !== false ) {
				foreach( $Info as $InfoKey => $InfoValue ) {
					if($InfoKey === 'favicon') {
						array_push($update_query, 'favicon='.$db->quote($InfoValue));
					} else if($InfoKey === 'version') {
						array_push($update_query, 'server_version='.$db->quote(preg_replace("/[^0-9,.]/", "", $InfoValue["name"])));
						if(strlen($InfoValue["name"])>10) {
							array_push($update_query, 'server_wrapper='.$db->quote(trim(preg_replace('/[0-9.,]/', '', $InfoValue["name"]))));
						}
					} else if($InfoKey === 'description') {
						array_push($update_query, 'motd='.$db->quote($InfoValue));
					} else if($InfoKey === 'HostName') {
						array_push($update_query, 'motd='.$db->quote($InfoValue));
					} else if($InfoKey === 'players') {
						array_push($update_query, 'max_players='.$db->quote($InfoValue["max"], \PDO::PARAM_INT));
						array_push($update_query, 'current_players='.$db->quote($InfoValue["online"], \PDO::PARAM_INT));
						if($InfoValue["sample"]) {
							$players = array();
							foreach($InfoValue["sample"] as $player) {
								array_push($players, $player["name"]);
							}
							array_push($update_query, 'players_list='.$db->quote('{'.implode(',',$players).'}'));
						}
					}
				}
			} else {
				$return_array["info"] = "no data";
			}
			array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'times_online=times_online+1');
			array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$f3->get('POST.server_id').';');
			// $return_array["db_log"] = echo $db->log(); // Do we really want to show everyone our database structure?
			$return_array["response"] = "ok";
		} else {
			// Error - probably offline...
			$return_array["info"] = "offline";
			$return_array["response"] = "no";
		}
	} else {
		$Query = minecraftPing($result[0]['address'], ($f3->get('POST.queryport') !== null ? trim($f3->clean($f3->get('POST.queryport'))) : $result[0]['query_port']), $Info, $Exception, true);
		
		$Timer = Number_Format( (MicroTime( true ) - $Timer), 4, '.', '' );
		
		if( !isset( $Exception ) ) {
			if( ( $Info = $Query->GetInfo( ) ) !== false ) {
				$Keys = Array('HostName'   => 'motd', 'Version'    => 'server_version', 'Plugins'    => 'Plugins', 'Players'    => 'current_players', 'MaxPlayers' => 'max_players', 'Software'   => 'server_wrapper');
				foreach( $Info as $InfoKey => $InfoValue ) {
					if( isset($Keys[$InfoKey])) {
						if( Is_Array( $InfoValue ) ) {
							array_push($update_query, $Keys[$InfoKey]."=".$db->quote('{'.implode(',',$InfoValue).'}'));
						} else {
							array_push($update_query, $Keys[$InfoKey]."=".$db->quote($InfoValue));
						}
					}
				}
			} else {
				$return_array["info"] = "no data";
			}
			if( ( $Players = $Query->GetPlayers( ) ) !== false ) {
				array_push($update_query, 'players_list='.$db->quote('{'.implode(',',$Players).'}'));
			}
			array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'times_online=times_online+1');
			array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$f3->get('POST.server_id').';');
			// $return_array["db_log"] = echo $db->log(); // Do we really want to show everyone our database structure?
			$return_array["response"] = "ok";
		} else {
			// Error - probably offline...
			$return_array["info"] = "offline";
			$return_array["response"] = "no";
		}
	}
	echo json_encode($return_array);
});

$f3->route('POST @delete: /del', function($f3) {
	if(!$f3->get('is_logged_in')) die('not logged in');
	if(!$f3->get('POST.server_id')) die('no server found');
	
	$db = getDB();
	$result = $db->exec('SELECT owner FROM servers WHERE identifier=' . $f3->get('POST.server_id'));
	
	$gcu = $f3->get('get_current_user');
	if($result[0]["owner"] != $gcu()["id"]) die('bad owner');
	
	$result = $db->exec('DELETE FROM servers WHERE identifier=' . $f3->get('POST.server_id'));
	if($result == 1) die(json_encode(array('response' => 'ok')));
	else die('ueo');
});

$f3->route('POST @transfer_owner: /igttp', function($f3) {
	$f3->set('POST', $f3->get('GET'));
	if(!$f3->get('is_logged_in')) die('not logged in');
	if($f3->get('POST.server_id') === null) die('no server found');
	if($f3->get('POST.new_owner_email') === null) die('no email found');
	
	$db = getDB();
	$result = $db->exec('SELECT name,owner FROM servers WHERE identifier=' . $f3->get('POST.server_id'));
	
	$gcu = $f3->get('get_current_user');
	if($result[0]["owner"] != $gcu()["id"]) die('bad owner');
	
	$f3->set('confirmation_code', CodeGenerator::generate());
	$f3->set('result', $result);
	
	$result = $db->exec('INSERT INTO codes (type,code,expiration,extra) VALUES (?,?,?,?);', array(1=>'otc', 2=>$f3->get('confirmation_code'), 3=>time() + 172800, 4=>json_encode(array("for"=>$f3->get('POST.server_id'), "used"=>false))));
	
	$smtp = new SMTP ( 'smtp.gmail.com', 465, 'ssl', 'steve@mclister.net', 'lammy123!@#' );
	$smtp->set('To', '<'.$f3->clean($f3->get('POST.new_owner_email')).'>');
	$smtp->set('From', '"McLister Support" <support@mclister.net>');
	$smtp->set('Content-Type', 'text/html; charset=utf-8');
	$smtp->set('Subject', 'You\'ve got a new server (listing)!');
	$smtp->send(Template::instance()->render('templates/new_owner_confirm_email.html'));
});

$f3->route('GET @confirm_tranfer_owner: /cpt', function($f3) {
	if($f3->get('is_logged_in')) {
		$db = getDB();
		$result = $db->exec('SELECT type,expiration,extra FROM codes WHERE code=?;', $f3->get('GET.cc'));
		if($result[0]["expiration"] > time() && $result[0]["type"] == 'otc') {
			$data = json_decode($result[0]["extra"], true);
			if($data["used"]) {
				// ERROR CODE HAS ALREADY BEEN USED!
				echo "bad code";
			} else {
				$gcu = $f3->get('get_current_user');
				if(count($db->exec('UPDATE servers SET owner='.$gcu()["id"].' WHERE identifier=?;', $data["for"])) == 1) {
					$f3->set('result', $db->exec('SELECT name FROM servers WHERE identifier=?;', $data["for"]));
					$smtp = new SMTP ( 'smtp.gmail.com', 465, 'ssl', 'steve@mclister.net', 'lammy123!@#' );
					$smtp->set('To', '<'.$gcu()["email"].'>');
					$smtp->set('From', '"McLister Support" <support@mclister.net>');
					$smtp->set('Content-Type', 'text/html; charset=utf-8');
					$smtp->set('Subject', 'Server Transfer Complete!');
					$smtp->send(Template::instance()->render('templates/new_owner_success_email.html'));
					$data["used"] = true;
					$db->exec('UPDATE codes SET extra=? WHERE code=?;', array(1=>json_encode($data),2=>$f3->get('GET.cc')));
					echo "owner transfer successful";
				} else {
					// SOMETHING HAPPENED, THE UPDATE DIDN'T GO THROUGH! D:
					echo "update failed";
				}
			}
		} else {
			// WRONG CODE TYPE OR CODE EXPIRED
			echo "bad code";
		}
	} else {
		$f3->set('main_content','templates/login_page.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	}
});

$f3->route('POST @ajax_vote: /ajax/vote', function($f3) {
	if($f3->get('POST.server_id') === null) die('no server found');
	
	require('lib/recaptchalib.php');
	$db = getDB();
	$gcu = $f3->get('get_current_user');
	$now = time();
	$vm = new VoteMgmt($db);
	$lastvote = $vm->getLastVoteTime($f3->get('POST.server_id'), $f3->get('POST.mc_name'), $gcu());
	$result = $db->exec('SELECT vote_frequency,votifier_port,votifier_key,address,identifier FROM servers WHERE identifier=?;', $f3->get('POST.server_id'));
	$vote_freq = $result[0]["vote_frequency"];
	$resp = recaptcha_check_answer($f3->get('recaptcha_private_key'), $_SERVER["REMOTE_ADDR"], $f3->get('POST.recaptcha_challenge_field'), $f3->get('POST.recaptcha_response_field'));

	if (!$resp->is_valid) {
		die ("bad captcha");
	} else {
		if($lastvote + $vote_freq > $now) {
			echo 'wait' . ($lastvote + $vote_freq - $now);
			die();
		} else {
			if($vm->vote($result[0], $f3->get('POST.mc_name'), $gcu())) {
				die('ok');
			} else {
				die('no');
			}
		}
	}
});

$f3->route('GET @clef: /clef', function($f3) {
	if (isset($_GET["code"]) && $_GET["code"] != "") {
		if($f3->clef->login($_GET["code"])) {
			echo "Logged In;";
			if(isset($_GET["next_page"])) {
				$f3->reroute($_GET["next_page"]);
			} else {
				$f3->reroute('@members');
			}
		}
	}
});

$f3->route('GET @login: /login', function($f3) {
	if($f3->get('is_logged_in')) {
		$f3->reroute('@members');
	} else {
		$f3->set('main_content','templates/login_page.html');
		echo Template::instance()->render('templates/page_wrapper.html');
	}
});

$f3->route('GET /me/@tab', function($f3,$params) {
	if($f3->get('is_logged_in')) {
		$f3->set('main_content','templates/members.html');
		$f3->set('tab',$params["tab"]);
		echo Template::instance()->render('templates/page_wrapper.html');
	} else {
		$f3->reroute('@login');
	}
});
$f3->route('GET @members: /me', function($f3,$params) {
	if($f3->get('is_logged_in')) {
		$f3->reroute($f3->get('ALIASES.members').'/account');
	} else {
		$f3->reroute('@login');
	}
});
$f3->route('POST @update_account_info: /uyaie', function($f3) {
	if($f3->get('POST.change') === null || $f3->get('POST.value') === null) {
		die('no input');
	} else {
		$db = getDB();
		$gcu = $f3->get('get_current_user');
		$db->exec('UPDATE users SET '.$f3->get('POST.change').'=:value WHERE id=:userid', array(":value"=>$f3->clean($f3->get('POST.value')),":userid"=>$gcu()["id"]));
		echo $db->log();
	}
});

$f3->route('POST /logout_hook', function($f3) {
	if(isset($_POST['logout_token'])) {
		if($f3->clef->logout_hook($f3->get('REQUEST.logout_token'))) echo "SUCCESS";
		else echo "ERROR";
	}
});

$f3->route('GET @logout: /logout', function($f3) {
	if($f3->get('is_logged_in')) {
		if($f3->clef->logout($f3->get('SESSION.user_id'))) {
			$f3->reroute('@home');
		} else {
			echo "Error logging out.";
		}
	} else {
		$f3->reroute('@home');
	}
});

$f3->route('POST @low_level_ping: /llping', function($f3) {
	try{
		$sock = fsockopen($f3->get('POST.host'), $f3->get('POST.port'), $errno, $errstr, 2);
		if (!$sock) {
			echo false;
		} else {
			fclose($sock);
			echo true;
		}
	} catch(Exception $e) {
		echo false;
	}
});

$f3->route('POST @minecraft_query_ping: /mqping', function($f3) {
	$Query = new MinecraftQuery( );
	
	try {
		$Query->Connect($f3->get('POST.host'), $f3->get('POST.port'), 1 );
	} catch( MinecraftQueryException $e ) {
		$Exception = $e;
	}
	
	if(isset($Exception)) echo false;
	else echo true;
});

$f3->route('GET /check-deep',
	function($f3) {
		$sid = 1;
		$db = getDB();
		$Timer = MicroTime( true );
		$update_query = array();
		
		$result = $db->exec('SELECT address, port, query_port FROM servers WHERE identifier=' . $sid);
		
		if($result[0]["query_port"] == "") {
			$Info = false;
			$Exception = null;
			
			minecraftPing($result[0]['address'], $result[0]['port'], $Info, $Exception);
			
			$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
			
			if( !isset( $Exception ) ) {
				if( $Info !== false ) {
					foreach( $Info as $InfoKey => $InfoValue ) {
						if($InfoKey === 'favicon') {
							array_push($update_query, 'favicon='.$db->quote($InfoValue));
						} else if($InfoKey === 'version') {
							array_push($update_query, 'server_version='.$db->quote(preg_replace("/[^0-9,.]/", "", $InfoValue["name"])));
							if(strlen($InfoValue["name"])>10) {
								array_push($update_query, 'server_wrapper='.$db->quote(trim(preg_replace('/[0-9.,]/', '', $InfoValue["name"]))));
							}
						} else if($InfoKey === 'description') {
							array_push($update_query, 'motd='.$db->quote($InfoValue));
						} else if($InfoKey === 'HostName') {
							array_push($update_query, 'motd='.$db->quote($InfoValue));
						} else if($InfoKey === 'players') {
							array_push($update_query, 'max_players='.$db->quote($InfoValue["max"], \PDO::PARAM_INT));
							array_push($update_query, 'current_players='.$db->quote($InfoValue["online"], \PDO::PARAM_INT));
							if($InfoValue["sample"]) {
								$players = array();
								foreach($InfoValue["sample"] as $player) {
									array_push($players, $player["name"]);
								}
								array_push($update_query, 'players_list='.$db->quote('{'.implode(',',$players).'}'));
							}
						}
					}
				} else {
					// No data received
				}
				array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
				array_push($update_query, 'times_checked=times_checked+1');
				array_push($update_query, 'times_online=times_online+1');
				array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
				$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$sid.';');
				echo $db->log();
			} else {
				// Error - probably offline...
				array_push($update_query, 'is_online='.$db->quote('false', \PDO::PARAM_BOOL));
				array_push($update_query, 'times_checked=times_checked+1');
				$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$sid.';');
				echo $db->log();
			}
		} else {
			$Query = minecraftPing($result[0]['address'], $result[0]['query_port'], $Info, $Exception, true);
			
			$Timer = Number_Format( (MicroTime( true ) - $Timer), 4, '.', '' );
			
			if( !isset( $Exception ) ) {
				if( ( $Info = $Query->GetInfo( ) ) !== false ) {
					$Keys = Array('HostName'   => 'motd', 'Version'    => 'server_version', 'Plugins'    => 'Plugins', 'Players'    => 'current_players', 'MaxPlayers' => 'max_players', 'Software'   => 'server_wrapper');
					$i=0;
					foreach( $Info as $InfoKey => $InfoValue ) {
						if( isset($Keys[$InfoKey])) {
							if( Is_Array( $InfoValue ) ) {
								array_push($update_query, $Keys[$InfoKey].'='.$db->quote('{'.implode(',',$InfoValue).'}'));
							} else {
								array_push($update_query, $Keys[$InfoKey].'='.$db->quote($InfoValue));
							}
						}
					}
				} else {
					// No data recieved
				}
				if( ( $Players = $Query->GetPlayers( ) ) !== false ) {
					array_push($update_query, 'players_list='.$db->quote('{'.implode(',',$Players).'}'));
				} else {
					// No players
				}
				array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
				array_push($update_query, 'times_checked=times_checked+1');
				array_push($update_query, 'times_online=times_online+1');
				array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
				$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$sid.';');
				echo $db->log();
			} else {
				// Error - probably offline...
				array_push($update_query, 'is_online='.$db->quote('false', \PDO::PARAM_BOOL));
				array_push($update_query, 'times_checked=times_checked+1');
				$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$sid.';');
				echo $db->log();
			}
		}
	}
);

$f3->route('GET /apiv1/server/@id.json', function($f3, $params){
});
$f3->route('POST /apiv1/poll', function($f3, $params){
	$db = getDB();
	$headers = getallheaders();
	$data = file_get_contents('php://input');
	$pkdat = $db->exec('SELECT private FROM polling_keys WHERE public=:pub', array(':pub'=>$headers["X-Public"]));
	
	if(count($pkdat) > 0) {
		$privatekey = $pkdat[0]["private"];
		if(md5($headers["X-Time"].$data.$headers["X-Length"].$privatekey) == $headers["X-Hash"]) {
			$server_data = $db->exec('SELECT identifier,address,query_port,port FROM servers');
			echo json_encode($server_data);
		} else {
			echo "unauthorized";
		}
	} else {
		echo "unauthorized";
	}
});
$f3->route('POST /apiv1/push', function($f3, $params){
	$db = getDB();
	$headers = getallheaders();
	$data = file_get_contents('php://input');
	$pkdat = $db->exec('SELECT private FROM polling_keys WHERE public=:pub', array(':pub'=>$headers["X-Public"]));
	
	if(count($pkdat) > 0) {
		$privatekey = $pkdat[0]["private"];
		if(md5($headers["X-Time"].$data.$headers["X-Length"].$privatekey) == $headers["X-Hash"]) {
			$query_cache = array();
			$query_params = array();
			foreach(json_decode($data, true) as $id => $server) {
				$values = array();
				$query = 'UPDATE servers SET ';
				foreach($server as $key => $value) {
					if(is_array($value)) {
						$query .= $key . '=:' . $key . ',';
						$values[":" . $key] = '{'.implode(',', $value).'}';
					} else {
						$query .= $key . '=:' . $key . ',';
						$values[":" . $key] = $value;
					}
				}
				$query .= 'times_checked=times_checked+1,';
				$query .= 'times_online=times_online+1,';
				$query .= 'last_checked=clock_timestamp(),';
				$query .= 'last_online=clock_timestamp()';
				$query .= ' WHERE identifier=:identifier;';
				$values[":identifier"] = $id;
				array_push($query_cache, $query);
				array_push($query_params, $values);
			}
			$db->exec($query_cache, $query_params);
			echo 'data received';
		} else {
			$f3->status(401);
		}
	} else {
		$f3->status(401);
	}
});

$f3->route('GET @paypal_success: /paypal/success', function($f3, $params){
	$f3->set('main_content','templates/paypal_payment_successful.html');
	echo Template::instance()->render('templates/page_wrapper.html');
});
$f3->route('GET @paypal_cancel: /paypal/cancel', function($f3, $params){
	$f3->set('main_content','templates/paypal_payment_cancel.html');
	echo Template::instance()->render('templates/page_wrapper.html');
});
$f3->route('POST @paypal_checkout: /paypal/checkout', function($f3, $params){
	$bundles = array(
		'10' => array(
			'amt' => '5.00',
			'desc' => '10 McPoints',
		),
		'35' => array(
			'amt' => '15.00',
			'desc' => '30 + 5 McPoints',
		),
		'65' => array(
			'amt' => '25.00',
			'desc' => '50 + 15 McPoints',
		),
		'150' => array(
			'amt' => '50.00',
			'desc' => '100 + 50 McPoints',
		),
	);
	
	if($f3->get('POST.bundle') === null) $f3->set('POST.bundle', false);
	if($f3->get('POST.bundle') == true) {
		$amt = $bundles[$f3->get('POST.amount')]["amt"];
		$desc = $bundles[$f3->get('POST.amount')]["desc"];
	} else {
		$amt = $f3->get('POST.amount')*.5;
		$desc = $f3->get('POST.amount') . ' McPoints';
	}

	$paypal = new PayPal($f3->get('paypal_facilitator_email'), $f3->get('paypal_facilitator_passwd'), $f3->get('paypal_facilitator_signature'), $f3->get('paypal_is_sandbox'));
	
	$result = $paypal->SetExpressCheckout(array(
		'PAYMENTREQUEST_0_AMT' => $amt,
		'PAYMENTREQUEST_0_PAYMENTACTION' => 'sale',
		'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
		'PAYMENTREQUEST_0_CUSTOM' => $f3->get('POST.amount'),
		'PAYMENTREQUEST_0_DESC' => $desc,
		'RETURNURL' => 'http://'.$f3->get('JAR.domain').$f3->get('ALIASES.paypal_checkout'),
		'CANCELURL' => 'http://'.$f3->get('JAR.domain').$f3->get('ALIASES.paypal_cancel'),
		'REQCONFIRMSHIPPING' => '0',
		'NOSHIPPING' => '1',
		'ALLOWNOTE' => '1'
	));
	
	if($result["ACK"] != 'Success') die('error');
	else $f3->reroute($f3->get('paypal_base_url').'/cgi-bin/webscr?cmd=_express-checkout&token='.$result["TOKEN"]);
});
$f3->route('GET /paypal/checkout', function($f3, $params){
	$paypal = new PayPal($f3->get('paypal_facilitator_email'), $f3->get('paypal_facilitator_passwd'), $f3->get('paypal_facilitator_signature'), $f3->get('paypal_is_sandbox'));
	$result = $paypal->GetExpressCheckoutDetails(array(
		"TOKEN" => $f3->get('GET.token'),
	));
	
	$f3->set('paypal_checkout_details', $result);
	$f3->set('main_content','templates/paypal_checkout_confirm.html');
	echo Template::instance()->render('templates/page_wrapper.html');
});
$f3->route('POST @paypal_do_payment: /paypal/dopay', function($f3, $params){
	$paypal = new PayPal($f3->get('paypal_facilitator_email'), $f3->get('paypal_facilitator_passwd'), $f3->get('paypal_facilitator_signature'), $f3->get('paypal_is_sandbox'));
	$result = $paypal->GetExpressCheckoutDetails(array(
		"TOKEN" => $f3->get('POST.token'),
	));
	$desc = $result["DESC"];
	$amount = $result["PAYMENTREQUEST_0_CUSTOM"];
	$result = $paypal->DoExpressCheckoutPayment(array(
		"TOKEN" => $result["TOKEN"],
		'PAYMENTREQUEST_0_AMT' => $result["AMT"],
		'PAYMENTREQUEST_0_PAYMENTACTION' => 'sale',
		'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
		'PAYMENTREQUEST_0_NOTIFYURL' => 'http://'.$f3->get('JAR.domain').$f3->get('ALIASES.paypal_ipn'),
		'PAYERID' => $result["PAYERID"],
	));
	if($result["PAYMENTINFO_0_ACK"] == 'Success') {
		$gcu = $f3->get('get_current_user');
		$db = getDB();
		$db->exec('INSERT INTO paypal_txns (txn_id,amount,item,target_user_id,payment_status,total_points) VALUES (:txnid,:amount,:item,:targetuid,:paystatus,:totepts)', array(':txnid'=>$result["PAYMENTINFO_0_TRANSACTIONID"],':amount'=>$result["PAYMENTINFO_0_AMT"],':item'=>$desc,':targetuid'=>$gcu()["id"],':paystatus'=>$result["PAYMENTINFO_0_PAYMENTSTATUS"],':totepts'=>$amount));
		$f3->reroute('@paypal_success');
	}
});
$f3->route('POST @paypal_ipn: /paypal/ipn', function($f3, $params){
	header('HTTP/1.1 200 OK');
	
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req  .= "&$key=$value";
	}

	$ch = curl_init($f3->get('paypal_base_url') . '/cgi-bin/webscr');
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
	 
	if( !($res = curl_exec($ch)) ) {
		error_log("Got " . curl_error($ch) . " when processing IPN data");
		curl_close($ch);
		exit;
	}
	curl_close($ch);
	
	if (strcmp ($res, "VERIFIED") == 0) {
		// The IPN is verified, process it
		if($f3->get('POST.payment_status') == 'Completed') {
			$db = getDB();
			$result = $db->exec('SELECT id,target_user_id,total_points FROM paypal_txns WHERE txn_id=:txnid',array(':txnid'=>$f3->get('POST.txn_id')));
			if(empty($result)) {
				error_log("Found unknown txn id, " . $f3->get('POST.txn_id') . ", when processing IPN data");
			} else {
				$db->exec('UPDATE paypal_txns SET payment_status=:paystatus WHERE txn_id=:txnid AND id=:id', array(':paystatus'=>$f3->get('POST.payment_status'),':txnid'=>$f3->get('POST.txn_id'),':id'=>$result[0]["id"]));
				$db->exec('UPDATE users SET mcpoints=mcpoints+:newpts WHERE id=:uid', array(':newpts'=>$result[0]["total_points"],':uid'=>$result[0]["target_user_id"]));
				error_log($db->log());
			}
		}
	} else if (strcmp ($res, "INVALID") == 0) {
		// IPN invalid, log for manual investigation
		error_log("The response from IPN was: ".$res);
	}
});

$f3->route('GET /minify/@type', function($f3,$args) { $f3->set('UI',$args['type'].'/'); echo Web::instance()->minify($_GET['files']); }, 3600);

$f3->set('DEBUG',3);
$f3->run();

function rerouteWithName($f3, $route, $sid, $db = null) {
	if($db == null) $db = getDB();
	$result = $db->exec('SELECT name FROM servers WHERE identifier=\'' . $sid . '\'');
	if(empty($result)) {
		$f3->error(404);
	} else {
		$f3->reroute($route . '(@server_id=' . $sid . ',@encoded_name=' . preg_replace("![^a-z0-9]+!i", "", $result[0]['name']) . ')');
	}
}

function getDB() {
	if(!isset($GLOBALS['DB'])) {
		$GLOBALS['DB'] = new DB\SQL('pgsql:host=127.0.0.1;port=5432;dbname=server_list', 'pgweb', 'mousepad');
	}
	return $GLOBALS['DB'];
}

function minecraftPing($host, $port, &$info, &$ex, $gs4 = false) {
	$query = null;
	if($gs4) {
		$query =  new MinecraftQuery();
		try {
			$query->Connect( $host, $port, 1 );
		} catch( MinecraftQueryException $e ) {
			$ex = $e;
		}
		return $query;
	} else {
		try {
			$query = new MinecraftPing($host, $port, 10);
			
			$info = $query->Query();
			
			if( $info === false ) {
				$query->Close();
				$query->Connect();
				
				$info = $query->QueryOldPre17();
			}
		} catch(MinecraftPingException $e) {
			$ex = $e;
		}
		
		if($query !== null) {
			$query->Close();
		}
	}
}

function fixurl($url) {
	if(strpos($url,'http') === false) {
		return 'http://' . $url;
	} else {
		return $url;
	}
}

function in_array_r($needle, $haystack, $strict = false) {
	foreach ($haystack as $item) {
		if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
			return true;
		}
	}
	
	return false;
}
?>