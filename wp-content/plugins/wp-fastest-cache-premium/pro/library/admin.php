<?php
	class WPFC_PREMIUM_ADMIN{
		public function __construct(){
			add_action( 'wp_ajax_wpfc_update_premium', array($this, 'wpfc_update_premium_callback'));
		}

		public function get_premium_version(){
			$response = wp_remote_get("http://api.wpfastestcache.net/user/".str_replace("www.", "", $_SERVER["HTTP_HOST"])."/type/".get_option("WpFc_api_key"), array('timeout' => 5));
	
			if (!$response || is_wp_error($response)) {
				return false;
			}else{
				if(wp_remote_retrieve_response_code($response) == 200){
					if(wp_remote_retrieve_body($response) == "free"){
						deactivate_plugins("wp-fastest-cache-premium/wpFastestCachePremium.php");
						$GLOBALS['wp_fastest_cache']->rm_folder_recursively(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium");
						$GLOBALS['wp_fastest_cache']->rm_folder_recursively(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium");
					}
				}
			}

			$wpfc_premium_version = "";
			if(file_exists(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/wpFastestCachePremium.php")){
				if($data = @file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/wpFastestCachePremium.php")){
					preg_match("/Version:\s*(.+)/", $data, $out);
					if(isset($out[1]) && $out[1]){
						$wpfc_premium_version = trim($out[1]);
					}
				}
			}
			return $wpfc_premium_version;
		}



		public function download_premium(){
			$res = array();
			$response = wp_remote_get("http://api.wpfastestcache.net/premium/newdownload/".str_replace(array("http://", "www."), "", $_SERVER["HTTP_HOST"])."/".get_option("WpFc_api_key"), array('timeout' => 10 ) );

			if ( !$response || is_wp_error( $response ) ) {
				$res = array("success" => false, "error_message" => $response->get_error_message());
			}else{
				if(wp_remote_retrieve_response_code($response) == 200){

					if($wpfc_zip_data = wp_remote_retrieve_body( $response )){
						$res = array("success" => true, "content" => $wpfc_zip_data);
					}else{
						$res = array("success" => false, "error_message" => ".zip file is empty");
					}

				}else{
					$res = array("success" => false, "error_message" => "Error: Try later...");
				}
			}
			return $res;
		}




		public function wpfc_update_premium_callback(){
			if(current_user_can('manage_options')){
				$content = $this->download_premium();

				if($content["success"]){
					$wpfc_zip_data = $content["content"];

					$wpfc_zip_dest_path = WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium.zip";

					if(@file_put_contents($wpfc_zip_dest_path, $wpfc_zip_data)){

						include_once ABSPATH."wp-admin/includes/file.php";
						include_once ABSPATH."wp-admin/includes/plugin.php";

						if(function_exists("unzip_file")){
							$GLOBALS['wp_fastest_cache']->rm_folder_recursively(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium");
							
							if(!function_exists('gzopen')){
								$res = array("success" => false, "error_message" => "Missing zlib extension"); 
							}else{
								WP_Filesystem();
								$unzipfile = unzip_file($wpfc_zip_dest_path, WPFC_WP_PLUGIN_DIR."/");

								if ($unzipfile) {
									$result = activate_plugin( 'wp-fastest-cache-premium/wpFastestCachePremium.php' );

									if ( is_wp_error( $result ) ) {
										$res = array("success" => false, "error_message" => "Error occured while the plugin was activated"); 
									}else{
										$res = array("success" => true);
										//$this->deleteCache(true);
									}
								} else {
									$res = array("success" => false, "error_message" => 'Error occured while the file was unzipped');      
								}
							}
							
						}else{
							$res = array("success" => false, "error_message" => "unzip_file() is not found");
						}
					}else{
						$res = array("success" => false, "error_message" => "/wp-content/plugins/ is not writable");
					}
				}else{
					$res = array("success" => false, "error_message" => $content["error_message"]);
				}
			
					


				echo json_encode($res);
				exit;

			}else{
				wp_die("Must be admin");
			}
		}
	}
?>