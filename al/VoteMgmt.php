<?php

class VoteMgmt {

	function __construct($db) {
		$this->db = $db;
	}

	function vote($server, $username = null, $user = null) {
		$query_array = array('columns'=>array(),'values'=>array());
		
		if($server["votifier_port"] != '' && $username != '') {
			$this->sendVotifierPacket($server["votifier_key"], $server["address"], $server["votifier_port"], $username);
			array_push($query_array["columns"], 'minecraft_username');
			array_push($query_array["values"], '\''.$username.'\'');
		}
		
		array_push($query_array["columns"], 'server_id');
		array_push($query_array["values"], $server["identifier"]);
		array_push($query_array["columns"], 'ip');
		array_push($query_array["values"], '\''.$_SERVER["REMOTE_ADDR"].'\'');
		if($user !== null) {
			array_push($query_array["columns"], 'user_id');
			array_push($query_array["values"], $user["id"]);
		}
		
		if($log = $this->db->exec('UPDATE servers SET votes=votes+1 WHERE identifier=?',$server["identifier"]) == 1) {
			$this->db->exec('INSERT INTO voting_log (' . implode(',', $query_array["columns"]) . ') VALUES (' . implode(',', $query_array["values"]) . ')');
			return true;
		} else {
			echo $log->log();
			return false;
		}
	}
	
	function getLastVoteTime($server_id, $username = null, $user = null) {
		$result = null;
		if($username !== null && $username != '') {
			$result = $this->db->exec('SELECT last_vote FROM voting_log WHERE server_id='.$server_id.' AND minecraft_username=\''.$username.'\' ORDER BY last_vote DESC LIMIT 1');
		} elseif($user !== null) {
			$result = $this->db->exec('SELECT last_vote FROM voting_log WHERE server_id='.$server_id.' AND user_id='.$user["id"].' ORDER BY last_vote DESC LIMIT 1');
		} else {
			$result = $this->db->exec('SELECT last_vote FROM voting_log WHERE server_id='.$server_id.' AND ip=\''.$_SERVER["REMOTE_ADDR"].'\' ORDER BY last_vote DESC LIMIT 1');
		}
		if(count($result) == 0) {
			return null;
		} else {
			return strtotime(substr($result[0]["last_vote"],0,strpos($result[0]["last_vote"], '.')));
		}
	}

	function sendVotifierPacket($public_key, $server_ip, $server_port, $username) {
	
		$public_key = wordwrap($public_key, 65, "\n", true);
		$public_key = <<<EOF
-----BEGIN PUBLIC KEY-----
$public_key
-----END PUBLIC KEY-----
EOF;
		
		$address = $_SERVER['REMOTE_ADDR'];
		
		$timeStamp = time();
		
		$string = "VOTE\nmclister.net\n$username\n$address\n$timeStamp\n";
		
		$leftover = (256 - strlen($string)) / 2;
		while ($leftover > 0) {
			$string.= "\x0";
			$leftover--;
		}
		
		openssl_public_encrypt($string,$crypted,$public_key);
		
		$socket = fsockopen($server_ip, $server_port, $errno, $errstr, 3);
		if ($socket) {
			fwrite($socket, $crypted);
			return true; 
		} else {
			return false;
		}
	}

}
?>