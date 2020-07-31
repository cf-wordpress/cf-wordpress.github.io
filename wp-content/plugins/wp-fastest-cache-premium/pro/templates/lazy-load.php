<div template-id="wpfc-modal-lazyload" style="top: 10.5px; left: 226px; position: absolute; padding: 6px; height: auto; width: 560px; z-index: 10001;display:none;">
	<div style="height: 100%; width: 100%; background: none repeat scroll 0% 0% rgb(0, 0, 0); position: absolute; top: 0px; left: 0px; z-index: -1; opacity: 0.5; border-radius: 8px;">
	</div>
	<div style="z-index: 600; border-radius: 3px;">
		<div style="font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;font-size:12px;background: none repeat scroll 0px 0px rgb(255, 161, 0); z-index: 1000; position: relative; padding: 2px; border-bottom: 1px solid rgb(194, 122, 0); height: 35px; border-radius: 3px 3px 0px 0px;">
			<table width="100%" height="100%">
				<tbody>
					<tr>
						<td valign="middle" style="vertical-align: middle; font-weight: bold; color: rgb(255, 255, 255); text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.5); padding-left: 10px; font-size: 13px; cursor: move;">Lazy Load Settings</td>
						<td width="20" align="center" style="vertical-align: middle;"></td>
						<td width="20" align="center" style="vertical-align: middle; font-family: Arial,Helvetica,sans-serif; color: rgb(170, 170, 170); cursor: default;">
							<div title="Close Window" class="close-wiz"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="window-content-wrapper" style="padding: 8px;">
			<div style="z-index: 1000; height: auto; position: relative; display: inline-block; width: 100%;" class="window-content">
				<div class="wpfc-cdn-pages-container">


					<div wpfc-page="1" class="wiz-cont" style="display:none;">
						<h1>Image Placeholder</h1>		
                        <p>Specify an image to be used as a placeholder while other images finish loading. <a target="_blank" href="http://www.wpfastestcache.com/premium/lazy-load-reduce-http-requests-and-page-load-time/#imageplaceholder"><img src="<?php echo plugins_url("wp-fastest-cache/images/info.png"); ?>" /></a></p>					    <div class="wiz-input-cont">
							<select style="width: 100% !important;max-width: 100% !important;" class="wpFastestCacheLazyLoad_placeholder">
								<option value="default"><?php echo preg_replace("/https?\:\/\//", "", WPFC_WP_CONTENT_URL)."/plugins/wp-fastest-cache-premium/pro/images/blank.gif"; ?></option>
								<option value="wpfc">wpfc.ml/b.gif</option>
								<option value="base64">data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7</option>
			    			</select>
					    </div>
					</div>



					<div wpfc-page="2" class="wiz-cont">
						<h1>Exclude Sources</h1>		
						<p>It is enough to write a keyword such as <strong>home.jpg or iframe or .gif</strong> instead of full url.</p>
						<div class="wiz-input-cont" style="padding:8px 18px;border-radius:0;text-align:center; background: #fff none repeat scroll 0 0;">
							
							<style type="text/css">
								.wiz-input-cont{
									box-shadow:0 2px 6px 0 rgba(0, 0, 0, 0.15) !important;
									
								}
								
								.wpfc-textbox-con{position:absolute;left:0;top:0;-webkit-border-radius:3px;-moz-border-radius:3px;background:#fff;-webkit-box-shadow:0 2px 6px 2px rgba(0,0,0,0.3);box-shadow:0 2px 6px 2px rgba(0,0,0,0.3);-moz-box-shadow:0 2px 6px 2px rgba(0,0,0,0.3);float:left;z-index:444;width:150px;border:1px solid #adadad;}
								.keyword-item-list:after{box-shodow:0 2px 6px 0 rgba(0, 0, 0, 0.15);content:'';clear:both;height:0;visibility:hidden;display:block}
								.keyword-item{width:auto;float:left;line-height:22px;position:relative;background:rgba(0,0,0,0.15);margin:0 5px 0 0;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px}
								.fixed-search input{width:100%;padding:6px 9px;line-height:20px;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;margin:0;border:none;border-bottom:1px solid #ccc;-webkit-box-shadow:0 2px 6px 0 rgba(0,0,0,0.1);box-shadow:0 2px 6px 0 rgba(0,0,0,0.1);-moz-box-shadow:0 2px 6px 0 rgba(0,0,0,0.1);-webkit-border-radius:3px 3px 0 0;-moz-border-radius:3px 3px 0 0;border-radius:3px 3px 0 0;font-weight:bold}.fixed-search input:focus{outline:0}
								.keyword-item{width:auto;float:left;line-height:22px;position:relative;background:rgba(0,0,0,0.15);margin:0 5px 5px 0;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;}
								.wpfc-add-new-keyword, .keyword-item a.keyword-label{
									background-color: #ffa100;
									color:#ffffff;
									text-decoration:none;
									padding:7px 15px;
									display:block;
									text-shadow:none;
									-webkit-transition:all .1s linear;
									-moz-transition:all .1s linear;
									-o-transition:all .1s linear;
									transition:all .1s linear;
									cursor: pointer;
								}
								.keyword-item a.keyword-label:hover{
									padding-left: 4px;
									padding-right: 26px;
								}
								.keyword-item a.keyword-label:hover:after{
									width:16px;
									height:16px;
									background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAAAnNCSVQICFXsRgQAAAAJcEhZcwAAAIAAAACAAc9WmjcAAAAZdEVYdFNvZnR3YXJlAHd3dy5pbmtzY2FwZS5vcmeb7jwaAAAA4klEQVQokWXOMSuFcRQH4EcZZFB3w0BZfIC7mLl3MaGu2cAtqyLJwMQiRuObidFG4hNQJrlFJBlMFgzKMfi/vPe9/ZbT+T11jvAbq2nI8k3aB1MymStPTrTcyWTmi2BDdCQrgprtjjQKIJgw15aZth+Co9KB6zLYL4GLMthqq7/slcFKoVzTa1ClHTT/wKwxt0I4N/wPGqk+NuTNqQ/PLt3oyUEtgQWbQl3NqHVhOgfVBCYdCC3dhnwKSzkYSWDXslDVNG5RqOegksCDPg/ufXv34kxXAkF/SpcBh1492tEbwg+6YscxiN7TegAAAABJRU5ErkJggg==');
									content:"";
									position:absolute;
									top:10px;
									right:4px;
								}

								.wpfc-add-new-keyword{
									cursor:pointer;
									text-decoration:none;
									background-color:#fff !important;
									color:#ccc !important;
									padding:5px 12px !important;
									border:2px dashed #ccc;
									line-height:21px;
								}
								.wpfc-add-new-keyword:before{display:inline-block;content:"+";margin:-1px 4px 0 -6px;}
								.wpfc-add-new-keyword:hover{color:#589b43 !important;border-color:#589b43;}
								
							</style>
							
							<ul class="keyword-item-list">
						        <li class="keyword-item">
						            <a class="wpfc-add-new-keyword">Add Keyword</a>
						            <div class="wpfc-textbox-con" style="display:none;">
						                <div class="fixed-search"><input type="text" placeholder="Add Keyword"></div>
						            </div>
						        </li>
						    </ul>
						</div>

						<?php if(isset($wpFastestCacheLazyLoad_exclude_full_size_img)){ ?>
						<div class="wiz-input-cont">
							<label class="mc-input-label" style="margin-right: 5px;"><input type="checkbox" <?php echo $wpFastestCacheLazyLoad_exclude_full_size_img; ?> id="wpFastestCacheLazyLoad_exclude_full_size_img"></label>
							<label for="wpFastestCacheLazyLoad_exclude_full_size_img">Exclude full size images in posts or pages <a target="_blank" href="https://www.wpfastestcache.com/premium/lazy-load-reduce-http-requests-and-page-load-time/#exclude-full-size-images"><img src="<?php echo plugins_url("wp-fastest-cache/images/info.png"); ?>" /></a></label>
						</div>
						<?php } ?>


					</div>
				</div>
			</div>
		</div>
		<?php include WPFC_MAIN_PATH."templates/buttons.html"; ?>
	</div>
</div>
<script type="text/javascript">
	jQuery("#wpFastestCacheLazyLoad").click(function(e){
		if(jQuery(this).is(':checked')){
			if(jQuery("div[id^='wpfc-modal-lazyload-']").length === 0){
				Wpfc_New_Dialog.dialog("wpfc-modal-lazyload", {
						next: "default",
						back: "default",
						finish: function(e){
							jQuery("#wpFastestCacheLazyLoad_keywords").val(jQuery("div[id^='wpfc-modal-lazyload']").find(".keyword-item-list li.keyword-item a.keyword-label").map(function(){return this.text;}).get().join(","));
							
							if(jQuery("#wpFastestCacheLazyLoad_placeholder").length > 0){
								jQuery("#wpFastestCacheLazyLoad_placeholder").val(Wpfc_New_Dialog.clone.find("div.window-content select.wpFastestCacheLazyLoad_placeholder").val());
							}

							if(jQuery("#wpFastestCacheLazyLoad_exclude_full_size_img").length){
								var exclude_full_size_img = jQuery("div[id^='wpfc-modal-lazyload']").find("input[id^='wpFastestCacheLazyLoad_exclude_full_size_img']").prop("checked");

								if(exclude_full_size_img){
									jQuery("#wpFastestCacheLazyLoad_exclude_full_size_img").val("1");
								}else{
									jQuery("#wpFastestCacheLazyLoad_exclude_full_size_img").remove();
								}
							}

							Wpfc_New_Dialog.remove(e);

						}
					}, function(dialog){
						if(jQuery("#wpFastestCacheLazyLoad_placeholder").length == 0){
							Wpfc_New_Dialog.show_page(2);
							Wpfc_New_Dialog.show_button("finish");
						}else{
							Wpfc_New_Dialog.clone.find("div.window-content select.wpFastestCacheLazyLoad_placeholder").val(jQuery("#wpFastestCacheLazyLoad_placeholder").val());
							Wpfc_New_Dialog.show_page(1);
							Wpfc_New_Dialog.show_button("next");
						}

						Wpfc_New_Dialog.insert_keywords("wpfc-modal-lazyload", jQuery("#wpFastestCacheLazyLoad_keywords").val());
					}
				);
			}
		}
	});
</script>