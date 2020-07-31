<?php

	class WpFastestCacheLazyLoad{
		private $exclude = array();
		private $host = "";
		private $placeholder = "";

		public function __construct(){
			$url = parse_url(site_url());
			$this->host = $url["host"];

			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords){
				$this->exclude = explode(",", $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_keywords);
			}

			$this->set_placeholder();
		}

		public function set_placeholder(){
			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder){
				switch ($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad_placeholder) {
					case "default":
						if(isset($GLOBALS["wp_fastest_cache"]->content_url) && $GLOBALS["wp_fastest_cache"]->content_url){
							$this->placeholder = $GLOBALS["wp_fastest_cache"]->content_url."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}else if(defined('WPFC_WP_CONTENT_URL')){
							$this->placeholder = preg_replace("/https?\:\/\//", "//", WPFC_WP_CONTENT_URL)."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}else{
							$this->placeholder = content_url()."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
						}
						break;
					case "wpfc":
						$this->placeholder = "//wpfc.ml/b.gif";
						break;
					case "base64":
						$this->placeholder = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
						break;
				}
			}else{
				$this->placeholder = preg_replace("/https?\:\/\//", "//", WPFC_WP_CONTENT_URL)."/plugins/wp-fastest-cache-premium/pro/images/blank.gif";
			}
		}

		public function is_mobile(){
			$is_mobile = false;

			foreach ($GLOBALS['wp_fastest_cache']->get_mobile_browsers() as $value) {
				if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
					$is_mobile = true;
				}
			}

			foreach ($GLOBALS['wp_fastest_cache']->get_operating_systems() as $key => $value) {
				if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
					$is_mobile = true;
				}
			}

			return $is_mobile;
		}

		public function mark_content_images($content){
			if($this->is_mobile()){
				return $content;
			}

			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $img ) {
					if($this->is_thumbnail($img)){
						continue;
					}

					if($this->is_third_party($img)){
						continue;
					}

					if(!$this->is_full($img)){
						continue;
					}

					$tmp_img = preg_replace("/<img\s/", "<img wpfc-lazyload-disable=\"true\" ", $img);

					$content = str_replace($img, $tmp_img, $content );
				}
			}

			return $content;
		}

		public function mark_attachment_page_images($attr, $attachment) {
			if($this->is_mobile()){
				return $attr;
			}
			
			if(isset($attr['src'])){
				if($this->is_thumbnail($attr['src'])){
					return $attr;
				}

				if($this->is_third_party($attr['src'])){
					return $attr;
				}

				if(!$this->is_full('<img src="'.$attr["src"].'" class="'.$attr["class"].'">')){
					return $attr;
				}
			}

			if(!$attachment){
				return $attr;
			}

			$attr['wpfc-lazyload-disable'] = "true";
			
			return $attr;
		}

		public function is_thumbnail($src){
			// < 299x299
			if(preg_match("/\-[12]\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 299x99
			if(preg_match("/\-[12]\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x299
			if(preg_match("/\-\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x99
			if(preg_match("/\-\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			return false;
		}

		public function is_third_party($src){
			if(preg_match("/".preg_quote($this->host, "/")."/i", $src)){
				return false;
			}

			return true;
		}

		public function is_full($img){
			// to check homepage. sometimes is_home() does not work
			if(isset($_SERVER["REQUEST_URI"]) && strlen($_SERVER["REQUEST_URI"]) < 2){
				return false;
			}
			
			if(is_home() || is_archive()){
				return false;
			}

			if(preg_match("/-\d+x\d+\.(jpg|jpeg|png)/i", $img)){
				if(preg_match("/\sclass\=[\"\'][^\"\']*size-medium[^\"\']*[\"\']/", $img)){
					return false;
				}
			}

			return true;
		}

		public function is_exclude($source){
			/*
			to disable lazy load for rav-slider images
			<img data-bgposition="center center" data-bgparallax="8" data-bgfit="cover" data-bgrepeat="no-repeat"class="rev-slidebg" data-no-retina>
			<img width="1920" height="600" data-parallax="8" class="rev-slidebg" data-no-retina>
			*/
			if(preg_match("/class\=\"rev-slidebg\"/i", $source) && preg_match("/data-(bg)*parallax\=/i", $source)){
				return true;
			}

			/*
			to exclude img tag which exists in json
			var xxx = {"content":"<a href=\"https:\/\/www.abc.com\"><img src='https:\/\/www.abc.com\/img.gif' \/><\/a>"}
			*/
			if(preg_match("/\\\\\//", $source)){
				return true;
			}

			/*
			<img src="my-image.jpg" data-no-lazy="1" alt="" width="100" width="100" />
			<img src="my-image.jpg" data-skip-lazy="1" alt="" width="100" width="100" />
			*/
			if(preg_match("/data-(no|skip)-lazy\s*\=\s*[\"\']\s*1\s*[\"\']/i", $source)){
				return true;
			}

			//Slider Revolution
			//<img src="dummy.png" data-lazyload="transparent.png" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
			if(preg_match("/\sdata-lazyload\=[\"\']/i", $source)){
				return true;
			}

			//<img src="dummy.png" data-lazy-src="transparent.png">
			//<img src="dummy.png" data-gg-lazy-src="transparent.png">
			if(preg_match("/\sdata-([a-z-]*)lazy-src\=[\"\']/i", $source)){
				return true;
			}

			//<div style="background-image:url(&#039;https://www.g.com/wp-content/plugins/bold-page-builder/img/blank.gif&#039;);background-position:top;background-size:cover;" data-background_image_src="https://www.g.com/wp-content/1.jpg">
			if(preg_match("/\sdata-background_image_src\=[\"\']/i", $source)){
				return true;
			}

			/*
			Smash Balloon Social Photo Feed
			<img src="https://site.com/wp-content/plugins/instagram-feed/img/placeholder.png"
			*/
			if(preg_match("/instagram-feed\/img\/placeholder\.png/i", $source)){
				return true;
			}

			// don't to the replacement if the image is a data-uri
			if(preg_match("/src\=[\'\"]data\:image/i", $source)){
				return true;
			}
			

			foreach ((array)$this->exclude as $key => $value) {
				if(preg_match("/".preg_quote($value, "/")."/i", $source)){
					return true;
				}
			}

			return false;
		}

		public function background_to_lazyload($content, $inline_scripts){
			if(isset($GLOBALS["wp_fastest_cache"]->noscript)){
				$inline_scripts = $inline_scripts.$GLOBALS["wp_fastest_cache"]->noscript;
			}

			preg_match_all('/<(div|a)\s[^\>]+style\s*\=\s*[\"\'][^\"\']*(background\-image\s*\:\s*url\s*\(([^\)\(]+)\)\;?)[^\"\']*[\"\'][^\>]*>/i', $content, $matches, PREG_SET_ORDER);

			if(count($matches) > 0){
				/*
				[0] = full
				[1] = tag
				[2] = backgound image
				[3] = url
				*/
				foreach ($matches as $key => $div){
					// don't to the replacement if the image appear in js
					if(preg_match("/".preg_quote($div[0], "/")."/i", $inline_scripts)){
						continue;
					}

					if($this->is_exclude($div[0])){
						continue;
					}

					$tmp = $div[0];
					//to remove backgound attribute
					$tmp = str_replace($div[2], "", $tmp);
					//to add lazy load attribute
					$div[3] = preg_replace("/[\"\']/", "", $div[3]);
					$tmp = preg_replace("/<([a-z]{1,3})\s/i", "<$1 data-wpfc-original-src='".$div[3]."' ", $tmp);

					$content = str_replace($div[0], $tmp, $content);
				}
			}

			return $content;
		}

		public function images_to_lazyload($content, $inline_scripts){
			if(isset($GLOBALS["wp_fastest_cache"]->noscript)){
				$inline_scripts = $inline_scripts.$GLOBALS["wp_fastest_cache"]->noscript;
			}

			if($this->is_mobile()){
				$offset = 0;
			}else{
				$offset = 3;
			}

			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $key => $img) {
					$tmp_img = false;

					if(preg_match("/onload=[\"\']/i", $img)){
						continue;
					}

					if(preg_match("/src\s*\=\s*[\'\"]\s*[\'\"]/i", $img)){
						continue;
					}

					// don't to the replacement if the image appear in js
					if(!preg_match("/".preg_quote($img, "/")."/i", $inline_scripts)){

						// don't to the replacement if quote of src does not exist
						if(!preg_match("/\ssrc\s*\=[\"\']/i", $img) && preg_match("/<img/i", $img)){
							continue;
						}
							
						if($this->is_exclude($img)){
							$tmp_img = preg_replace("/\swpfc-lazyload-disable\=[\"\']true[\"\']\s*/", " ", $img);
						}else if(preg_match("/wpfc-lazyload-disable/", $img)){
							$tmp_img = preg_replace("/\swpfc-lazyload-disable\=[\"\']true[\"\']\s*/", " ", $img);
						}else{
							if($key < $offset){
								$tmp_img = $img;
							}else{
								if(preg_match("/\ssrc\s*\=[\"\'][^\"\']+[\"\']/i", $img)){
									if(preg_match("/mc\.yandex\.ru\/watch/i", $img)){
										$tmp_img = $img;
									}else{
										$tmp_img = $img;
										$tmp_img = preg_replace("/\ssrc\s*\=/i", " data-wpfc-original-src=", $tmp_img);
										$tmp_img = preg_replace("/\ssrcset\s*\=/i", " data-wpfc-original-srcset=", $tmp_img);
										$tmp_img = preg_replace("/<img\s/i", "<img onload=\"Wpfcll.r(this,true);\" src=\"".$this->placeholder."$2\" ", $tmp_img);

										// to add alt attribute for seo
										$tmp_img = preg_replace("/\salt\s*\=\s*[\"|\']\s*[\"|\']/", " alt=\"blank\"", $tmp_img);
										if(!preg_match("/\salt\s*\=\s*/i", $tmp_img)){
											$tmp_img = preg_replace("/<img\s+/i", "<img alt=\"blank\" ", $tmp_img);
										}

									}
								}
							}
						}

						if($tmp_img){
							$content = str_replace($img, $tmp_img, $content);
						}
					}else{
						$tmp_img = preg_replace("/\swpfc-lazyload-disable\=[\"\']true[\"\']\s*/", " ", $img);
						$content = str_replace($img, $tmp_img, $content);
					}
				}
			}

			return $content;
		}

		public function iframe_to_lazyload($content, $inline_scripts) {
			preg_match_all('/<iframe[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $iframe ) {
					if($this->is_exclude($iframe)){
						continue;
					}
					
					// don't to the replacement if the frame appear in js
					if(!preg_match("/".preg_quote($iframe, "/")."/i", $inline_scripts)){
						if(!preg_match("/onload=[\"\']/i", $iframe)){

							if(preg_match("/(youtube|youtube-nocookie)\.com\/embed/i", $iframe) && !preg_match("/videoseries\?list/i", $iframe)){
								// to exclude /videoseries?list= because of problem with getting thumbnail
								$tmp_iframe = preg_replace("/\ssrc\=[\"\'](https?\:)?\/\/(www\.)?(youtube|youtube-nocookie)\.com\/embed\/([^\"\']+)[\"\']/i", " onload=\"Wpfcll.r(this,true);\" data-wpfc-original-src=\"".WPFC_WP_CONTENT_URL."/plugins/wp-fastest-cache-premium/pro/templates/youtube.html#$4\"", $iframe);
							}else{
								$tmp_iframe = preg_replace("/\ssrc\=/i", " onload=\"Wpfcll.r(this,true);\" data-wpfc-original-src=", $iframe);
							}

							$content = str_replace($iframe, $tmp_iframe, $content);
						}
					}
				}
			}

			return $content;
		}

		public static function get_js_source_new(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load-new.js")."</script>\n";
			
			$js = preg_replace("/\/\*[^\n]+\*\//", "", $js); //to remove comments
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\+|\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "ot", $js);
			$js = preg_replace("/originalsrc/", "oc", $js);
			$js = preg_replace("/load_sources/", "ls", $js);
			$js = preg_replace("/set_source/", "ss", $js);
			$js = preg_replace("/sources/", "s", $js);
			$js = preg_replace("/winH/", "w", $js);
			$js = preg_replace("/number/", "n", $js);
			$js = preg_replace("/elemRect/", "er", $js);
			$js = preg_replace("/parentRect/", "pr", $js);
			$js = preg_replace("/parentOfE/", "p", $js);
			$js = preg_replace("/top(\=|\+)/", "t$1", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}

		public static function get_js_source(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load.js")."</script>\n";
			
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "osrcs", $js);
			$js = preg_replace("/originalsrc/", "osrc", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}
	}
?>