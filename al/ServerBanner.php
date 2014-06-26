<?php

class ServerBanner {

	function __construct($db, $bg = null) {
		$this->db = $db;
		$this->bg = $bg;
	}
	
	function make($serverid) {
		$image = new Imagick('img/bannerbg/'.($this->bg === null ? mt_rand(0,2) : $this->bg).'.png');
		$draw = new ImagickDraw();
		$draw->setFillColor('#FFFFFF');
		
		$res = $this->db->exec('SELECT name,server_version,is_online,address,port,current_players,max_players,times_online,times_checked,votes FROM servers WHERE identifier = :id', array(':id' => $serverid));
		$data = $res[0];
		$data["name"] = $this->truncateName($image,$data["name"],$data["server_version"]);
		
		$circle = $this->get_status_circle(($data["is_online"]?'online':'offline'), ($data["is_online"]?'#008000':'#800000'));
		$image->compositeImage($circle, Imagick::COMPOSITE_OVER, 27, 16); 
		$circle->clear();
		
		$text_layer = new Imagick();
		$text_layer->newImage($image->getImageWidth(), $image->getImageHeight(), '#00000000');
		
		$draw->setFont('Helvetica-Bold');
		$draw->setFontSize( 36 );
		$text_layer->annotateImage($draw, 62, 45, 0, $data["name"]);
		$metrics = $image->queryFontMetrics($draw, $data["name"]);
		
		$draw->setFont('fonts/AlegSansMed__.ttf');
		$draw->setFontSize( 26 );
		$text_layer->annotateImage($draw, 62 + $metrics["textWidth"] + 15, 48, 0, $data["server_version"]);
		$text_layer->annotateImage($draw, 25, 90, 0, 'Address:');
		$text_layer->annotateImage($draw, 130, 90, 0, $data["address"]);
		$text_layer->annotateImage($draw, 25, 120, 0, 'Slots:');
		$text_layer->annotateImage($draw, 130, 120, 0, $data["current_players"].'/'.$data["max_players"]);
		$text_layer->annotateImage($draw, 25, 150, 0, 'Uptime:');
		$text_layer->annotateImage($draw, 130, 150, 0, (floor($data["times_online"]/$data["times_checked"]*10000)/100).'%');
		$text_layer->annotateImage($draw, 25, 180, 0, 'Votes:');
		$text_layer->annotateImage($draw, 130, 180, 0, $data["votes"]);
		
		$shadow_layer = clone($text_layer); 
		$shadow_layer->setImageBackgroundColor( new ImagickPixel( 'black' ) ); 
		$shadow_layer->shadowImage( 40, 1, 5, 5 ); 
		
		$image->compositeImage( $shadow_layer, Imagick::COMPOSITE_OVER, 0, 0 );
		$shadow_layer->clear();
		
		$image->compositeImage( $text_layer, Imagick::COMPOSITE_OVER, 0, 0 );
		$text_layer->clear();
		
		$this->image = $image;
	}
	
	function resize($size) {
		if($size === 'big') {
		} elseif($size === 'small') {
			$this->image->resizeImage(300, 100, Imagick::FILTER_BOX, 1);
		} else {
			$this->image->resizeImage(450, 150, Imagick::FILTER_TRIANGLE, 1);
		}
	}
	
	function getPng() {
		$this->image->setImageFormat('png');
		return $this->image;
	}
	
	function clean() {
		$this->image->clear();
	}
	
	function get_status_circle($stat, $color = '#000000') {
		$svg = new Imagick();
		$svg->setBackgroundColor(new ImagickPixel('#00000000'));
		$svg->readImage('img/svg/'.$stat.'.svg');
		$svg->scaleImage(30,30);
		$tmp = new Imagick();
		$tmp->newImage(30, 30, $color);
		$tmp->compositeImage($svg, Imagick::COMPOSITE_COPYOPACITY, 0, 0); 
		$svg->clear();
		return $tmp;
	}
	
	function truncateName($image, $name, $ver) {
		$d = new ImagickDraw();
		$d->setFont('Helvetica-Bold');
		$d->setFontSize( 26 );
		$vwidth = $image->queryFontMetrics($d, $ver)["textWidth"];
		$max_w = 498 - $vwidth;
		$d->setFontSize( 36 );
		if($image->queryFontMetrics($d, $name)["textWidth"] <= $max_w) {
			return $name;
		}
		while($image->queryFontMetrics($d, substr($name, 0, -1)."...")["textWidth"] > $max_w) {
			$name = substr($name, 0, -1);
		}
		$d->clear();
		return $name."...";
	}

}
?>