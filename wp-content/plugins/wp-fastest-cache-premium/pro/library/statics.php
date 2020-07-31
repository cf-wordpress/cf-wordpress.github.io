<?php
	class WpFastestCacheStatics{
		private $extension = false;
		private $size = false;

		public function __construct($extension = false, $size = false){
			$this->extension = $extension;
			$this->size = $size;
		}

		public function update_db(){
			$option_name = "WpFastestCache".strtoupper($this->extension);
			$option_name_for_size = $option_name."SIZE";

			if($current = get_option($option_name)){
				$current = $current + 1;
				update_option($option_name, $current);
			}else{
				add_option($option_name, 1, null, "yes");
			}

			if($current_size = get_option($option_name_for_size)){
				$current_size = $current_size + $this->size;
				update_option($option_name_for_size, $current_size);
			}else{
				add_option($option_name_for_size, $this->size, null, "yes");
			}
		}
		public function statics(){
			include_once(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/templates/cache-statics.html");
		}

		public function get(){
			$arr = array("desktop" => array("size" => get_option("WpFastestCacheHTMLSIZE")/1000, "file" => get_option("WpFastestCacheHTML")),
						 "mobile" => array("size" => get_option("WpFastestCacheMOBILESIZE")/1000, "file" => get_option("WpFastestCacheMOBILE")),
						 "js" => array("size" => get_option("WpFastestCacheJSSIZE")/1000, "file" => get_option("WpFastestCacheJS")),
						 "css" => array("size" => get_option("WpFastestCacheCSSSIZE")/1000, "file" => get_option("WpFastestCacheCSS"))
						 );
			return $arr;
		}
	}
?>