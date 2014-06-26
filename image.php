<?php
require('al/MinecraftPing.php');
require('al/MinecraftQuery.php');

$publickey = 'b31313d3fc001657d839cc052dd27e5c7d50d29b';
$privatekey = 'f93f83d19b0c46ed6801635bdd990790ff4fa2f04321dacf9c2c08a8423e9d56';
$now = time();
$data = "get_servers_to_poll";

$header = "Content-type: application/x-www-form-urlencoded\r\n";
$header .= "X-Public: " . $publickey . "\r\n";
$header .= "X-Length: " . strlen($data) . "\r\n";
$header .= "X-Time: " . $now . "\r\n";
$header .= "X-Hash: " . md5($now.$data.strlen($data).$privatekey) . "\r\n";

$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => $header,
		'content' => $data
	)
);

$context  = stream_context_create($opts);
$response = file_get_contents("http://dev.mclister.net/apiv1/poll", false, $context);

if(strpos($http_response_header[0],'200') === false) die('did not get response 200');

$servers = json_decode($response, true);
$poll_data = array();
foreach($servers as $server) {
	$Timer = MicroTime( true );
	$data = array();
	
	if($server["query_port"] == "") {
		$Info = false;
		$Query = null;
		
		try {
			$Query = new MinecraftPing( $server["address"], $server["port"], 1 );
			
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
		
		$Timer = Number_Format( MicroTime( true ) - $Timer, 4, '.', '' );
		
		if( !isset( $Exception ) ) {
			if( $Info !== false ) {
				foreach( $Info as $InfoKey => $InfoValue ) {
					if($InfoKey === 'favicon') {
						$data["favicon"] = $InfoValue;
					} else if($InfoKey === 'version') {
						$data["server_version"] = preg_replace("/[^0-9,.]/", "", $InfoValue["name"]);
						if(strlen($InfoValue["name"])>10) {
							$data["server_wrapper"] = preg_replace('/[0-9.,]/', '', $InfoValue["name"]);
						}
					} else if($InfoKey === 'description') {
						$data["motd"] = $InfoValue;
					} else if($InfoKey === 'players') {
						$data["max_players"] = $InfoValue["max"];
						$data["current_players"] = $InfoValue["online"];
						if(!empty($InfoValue["sample"])) {
							$players = array();
							foreach($InfoValue["sample"] as $player) {
								array_push($players, $player["name"]);
							}
							$data["players_list"] = $players;
						} else {
						}
					}
				}
			} else {
				// No data received
			}
			$data["is_online"] = true;
			$data["latency"] = floor($Timer*1000);
		} else {
			// Error - probably offline...
			$data["is_online"] = false;
		}
	} else {
		$Query = new MinecraftQuery( );
		
		try {
			$Query->Connect( $server["address"], $server["query_port"], 1 );
		} catch( MinecraftQueryException $e ) {
			$Exception = $e;
		}
		
		$Timer = Number_Format( (MicroTime( true ) - $Timer), 4, '.', '' );
		
		if( !isset( $Exception ) ) {
			if( ( $Info = $Query->GetInfo( ) ) !== false ) {
				$Keys = Array(
					'HostName'   => 'motd',
					'Version'    => 'server_version',
					'Plugins'    => 'plugins',
					'Players'    => 'current_players',
					'MaxPlayers' => 'max_players',
					'Software'   => 'server_wrapper',
				);
				$i=0;
				foreach( $Info as $InfoKey => $InfoValue ) {
					if( isset($Keys[$InfoKey])) {
						$data[$Keys[$InfoKey]] = $InfoValue;
					}
				}
			} else {
				// No data recieved
			}
			if( ( $Players = $Query->GetPlayers( ) ) !== false ) {
				$data["players_list"] = $Players;
			}
			$data["is_online"] = true;
			$data["latency"] = floor($Timer*1000);
		} else {
			// Error - probably offline...
			$data["is_online"] = false;
		}
	}
	$poll_data[$server["identifier"]] = $data;
}

$now = time();
$data = json_encode($poll_data);

$header = "Content-type: application/x-www-form-urlencoded\r\n";
$header .= "X-Public: " . $publickey . "\r\n";
$header .= "X-Length: " . strlen($data) . "\r\n";
$header .= "X-Time: " . $now . "\r\n";
$header .= "X-Hash: " . md5($now.$data.strlen($data).$privatekey) . "\r\n";

$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => $header,
		'content' => $data
	)
);

$context  = stream_context_create($opts);
$response = file_get_contents("http://dev.mclister.net/apiv1/push", false, $context);

echo $response;
?>