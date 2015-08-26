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

/* litebox */
$(function(){
	var $items=$('.litebox figure');if(!$items.length)return;
	var list=[],$box=$('.mv-litebox'),$img=$box.find('img'),$txt=$box.find('div'),speed=500,active=-1;

	function prepItems(i,o){
		var $o=$(o),link=$o.find('a').attr('href'),im=new Image;
		im.src=link;
		list[i]={link:link,img:im,txt:$o.find('figcaption').html()};
		o.index=i;
	}

	function setImg(i){
		$('.mv-lb-prev').toggle(i>0);
		$('.mv-lb-next').toggle(i<list.length-1);
		var p=list[i],ww=0,s=(active>-1)?speed:0;
		active=i;
		if(s)$img.fadeOut(s),$txt.html('');
		setTimeout(function(){$img.attr('src',p.link).hide().fadeIn(s);$txt.html(p.txt)},s);
	}
	function showBox(){
		var p=this.index*1,s=speed;
		$img.hide();
		$txt.html('');
		$box.fadeIn(s);
		setTimeout(function(){setImg(p)},s);
		return false;
	}
	function doAction(){
		var a=active,s=speed;
		switch(this.className.slice(6)){
		case'prev':setImg(a-1);break;
		case'next':setImg(a+1);break;
		default:
			active=-1;
			$box.fadeOut(s);
			setTimeout(function(){$box.hide()},s);
		}
		return false;
	}
	$items.each(prepItems).click(showBox);
	$box.find('span a').click(doAction);
});
