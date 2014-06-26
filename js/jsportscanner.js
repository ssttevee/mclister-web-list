pingServer = function (target, port, timeout, callback) {
	var timeout = (timeout == null)?100:timeout;
	var img = new Image();
	
	img.onerror = function (e) {
		if (!img) return;
		img = undefined;
		callback(true);
		console.log(e)
	};
	
	img.onload = img.onerror;
	img.src = 'http://' + target + ':' + port;
	
	setTimeout(function () {
		if (!img) return;
		img = undefined;
		callback(false);
	}, timeout);
};