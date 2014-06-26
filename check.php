<?php

require('lib/base.php');
require('al/MinecraftPing.php');
require('al/MinecraftQuery.php');
$db = new DB\SQL('pgsql:host=127.0.0.1;port=5432;dbname=server_list', 'pgweb', 'mousepad');
$result = $db->exec('SELECT identifier,address,query_port,port FROM servers');

foreach($result as $server) {
	$Timer = MicroTime( true );
	$update_query = array();
	
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
						array_push($update_query, 'favicon='.$db->quote($InfoValue));
					} else if($InfoKey === 'version') {
						array_push($update_query, 'server_version='.$db->quote(preg_replace("/[^0-9,.]/", "", $InfoValue["name"])));
						if(strlen($InfoValue["name"])>10) {
							array_push($update_query, 'server_wrapper='.$db->quote(trim(preg_replace('/[0-9.,]/', '', $InfoValue["name"]))));
						}
					} else if($InfoKey === 'description') {
						array_push($update_query, 'motd='.$db->quote($InfoValue));
					} else if($InfoKey === 'players') {
						array_push($update_query, 'max_players='.$db->quote($InfoValue["max"], \PDO::PARAM_INT));
						array_push($update_query, 'current_players='.$db->quote($InfoValue["online"], \PDO::PARAM_INT));
						if(!empty($InfoValue["sample"])) {
							$players = array();
							foreach($InfoValue["sample"] as $player) {
								array_push($players, $player["name"]);
							}
							array_push($update_query, 'players_list='.$db->quote('{'.implode(',',$players).'}'));
						} else {
							array_push($update_query, 'players_list=NULL');
						}
					}
				}
			} else {
				// No data received
			}
			array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'times_online=times_online+1');
			array_push($update_query, 'last_checked=clock_timestamp()');
			array_push($update_query, 'last_online=clock_timestamp()');
			array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$server["identifier"].';');
		} else {
			// Error - probably offline...
			array_push($update_query, 'is_online='.$db->quote('false', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'last_checked=clock_timestamp()');
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$server["identifier"].';');
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
					'Plugins'    => 'Plugins',
					'Players'    => 'current_players',
					'MaxPlayers' => 'max_players',
					'Software'   => 'server_wrapper',
				);
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
				array_push($update_query, 'players_list=NULL');
			}
			array_push($update_query, 'is_online='.$db->quote('true', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'times_online=times_online+1');
			array_push($update_query, 'last_checked=clock_timestamp()');
			array_push($update_query, 'last_online=clock_timestamp()');
			array_push($update_query, 'latency='.$db->quote(floor($Timer*1000), \PDO::PARAM_INT));
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$server["identifier"].';');
		} else {
			// Error - probably offline...
			array_push($update_query, 'is_online='.$db->quote('false', \PDO::PARAM_BOOL));
			array_push($update_query, 'times_checked=times_checked+1');
			array_push($update_query, 'last_checked=clock_timestamp()');
			$result = $db->exec('UPDATE servers SET ' . implode(',',$update_query) . ' WHERE identifier='.$server["identifier"].';');
		}
	}
}
echo '<pre>'.$db->log().'</pre>';

?>