<?php

class CodeGenerator {
	private static $salt = "ccOnWmgQlkmGIByRSGqhZnaMNEga9qSb40lEq21rTv8RST04hT";
	
	public static function generate() {
		static $key;
		$key = hash('whirlpool', time() + $_SERVER["REMOTE_ADDR"] + self::$salt + time(), false);
		$key = hash('haval128,4', self::$salt + $key + mt_rand() + self::$salt, false);
		return $key;
	}
	
	public static function getServerVerifier() {
		return new ServerVerifier();
	}
}

class ServerVerifier {

	private $salt = "p2IaqZmeMPCBi3g6kA0qhBQCQiOyHkNmd4oTq2uEUwARPr06h7";
	
	public function generateKey() {
		$this->key = hash('whirlpool', 'mcl:' + time() + $this->salt, false);
		return $this->key;
	}
	
	public function setKey($key) {
		$this->key = $key;
	}
	
	public function getAnswer() {
		if(!isset($this->key)) return null;
		return hash('haval128,5', hash('gost', hash('ripemd320', $this->salt) + $this->key + hash('crc32b', $this->salt + $this->key)) + $this->key, false);
	}
}

?>