<?php

class ClefMgmt {
	
	private static $instance;
	private $clef_base_url = 'https://clef.io/api/v1/';
	private $app_id = '45e95f6cb130770e745671c326e156c3';
	private $app_secret = '3e02ef2261f213d08a3ec9e9e4909e04';
	
	function __construct($f3, $db) {
		$this->f3 = $f3;
		$this->pdo = $db;
	}
	
	public static function getInstance() {
		
		if(!self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
		
	}
	
	public function validateSession() {
		
		$result = $this->pdo->exec('SELECT logged_out_at FROM users WHERE clef_id=' . $this->f3->get('SESSION.user_id') . ';');
		
		if(!empty($result)) {
			
			$logged_out_at = $result[0]["logged_out_at"];
			
			if($this->f3->get('SESSION.logged_in_at') === null || $this->f3->get('SESSION.logged_in_at') < $logged_out_at) { // or if the user is logged out with Clef
				$this->f3->clear('SESSION'); // log the user out on this site
				return false;
			}
			return true;
			
		} else return false;
	}
	
	public function isLoggedIn() {
		if(count($this->f3->get('SESSION')) > 0) return true;
		else return false;
	}
	
	public function login($code) {
		$postdata = http_build_query(
			array(
				'code' => $code,
				'app_id' => $this->app_id,
				'app_secret' => $this->app_secret
			)
		);
	
		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
	
		// get oauth code for the handshake
		$context  = stream_context_create($opts);
		$response = file_get_contents($this->clef_base_url."authorize", false, $context);
	
		if($response) {
			$response = json_decode($response, true);
	
			// if there's an error, Clef's API will report it
			if(!isset($response['error'])) {
				$access_token = $response['access_token'];
	
				$opts = array('http' =>
					array(
						'method'  => 'GET'
					)
				);
	
				$url = $this->clef_base_url."info?access_token=".$access_token;
	
				// exchange the oauth token for the user's info
				$context  = stream_context_create($opts);
				$response = file_get_contents($url, false, $context);
				if($response) {
					$response = json_decode($response, true);
	
					// again, make sure nothing went wrong
					if(!isset($response['error'])) {
	
						$result = $response['info'];
	
						// reset the user's session
						if (isset($result['id'])&&($result['id']!='')) {
							$this->f3->clear('SESSION');
	
							$this->f3->set('SESSION.name', $result['first_name'].' '.$result['last_name']);
							$this->f3->set('SESSION.email', $result['email']);
							$this->f3->set('SESSION.user_id', $result['id']);
							$this->f3->set('SESSION.logged_in_at', time());	 // timestamp in unix time
	
							$clef_id = $result['id'];
							$name = $result['first_name'] . ' ' . $result['last_name'];
							$email = $result['email'];
							
							$response = $this->pdo->exec('SELECT * FROM users WHERE clef_id=?',$clef_id);
							
							if(count($response) == 0) {
								// user is new, register them
								$response = $this->pdo->exec('INSERT INTO users (clef_id, name, email, logged_out_at) VALUES (?,?,?,0);', array(1=>$clef_id,2=>$name,3=>$email));
							}
							return true;
						}
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
			
		} else {
			return false;
		}
	}
	
	public function logout_hook($logout_token) {
		$postdata = http_build_query(
			array(
				'logout_token' => $logout_token,
				'app_id' => $this->app_id,
				'app_secret' => $this->app_secret
			)
		);

		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);

		$context  = stream_context_create($opts);
		$response = file_get_contents($this->clef_base_url."logout", false, $context);

		$response = json_decode($response, true);

		if (isset($response['success']) && isset($response['clef_id'])) {

			$clef_id = $response['clef_id'];
			$this->logout($clef_id);
			echo $this->pdo->log();
			return true;
		}
		return false;
	}
	
	public function logout($clef_id) {
		$result = $this->pdo->exec('UPDATE users SET logged_out_at=' . time() . ' WHERE clef_id=?;', $clef_id);
		if($result > 0) return true;
		else return false;
	}

}
?>