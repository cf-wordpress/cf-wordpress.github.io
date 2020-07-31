var Wpfc_Lazyload = {
	sources: [],
	osl: 0,
	scroll: false,
	init: function(){
		Wpfc_Lazyload.set_source();

		window.addEventListener('load', function(){
			window.addEventListener("DOMSubtreeModified", function(e){
				Wpfc_Lazyload.osl = Wpfc_Lazyload.sources.length;Wpfc_Lazyload.set_source();
				if(Wpfc_Lazyload.sources.length > Wpfc_Lazyload.osl){Wpfc_Lazyload.load_sources(false);}
			},false);
			
			Wpfcll.load_sources(true);
		});
		window.addEventListener('scroll', function(){Wpfc_Lazyload.scroll=true;Wpfc_Lazyload.load_sources(false);});
		window.addEventListener('resize', function(){Wpfc_Lazyload.scroll=true;Wpfc_Lazyload.load_sources(false);});
		window.addEventListener('click', function(){Wpfc_Lazyload.scroll=true;Wpfc_Lazyload.load_sources(false);});
	},
	c: function(e, pageload){
		var winH = document.documentElement.clientHeight || body.clientHeight;
		var number = 0;

		if(pageload){
			number = 0;
		}else{
			number = (winH > 800) ? 800 : 200;
			number = Wpfc_Lazyload.scroll ? 800 : number;
		}

		var elemRect = e.getBoundingClientRect();
		var top = 0;
		var parentOfE = e.parentNode ? e.parentNode : false;

		if(typeof parentOfE.getBoundingClientRect == "undefined"){
			var parentRect = false;
		}else{
			var parentRect = parentOfE.getBoundingClientRect();
		}

		if(elemRect.x == 0 && elemRect.y == 0){
			for(var i = 0; i < 10; i++){
				if(parentOfE){
					if(parentRect.x == 0 && parentRect.y == 0){
						if(parentOfE.parentNode){
							parentOfE = parentOfE.parentNode;
						}

						if(typeof parentOfE.getBoundingClientRect == "undefined"){
							parentRect = false;
						}else{
							parentRect = parentOfE.getBoundingClientRect();
						}
					}else{
						top = parentRect.top;
						break;
					}
				}
			};
		}else{
			top = elemRect.top;
		}


		if(winH - top + number > 0){
			return true;
		}

		return false;
	},
	r: function(e, pageload){
		var self = this;
		var originalsrc,originalsrcset;

		try{

			originalsrc = e.getAttribute("data-wpfc-original-src");
			originalsrcset = e.getAttribute("data-wpfc-original-srcset");

			if(self.c(e, pageload)){
				if(originalsrc || originalsrcset){
					if(e.tagName == "DIV" || e.tagName == "A"){
						e.style.backgroundImage = "url(" + originalsrc + ")";
						e.removeAttribute("data-wpfc-original-src");
						e.removeAttribute("data-wpfc-original-srcset");
						e.removeAttribute("onload");
						
					}else{
						if(originalsrc){
							e.setAttribute('src', originalsrc);
						}

						if(originalsrcset){
							e.setAttribute('srcset', originalsrcset);
						}

						if(e.getAttribute("alt") && e.getAttribute("alt") == "blank"){
							e.removeAttribute("alt");
						}

						e.removeAttribute("data-wpfc-original-src");
						e.removeAttribute("data-wpfc-original-srcset");
						e.removeAttribute("onload");

						if(e.tagName == "IFRAME"){
							e.onload = function(){
								if(typeof window.jQuery != "undefined"){if(jQuery.fn.fitVids){jQuery(e).parent().fitVids({ customSelector: "iframe[src]"});}}

								var s = e.getAttribute("src").match(/templates\/youtube\.html\#(.+)/);
								var y = "https://www.youtube.com/embed/";
								if(s){
									try{
										var i = e.contentDocument || e.contentWindow;
										if(i.location.href == "about:blank"){
											e.setAttribute('src',y+s[1]);
										}
									}catch(err){
										e.setAttribute('src',y+s[1]);
									}
								}
							}
						}
					}
				}else{
					if(e.tagName == "NOSCRIPT"){
						if(jQuery(e).attr("data-type") == "wpfc"){
							e.removeAttribute("data-type");
							jQuery(e).after(jQuery(e).text());
						}
					}
				}
			}

		}catch(error){
			console.log(error);
			console.log("==>", e);
		}
	},
	set_source: function(){
		var i = Array.prototype.slice.call(document.getElementsByTagName("img"));
		var f = Array.prototype.slice.call(document.getElementsByTagName("iframe"));
		var d = Array.prototype.slice.call(document.getElementsByTagName("div"));
		var a = Array.prototype.slice.call(document.getElementsByTagName("a"));
		var n = Array.prototype.slice.call(document.getElementsByTagName("noscript"));

		this.sources = i.concat(f).concat(d).concat(a).concat(n);
	},
	load_sources: function(pageload){
		var self = this;

		[].forEach.call(self.sources, function(e, index) {
			self.r(e, pageload);
		});
	}
};

document.addEventListener('DOMContentLoaded',function(){
	wpfcinit();
});

function wpfcinit(){
	Wpfc_Lazyload.init();
}