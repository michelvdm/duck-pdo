'use strict';

/* make external svg work in IE */
$(function(){
	if($.ieVer()<9)return;
	var $uses=$('use'),embeds={};

	$uses.each(function(i,o){
		var $o=$(o),url=$o.attr('xlink:href').split('#'),p=url[0];
		$o.attr('xlink:href','#'+url[1]);
		if(!embeds[p])embeds[p]=1,$.get(url[0],null,function(o){$('head').append(o)});
	});
});

/* responsive menu */
$(function(){
	$('.bb-menu-link').click(function(){return !$('aside').toggleClass('open')});
});

