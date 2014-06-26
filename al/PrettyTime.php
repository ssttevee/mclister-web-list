<?php

class PrettyTime {
	
	function __construct($time) {
		$this->time = $time;
	}
	
	public function toString() {
		$delta = time() - $this->time;
		
		if ($delta < 60) {
			return $delta == 1 ? "one second ago" : $delta . " seconds ago";
		} elseif ($delta < 120) {
			return "a minute ago";
		} elseif($delta < 2700) { // 45 * 60
			return floor($delta / 60) . " minutes ago";
		} elseif($delta < 5400) { // 90 * 60
			return "an hour ago";
		} elseif($delta < 86400) { // 24 * 60 * 60
			return floor($delta / 3600) . " hours ago";
		} elseif($delta < 172800) { // 48 * 60 * 60
			return "yesterday";
		} elseif($delta < 2592000) { // 30 * 24 * 60 * 60
			return floor($delta / 86400) . " days ago";
		} elseif($delta < 31104000) { // 12 * 30 * 24 * 60 * 60
			return floor($delta / 2592000) <= 1 ? "one month ago" : floor($delta / 2592000) + " months ago";
		} else {
			return floor($delta / 31536000) <= 1 ? "one year ago" : floor($delta / 31536000) + " years ago";
		}
	}
	
}

?>