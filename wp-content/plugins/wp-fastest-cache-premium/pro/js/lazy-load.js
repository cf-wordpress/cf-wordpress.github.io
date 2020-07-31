var Wpfc_Lazyload = {
	sources: [],
	loaded_index:[],
	init: function(){
		this.set_source(document.getElementsByTagName("img"));
		this.set_source(document.getElementsByTagName("iframe"));

		this.load_sources(true);
		window.addEventListener('scroll', function(){Wpfc_Lazyload.load_sources(false);});
		window.addEventListener('resize', function(){Wpfc_Lazyload.load_sources(false);});
	},
	set_source: function(arr){
		if(arr.length > 0){
			var self = this;
			[].forEach.call(arr, function(e, index) {
				self.sources.push(e);
			});
		}
	},
	load_sources: function(pageload){
		var self = this;
		var originalsrc,originalsrcset;
		var winH = document.documentElement.clientHeight || body.clientHeight;
		var number = pageload ? 0 : 300;

		[].forEach.call(self.sources, function(e, index) {
			try{
				var elemRect = e.getBoundingClientRect();

				if(winH - elemRect.top + number > 0){
					if(self.loaded_index.indexOf(index) == -1){
						originalsrc = e.getAttribute("data-wpfc-original-src");
						originalsrcset = e.getAttribute("data-wpfc-original-srcset");

						if(originalsrc || originalsrcset){
							if(originalsrc){
								e.setAttribute('src', originalsrc);
							}

							if(originalsrcset){
								e.setAttribute('srcset', originalsrcset);
							}

							self.loaded_index.push(index);
						}
					}
				}

			}catch(error){
				console.log(error);
				console.log("==>", e);
			}
		});
	}
};
Wpfc_Lazyload.init();