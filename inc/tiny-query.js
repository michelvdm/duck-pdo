/* tiny query v.0.2 */

'use strict';
!function(){

var A=[],
	D=document,
	aA='Attribute',
	aG='get'+aA,
	aS='set'+aA,
	aR='remove'+aA,
	cA='add',
	cB='block',
	cC='classList',
	cD='documentElement',
	cG='toggle',
	cH='innerHTML',
	cL='length',
	cN='none',
	cP='preventDefault',
	cR='remove',
	cS='scrollTop',
	cT='string',
	cV='value',
	M=Math,
	T=!0,
	F=!1,
	W=window,
	J=W.$=function(a){if(typeof a=='function')A.push(a);else return new O(a,D)},
	AN={
		t:function(){return new Date().getTime()},
		fn:function(a){return(0.5-M.cos(a*M.PI)/2)},
		q:function(a){if(a){clearTimeout(a);a=null}},
		s:function(o,a,tm,f){
			var m=this,s1={},p;m.q(o.T);for(p in a)s1[p]=getCss(o,p);
			extend(o,{A:m.t(),Z:tm||800,s1:s1,s2:a,S:function(){
					var t=m.t()-o.A,q,p;
					if(t>=o.Z){for(p in o.s2)setCss(o,p,o.s2[p]);m.q(o.T);if(f)f()}
					else{q=m.fn(t/o.Z);for(p in o.s2)setCss(o,p,o.s1[p]+(o.s2[p]-o.s1[p])*q)}
				}
			});
			o.T=setInterval(o.S,10);
		}
	},
	S=400
;

function addEvent(a,b,c){a.addEventListener(b,c,F)}
function doClass(a,b,c){var i=a[cL];while(i--)a[i][cC][b](c);return a}
function extend(a,b){for(var o in b)a[o]=b[o];return a}
function getCss(o,a){switch(a){case cS:return o[a];default:return parseInt(getComputedStyle(o)[a])}}
function setCss(o,a,v){switch(a){case cS:o[a]=v;break;case'left':case'top':case'width':case'height':o.style[a]=v+'px';break;default:o.style[a]=v}}
function setCssMulti(a,b){for(var p in b)setCss(a,p,b[p])}
function isSet(a){return!(a===null||typeof a=='undefined')}

function O(a,b){
	var m=this,o=(typeof a==cT)?b.querySelectorAll(a):[a],i=0,l=m[cL]=o[cL];
	for(;i<l;i++)m[i]=o[i];
	if(!isSet(m[0]))m[cL]=0;
}
J.fn=O.prototype={
	addClass:function(a){return doClass(this,cA,a)},
	removeClass:function(a){return doClass(this,cR,a)},
	toggleClass:function(a,b){return doClass(this,isSet(b)?b==T?cA:cR:cG,a)},
	hasClass:function(a){var m=this,i=m[cL];while(i--)if(m[i][cC].contains(a))return T;return F},
	attr:function(a,b){var m=this,i=m[cL],q=isSet(b);if(q)while(i--)m[i][aS](a,b);return q?m:m[0][aG](a)},
	removeAttr:function(a){var m=this,i=[cL];while(i--)m[i][aR](a)},
	html:function(a){var m=this,i=m[cL],q=isSet(a);if(q)while(i--)m[i][cH]=a;return q?m:m[0][cH]},
	val:function(a){var m=this,i=m[cL],q=isSet(a);if(q)while(i--)m[i][cV]=a;return q?m:m[0][cV]},
	eq:function(a){return new O(this[a])},
	on:function(a,b){var m=this,i=m[cL];while(i--)addEvent(m[i],a,function(e){var q=b.apply(this);if(q===F){e=e||W.event;if(e[cP])e[cP]();else e.returnValue=F}return q});return m},
	scroll:function(f){return this.on('scroll',f)},
	click:function(f){return this.on('click',f)},
	hover:function(f,f2){return this.on('mouseover',f).on('mouseout',f2)},
	scrollTop:function(){return this[0][cS]||this[0].scrollY||D[cD][cS]},
	width:function(){var m=this,o=m[0];return (o==W)?M.max(D[cD].clientWidth,W.innerWidth||0):o.offsetWidth},
	animate:function(a,b,c){var m=this,i=m[cL];while(i--)AN.s(m[i],a,b,c);return m},
	css:function(a,b){var m=this,i=m[cL];while(i--)setCssMulti(m[i],a,b);return m},
	slideDown:function(a){
		var m=this,i=m[cL],p,h;
		a=a||400;
		AN.q(m.timer);
		while(i--){
			p=m[i];
			setCssMulti(p,{position:'absolute',visibility:'hidden',display:cB});
			p.style.height='auto';
			h=p.offsetHeight;
			setCssMulti(p,{position:'static',visibility:'visible',height:0,overflow:'hidden'});
			AN.s(p,{height:h},a);
		}
		return m
	},
	slideUp:function(a){var m=this,i=m[cL];a=a||S;while(i--)AN.s(m[i],{height:0},a);m.timer=setTimeout(function(){m.css({display:cN})},a);return m},
	fadeIn:function(a,f){return this.css({opacity:0,display:cB}).animate({opacity:1},a||S,f)},
	fadeOut:function(a,f){return this.animate({opacity:0},a||S,f)},
	show:function(){return this.css({display:cB,opacity:'1'})},
	hide:function(){return this.css({display:cN})},
	toggle:function(a){return this.css({display:a?cB:cN})},
	parent:function(){return new O(this[0].parentNode)},
	find:function(a){return new O(a,this[0])},
	each:function(f){var m=this,i=m[cL];while(i--)f(i,m[i]);return m},
	index:function(){var o=this[0],p=o.parentNode.childNodes,i=0;for(;i<p[cL];i++)if(p[i]==o)return i;return -1},
	append:function(a){var m=this,i=m[cL];while(i--)m[i][cH]+=a;return m}
}
extend(J,{
	ieVer:function(){
		var o=navigator.userAgent,rv;
		if(/Edge\//.test(o))return 12;
		if(!/Trident\/[567]\b/.test(o))return -1;
		var re=new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    	return(!!re.exec(o))?parseFloat(RegExp.$1):11;
	},
	get:function(url,data,f){
		var r=new XMLHttpRequest();
  		r.onreadystatechange=function(){if(r.readyState==4 && r.status==200)f(r.responseText)}
		r.open('GET',url,T);
		r.send(data||F);
	}
});
addEvent(D,'DOMContentLoaded',function(){for(var p in A)A[p]()})}();
