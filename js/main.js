var McListerAPI = {
	showNotif: function(title, message, color) {
		if($('.notification-area').length == 0) $('body').append('<div class="col-lg-3 notification-area"></div>');
		$('<div class="alert alert-'+color+' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>'+title+'</strong> '+message+'</div>').prependTo('.notification-area').hide().slideDown();
	},
	toggleLoading: function() {
		if($('.top-loading-indicator').length == 0) {
			var ind = $('<span class="label label-info top-loading-indicator">Loading...</span>');
			ind.prependTo('body').hide().slideDown(300);
			ind.css('margin-left', -1*(ind.width()/2));
		}
		else $('.top-loading-indicator').slideUp(300, function() {$(this).remove()});
	},
	toggleLoading: function(on) {
		if(on) {
			if($('.top-loading-indicator').length == 0) {
				var ind = $('<span class="label label-info top-loading-indicator">Loading...</span>');
				ind.prependTo('body').hide().slideDown(300);
				ind.css('margin-left', -1*(ind.width()/2));
			}
		} else {
			if($('.top-loading-indicator').length != 0) {
				$('.top-loading-indicator').slideUp(300, function() {$(this).remove()});
			}
		}
	},
	moveTabToMore: function() {
		var last = $('.nav-tabs').children().last();
		var hasDropdown = false;
		if(last.hasClass('dropdown')) {
			last = $('.nav-tabs').children().last().prev();
			hasDropdown = true;
		}
		if(!hasDropdown) {
			$('.nav-tabs').append('<li class="dropdown pull-right"><a class="dropdown-toggle" data-toggle="dropdown" href="#">More <span class="caret"></span></a><ul class="dropdown-menu pull-right"></ul></li>');
		}
		last = last.detach();
		last.prependTo('.nav-tabs ul.dropdown-menu');
	}
};

$.fn.serializeObject = function() {
	var o = {};
	var a = this.serializeArray();
	$.each(a, function() {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};
String.prototype.toHHMMSS = function() {
    var sec_num = parseInt(this, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

	var time = '';
    if (hours > 0) {time += hours + ' hours '}
    if (minutes > 0) {time += minutes + ' minutes '}
    if (seconds > 0) {time += seconds + ' seconds'}
    return time;
}