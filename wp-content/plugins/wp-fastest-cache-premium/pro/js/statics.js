var WpFcStatics = {
	ajax_url: "",
	plugin_dir_url: "",
	current_page: 0,
	total_page: 0,
	per_page: 5,
	statics: {},
	country_code: "",
	init: function(ajax_url, plugin_dir_url, country_code){
		this.country_code = country_code;
		this.ajax_url = ajax_url;
		this.plugin_dir_url = plugin_dir_url;
		this.set_click_event_show_hide_button();
		this.set_click_event_optimize_image_button();
		this.set_click_event_search_button();
		this.set_click_event_paging();
		this.set_click_event_clear_search_text();
		this.set_click_event_filter();
		this.set_click_event_per_page();
		this.set_click_event_buy_image_credit();
	},
	set_click_event_buy_image_credit: function(){
		var self = this;

		jQuery("#buy-image-credit").click(function(){

			var shop = jQuery('<div id="wpfc-shop-modal" style="width: 500px;height: 100px;"></div>');
			jQuery("body").append(shop);

			var windowHeight = (jQuery(window).height() - shop.height())/2 + jQuery(window).scrollTop();
			var windowWidth = (jQuery(window).width() - shop.width())/2;
			shop.css({"top": windowHeight, "left": windowWidth});


			jQuery.ajax({
				type: 'GET',
				url: self.ajax_url,
				data : {"action": "wpfc_image_credit_template_ajax_request"},
				cache: false, 
				success: function(data){
					jQuery("#wpfc-shop-modal").css({"background" : "white"});
					jQuery("#wpfc-shop-modal").html(data);

					windowHeight = 35 + jQuery(window).scrollTop();
					windowWidth = (jQuery(window).width() - shop.width())/2;
					shop.css({"top": windowHeight, "left": windowWidth});

					self.event_on_shop();

				}
			});
		});
	},
	event_on_shop: function(){
		jQuery("#wpfc-product-selection-list li").hover(function(e){
			jQuery("#wpfc-product-selection-list li label").removeClass("hover");
			jQuery(e.target).addClass("hover");
		});

		jQuery("#wpfc-product-selection-list li").click(function(e){
			jQuery("#wpfc-product-selection-list li label").removeClass("checked");
			jQuery(e.currentTarget).find("label").addClass("checked");
			jQuery(e.currentTarget).find("input").attr("checked", true);
		});

		jQuery("#wpfc-cancel-buy-credit").click(function(){
			jQuery("#wpfc-shop-modal").remove();
		});
	},
	set_click_event_per_page: function(){
		var self = this;
		
		jQuery("#wpfc-image-per-page").change(function(e){
			self.update_image_list(0);
		});
	},
	set_click_event_filter: function(){
		var self = this;
		
		jQuery("#wpfc-image-list-filter").change(function(e){
			self.update_image_list(0);
		});
	},
	set_click_event_clear_search_text: function(){
		var self = this;

		jQuery("span.deleteicon span").click(function(e){
			jQuery("#wpfc-image-search-input").val("");
			jQuery(e.target).addClass("cleared");
			self.update_image_list(0);
		});

		jQuery("#wpfc-image-search-input").keyup(function(e){
			if(jQuery(e.target).val().length > 0){
				jQuery("span.deleteicon span").removeClass("cleared");
			}else{
				jQuery("span.deleteicon span").addClass("cleared");

				if(e.keyCode == 8){
					self.update_image_list(0);
				}
			}

			if(e.keyCode == 13){
				self.update_image_list(0);
			}
		});
	},
	set_click_event_paging: function(){
		var self = this;
		jQuery(".wpfc-image-list-next-page, .wpfc-image-list-prev-page, .wpfc-image-list-first-page, .wpfc-image-list-last-page").click(function(e){
			if(jQuery(e.target).hasClass("wpfc-image-list-next-page")){
				self.update_image_list(self.current_page + 1);
			}else if(jQuery(e.target).hasClass("wpfc-image-list-prev-page")){
				self.update_image_list(self.current_page - 1);
			}else if(jQuery(e.target).hasClass("wpfc-image-list-first-page")){
				self.update_image_list(0);
			}else if(jQuery(e.target).hasClass("wpfc-image-list-last-page")){
				self.update_image_list(self.total_page - 1);
			}
		});
	},
	set_click_event_search_button: function(){
		var self = this;
		jQuery("#wpfc-image-search-button").click(function(){
			self.update_image_list(0);
		});
	},
	set_click_event_optimize_image_button: function(){
		var self = this;
		jQuery("#wpfc-optimize-images-button").click(function(){
			//jQuery("[id^='wpfc-optimized-statics-']").addClass("wpfc-loading-statics");
			//jQuery("[id^='wpfc-optimized-statics-']").html("");
			jQuery(this).attr("disabled", true);
			self.optimize_image(self, false, true, false);
		});
	},
	update_image_list: function(page, search){
		var self = this;
		self.per_page = jQuery("#wpfc-image-per-page").val();

		if(page !== 0 && (page < 0 || (page > self.total_page - 1))){
			return;
		}

		jQuery("#revert-loader").show();

		var search = jQuery("#wpfc-image-search-input").val();
		var filter = jQuery("#wpfc-image-list-filter").val();

		jQuery.ajax({
			type: 'GET',
			url: self.ajax_url,
			data : {"action": "wpfc_update_image_list_ajax_request", "page": page, "search" : search, "filter" : filter, 'per_page' : self.per_page},
			dataType : "json",
			cache: false, 
			success: function(data){
				if(typeof data != "undefined" && data){
					self.total_page = Math.ceil(data.result_count/self.per_page);
					self.total_page = self.total_page > 0 ? self.total_page : 1;

					self.current_page = page;

					jQuery(".wpfc-current-page").text(self.current_page + 1);
					jQuery("#the-list").html(data.content);
					jQuery(".wpfc-total-pages").html(self.total_page);
					jQuery("#revert-loader").hide();

					jQuery(".wpfc-image-list-prev-page").removeClass("disabled");
					jQuery(".wpfc-image-list-next-page").removeClass("disabled");
					jQuery(".wpfc-image-list-first-page").removeClass("disabled");
					jQuery(".wpfc-image-list-last-page").removeClass("disabled");

					if((self.current_page + 1) == self.total_page){
						jQuery(".wpfc-image-list-next-page").addClass("disabled");
						jQuery(".wpfc-image-list-last-page").addClass("disabled");
					}

					if(self.current_page === 0){
						jQuery(".wpfc-image-list-prev-page").addClass("disabled");
						jQuery(".wpfc-image-list-first-page").addClass("disabled");
					}

					self.set_click_event_revert_image();
					self.set_click_event_collapse_image_details();

				}else{
					alert("Error: Image List Problem. Please refresh...");
				}
			}
		});
	},
	set_click_event_show_hide_button: function(){
		var self = this;
		jQuery("#show-image-list, #hide-image-list").click(function(e){
			if(e.target.id == "show-image-list"){
				jQuery(e.target).hide();
				jQuery("#hide-image-list").show();
				jQuery("#wpfc-image-list").show();
				jQuery("#wpfc-image-static-panel").hide();
				self.update_image_list(0);
			}else if(e.target.id == "hide-image-list"){
				jQuery(e.target).hide();
				jQuery("#show-image-list").show();
				jQuery("#wpfc-image-list").hide();
				jQuery("#wpfc-image-static-panel").show();
				WpFcStatics.update_statics();
			}
		});

		jQuery("div[data-click-action='errors']").click(function(){
			jQuery("#show-image-list").hide();
			jQuery("#hide-image-list").show();
			jQuery("#wpfc-image-list").show();
			jQuery("#wpfc-image-static-panel").hide();
			jQuery("#wpfc-image-list-filter").val("error_code");
			self.update_image_list(0);
		});
	},
	wpfc_get_server_time: function(servers){
		var html = function(value){
			return '<div style="width: 70px;float:left;">' + 
									'<input value="' + value.url + '" name="wpfc-server-url" type="text" style="display:none;">' +
									'<input value="' + value.key + '" name="wpfc-server-location" type="radio" style="vertical-align: top; padding-top: 0px; margin-top: 0px;"><img>' + 
									'<div style="color:black;float: right; width: 62px; text-align: center;font-weight:bold;">' + value.location + '</div>' + 
									'<div class="server-time" style="float: right; width: 62px; text-align: center;font-weight:bold;">' + value.time.time + '</div>' + 
								'</div>';
		};

		var ajaxTime= new Date().getTime();
		var div;

		jQuery.ajax({
			type: 'GET', 
			url: WpFcCacheStatics.admin_ajax_url,
			data : {"action" : "get_server_time_ajax_request", "servers" : servers},
			dataType : "json",
			cache: false,
			error: function(x, t, m) {
				//alert(t);
				jQuery(servers).each(function(key, value){
					jQuery("#wpfc-server-list").prepend(html(value));
				});
			},
			success: function(data){
				jQuery(data).each(function(key, value){
					if(value.time.success){

						if(jQuery("#wpfc-server-list > div").length == 0){
							jQuery("#wpfc-server-list").prepend(html(value));
						}else{
							jQuery("#wpfc-server-list > div").each(function(i, e){
								if(value.time.time < parseFloat(jQuery(e).find("div.server-time").text())){
									jQuery(e).before(html(value));
									return false;
								}else{
									if(jQuery(e).next().length == 0){
										jQuery(e).after(html(value));
										return false;
									}else{
										if(value.time.time < parseFloat(jQuery(e).next().find("div.server-time").text())){
											jQuery(e).after(html(value));
											return false;
										}
									}
								}
							});
						}

					}else{
						if(key === 0){
							alert("Neither fsockopen nor curl exist in the server");
						}
					}
				});
			},
			timeout: 10000
		});
	},
	wpfc_get_servers: function(){
		var self = this;
		var servers = {


					   "naw": [
							   {"key": "la", "time":0,"flag":"us","location":"Los Ang","color":"red", "url":"https://api.wpfcla.tk"},
					   		   {"key": "az", "time":0,"flag":"us","location":"Arizona","color":"red", "url":"https://api.wpfcarizona.tk"},
					   		   {"key": "fremont", "time":0,"flag":"us","location":"Fremont","color":"red", "url":"https://fremont.wpfastestcache.com"},
					   		 ],
					   	"nae": [
							   {"key": "ny", "time":0,"flag":"us","location":"New York","color":"red", "url":"https://api.wpfastestcache.gq"},
					   		   {"key": "chic", "time":0,"flag":"us","location":"Chicago","color":"red", "url":"https://chicago.wpfastestcache.com"},
					   		   {"key": "dal", "time":0,"flag":"us","location":"Dallas","color":"red", "url":"https://dallas.wpfastestcache.com"},
							   {"key": "stlo", "time":0,"flag":"us","location":"St. Louis","color":"red", "url":"https://api.wpfastestcache.in"},
					   		 ],



					   "euw": [
					   		   {"key": "france", "time":0,"flag":"de","location":"France","color":"red", "url":"https://fr.wpfastestcache.com"},
					   		   {"key": "fran", "time":0,"flag":"de","location":"Frankfurt","color":"red", "url":"https://frankfurt.wpfastestcache.com"},
							   {"key": "uk", "time":0,"flag":"gb","location":"UK","color":"red", "url":"https://api.wpfastestcache.ml"},
							   {"key": "nl", "time":0,"flag":"nl","location":"Holland","color":"red", "url":"https://api.wpfastestcache.cf"},
					   		 ],
					   	"eue": [
							   {"key": "de", "time":0,"flag":"de","location":"Germany","color":"red", "url":"https://api.wpfastestcache.ga"},
							   {"key": "se", "time":0,"flag":"de","location":"Sweden","color":"red", "url":"https://sweden.wpfastestcache.com"},
							   {"key": "bg", "time":0,"flag":"bg","location":"Bulgaria","color":"red", "url":"https://api.wpfcbg.tk"},
							   {"key": "it", "time":0,"flag":"it","location":"Italy","color":"red", "url":"https://it.wpfastestcache.com"},
					   		 ],



					   "as": [
							   {"key": "au", "time":0,"flag":"sg","location":"Australia","color":"red", "url":"https://au.wpfastestcache.com"},
							   {"key": "hk", "time":0,"flag":"hk","location":"Hong Kong","color":"red", "url":"https://api.wpfastestcache.tk"},
							   {"key": "jp", "time":0,"flag":"jp","location":"Japan","color":"red", "url":"https://jp.wpfastestcache.com"},
							   {"key": "sg", "time":0,"flag":"sg","location":"Singapour","color":"red", "url":"https://api.wpfcsg.tk"},
							   {"key": "in", "time":0,"flag":"in","location":"India","color":"red", "url":"https://mumbai.wpfastestcache.com"}
							 ]
					};

		//http://www.countryareacode.net/en/list-of-countries-according-to-continent/
		var america = "AI, AR, AW, BS, BB, BZ, BM, BO, BR, VG, CA, KY, CL, CO, CR, CU, CW, DM, DO, EC, SV, FK, GL, GP, GT, GY, HT, HN, JM, MX, MS, NI, PA, PY, PE, PR, BL, KN, LC, MF, PM, VC, SR, TT, US, UY, VE";
		var oceania = "AS, AU, CK, TL, FJ, PF, GU, KI, MH, FM, NR, NC, NZ, NU, NF, MP, PW, PG, PN, WS, SB, TK, TV, VU";
		var asia = "AF, AM, AZ, BH, BD, BT, BN, KH, CN, GE, HK, IN, ID, IR, IQ, JP, JO, KW, KG, LA, LB, MO, MY, MV, MN, MM, NP, KP, OM, PK, PH, QA, SA, SG, KR, LK, SY, TW, TJ, TH, TM, AE, UZ, VN, YE";
		var europe = "AL, AD, AT, BY, BE, BA, BG, HR, CY, CZ, DK, EE, FO, FI, FR, DE, GI, GR, HU, IL, IS, IE, IM, IT, XK, KZ, LV, LI, LT, LU, MK, MT, MD, MC, ME, NL, NO, PL, PT, RO, RU, SM, RS, SK, SI, ES, SE, TR, CH, UA, GB, VA";
		var time_out = 4000;

		jQuery("#revert-loader-toolbar").show();
		jQuery("#wpfc-server-list").html("");


		if(self.country_code){
			if(america.search(self.country_code) > -1){
				self.wpfc_get_server_time(servers.naw);
				self.wpfc_get_server_time(servers.nae);
			}else if(europe.search(self.country_code) > -1){
				self.wpfc_get_server_time(servers.euw);
				self.wpfc_get_server_time(servers.eue);
			}else if(oceania.search(self.country_code) > -1){
				self.wpfc_get_server_time(servers.as);
			}else if(asia.search(self.country_code) > -1){
				self.wpfc_get_server_time(servers.as);

				time_out = 5000;
			}else{
				self.wpfc_get_server_time(servers.euw);
				self.wpfc_get_server_time(servers.eue);
			}
		}else{
			self.wpfc_get_server_time(servers.naw);
			self.wpfc_get_server_time(servers.nae);
			self.wpfc_get_server_time(servers.euw);
			self.wpfc_get_server_time(servers.eue);
			self.wpfc_get_server_time(servers.as);
		}



		setTimeout(function(){
			jQuery("#revert-loader-toolbar").hide();
			jQuery("#wpfc-optimize-images-button").attr("disabled", false);
		}, time_out);
		//self.wpfc_get_server_time(server_key, value);
	},
	optimize_image: function(self, id, recursive, last){
		function set_loading_bar(percentage){
			jQuery("#wpfc-opt-image-loading div").width(percentage + "%");
		};

		// if(typeof id == "undefined"){
		// 	jQuery("[id^='wpfc-optimized-statics-']").addClass("wpfc-loading-statics");
		// 	jQuery("[id^='wpfc-optimized-statics-']").html("");
		// }


		if(jQuery("#wpfc-server-list input[name='wpfc-server-url']").length > 0){
			var server_url = jQuery("#wpfc-server-list input[name='wpfc-server-url']").val();
		}else{
			WpFcStatics.wpfc_get_servers();
			return 0;
		}
		
		if(!id){
			id = "";
		}

		jQuery.ajax({
			type: 'GET', 
			url: self.ajax_url,
			dataType : "json",
			data : {"action": "wpfc_optimize_image_ajax_request", "id" : id, "last" : last, "server_url": server_url},
			cache: false,
			timeout: 20000,
			error: function(x, t, m) {
				console.log("first" + JSON.stringify(x));
				console.log(x.status + "---" + x.statusText);

				console.log("second" + JSON.stringify(t));
				console.log("third" + JSON.stringify(m));
				
				var reload = false;

				if(t === "timeout" || t == "Internal Server Error") {
					reload = true;
				}else if(m == "Not Found" || m == "Internal Server Errors" || m == "Forbidden"){
					reload = true;
				}else if(x.status == 404 || x.status == 503){
					reload = true;
				}else if(x.status == 200 && (x.responseText.match(/Error\sestablishing\sa\sdatabase\sconnection/i))){
					reload = true;
				}else{
					alert(t);
				}


				if(reload){
					if(recursive){
						setTimeout(function(){
							self.modify_statics_html();
							self.optimize_image(self, id, true, false);
						}, 10000);
					}
				}
			},
			success: function(data){
				if(data && data.success == "success"){
					if(data.message != "finish"){

						console.log(data);
						//to check first call or not
						set_loading_bar(data.percentage);

						if(data.percentage == 100){
							self.update_statics(function(){
								set_loading_bar(0);

								if(recursive){
									self.optimize_image(self, false, true, false);
								}
							});
						}else{
							if(recursive){
								self.optimize_image(self, data.id, true, false);

								if(id == false){
									if((data.percentage > 0) && (data.percentage < 30)){

										for (var last_i = 1; last_i <= (100/(data.percentage*2)); last_i++){
											self.optimize_image(self, data.id, false, "last-" + last_i);
										}

									}
								}
							}
						}
					}else{
						jQuery("#wpfc-optimize-images-button").attr("disabled", false);
						self.update_statics();
					}
				}else{
					self.update_statics();
					if(data && typeof data.message != "undefined" && data.message){

						if(data.message.match(/Error\sCode\:\s101/) || data.message.match(/^500$/)){
							if(jQuery("#wpfc-opt-image-loading div").width() > 0){
								if(recursive){
									setTimeout(function(){
										self.optimize_image(self, id, true, false);
									}, 900);
								}
							}
						}else{
							if(data.message.match(/no\sdecode\sdelegate\sfor\sthis\simage\sformat/i)){
								// toDO
							}

							if(data.message.match(/text\schunk\(s\)\sfound\safter\sPNG\sIDAT\s\(fixed\)/i) || 
								data.message.match(/cURL\serror\s28\:\sResolving\stimed\sout\safter/i)){
								//[minor] Text chunk(s) found after PNG IDAT (fixed)
								//cURL error 28: Resolving timed out after 10000 milliseconds

								if(recursive){
									setTimeout(function(){
										self.optimize_image(self, id, true, false);
									}, 900);
								}
							}else{
								alert(data.message);
								jQuery("#wpfc-optimize-images-button").attr("disabled", false);
							}
						}

					}else{
						alert("Please try later...");
					}
				}
			}
		});
	},
	update_statics: function(callback){
		var self = this;
		var credit = false;
		var str_callback = false;

		jQuery("[id^='wpfc-optimized-statics-']").addClass("wpfc-loading-statics");
		jQuery("[id^='wpfc-optimized-statics-']").html("");

		if(callback){
			str_callback = callback.toString();

			if(str_callback.match(/set_loading_bar\(0\)/)){
				credit = true;
			}
		}

		jQuery.ajax({
			type: 'GET', 
			url: self.ajax_url,
			dataType : "json",
			data : {"action": "wpfc_statics_ajax_request", "credit" : credit},
			cache: false, 
			success: function(data){
				if(callback){ callback(); }
				self.statics = data;
				self.modify_statics_html();
			}
		});
	},
	modify_statics_html: function(){
		var self = this;
		jQuery.each(this.statics, function(e, i){
			var el = jQuery("#wpfc-optimized-statics-" + e);
			if(el.length === 1){
				if(e == "percent"){
					var percent = i*3.6;

					if(percent > 180){
						jQuery("#wpfc-pie-chart-big-container-first").show();
						jQuery("#wpfc-pie-chart-big-container-second-right").show();
						jQuery('#wpfc-pie-chart-big-container-second-left').animate({  borderSpacing: (percent - 180) }, {
						    step: function(now,fx) {
						      jQuery(this).css('-webkit-transform','rotate('+now+'deg)'); 
						      jQuery(this).css('-moz-transform','rotate('+now+'deg)');
						      jQuery(this).css('transform','rotate('+now+'deg)');
						    },
						    duration:'slow'
						},'linear');

					}else{
						jQuery("#wpfc-pie-chart-big-container-first").hide();
						jQuery("#wpfc-pie-chart-big-container-second-right").hide();

						jQuery('#wpfc-pie-chart-little').animate({  borderSpacing: percent }, {
						    step: function(now,fx) {
						      jQuery(this).css('-webkit-transform','rotate('+now+'deg)'); 
						      jQuery(this).css('-moz-transform','rotate('+now+'deg)');
						      jQuery(this).css('transform','rotate('+now+'deg)');
						    },
						    duration:'slow'
						},'linear');
					}
				}

				el.removeClass("wpfc-loading-statics");
				el.html(i);
			}
		});
	},
	set_click_event_collapse_image_details: function(){
		jQuery("td.open-image-details").click(function(e){
			jQuery("tr[post-id='" + jQuery(e.target).closest("tr[post-id]").attr("post-id") + "'][post-type='detail']").toggle();
		});
	},
	set_click_event_revert_image: function(){
		var self = this;
		jQuery("div.revert").click(function(e){
			jQuery("#revert-loader").show();

			var id = jQuery(e.target).find("input")[0].value;

			jQuery.ajax({
				type: 'GET', 
				url: self.ajax_url,
				dataType : "json",
				data : {"action": "wpfc_revert_image_ajax_request", "id" : id},
				cache: false, 
				success: function(data){
					try{
						if(data.success == "true"){
							self.update_statics(function(){
								jQuery("tr[post-id='" + id + "']").hide(100, function(){
									if(jQuery("#the-list tr:visible").length === 0){
										self.update_image_list(0);
									}else{
										jQuery("#revert-loader").hide();
									}
								});
							});
						}else if(data.success == "false"){
							jQuery("#revert-loader").hide();
							if(typeof data.message != "undefined" && data.message){
								alert(data.message);
							}else{
								alert("Revert Image: " + 'data.success == "false"');
							}
						}

					}catch(err){
						alert("Revert Image: " + err.message);
					}
				}
			});

		});
	}
};