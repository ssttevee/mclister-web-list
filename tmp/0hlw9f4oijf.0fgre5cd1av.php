<!DOCTYPE html>
<!--[if lt IE 7]>		 <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>			 <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>			 <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title></title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="/css/main.css">
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
		<script src="/minify/js?files=/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js,/js/main.js"></script>
	</head>
	<body>
		<!--[if lt IE 7]>
			<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->
		
		<?php echo $this->render('templates/navigation.html',$this->mime,get_defined_vars()); ?>
	<?php if ($PATH == $ALIASES['home']): ?>
		
	<!-- Main jumbotron for a primary marketing message or call to action -->
	<div class="jumbotron">
		<div class="container">
		<h1>Welcome to McLister Mk III!</h1>
		<p>We are set to be best Minecraft server list on the web.  We have features that no other site has even dreamed about!  Login with Clef and add your server today!</p>
		<p><a class="btn btn-primary btn-lg">Learn more &raquo;</a></p>
		</div>
	</div>
		
	<?php endif; ?>

	<div class="container">
		<?php echo $this->render($main_content,$this->mime,get_defined_vars()); ?>

<div style="clear:both;"></div>
		<hr>

		<footer>
		<p>&copy; StevieSoft 2013 - All Rights Reserved</p>
		</footer>
	</div> <!-- /container -->			
		<script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.10.1.min.js"><\/script>')</script>

		<script src="/js/vendor/bootstrap.min.js"></script>

		<script>
			var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
			(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
			g.src='//www.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));
		</script>
		
	</body>
</html>
