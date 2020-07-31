<?php
	
	class WpFastestCacheUpdate{
		private $slug = "wp-fastest-cache-premium";
		private $download_url = "";
		private $info = false;
		private $current_version = false;

		public function __construct(){
			if(isset($_GET["page"]) && $_GET["page"] == "wpfastestcacheoptions"){
				add_action('in_admin_footer', array($this, 'addJavaScript'));
			}

			add_filter('plugins_api', array($this, 'wp_fastest_cache_premium_plugin_info'), 20, 3);
			add_filter('pre_set_site_transient_update_plugins', array($this, 'display_transient_update_plugins'));
			add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);
		}

		public function after_update($upgrader_object, $options){
			$current_plugin_path_name = plugin_basename( __FILE__ );

		    if($options['action'] == 'update' && $options['type'] == 'plugin'){
		    	if(isset($options['plugins'])){
			    	foreach($options['plugins'] as $each_plugin){
			    		if(preg_match("/wp-fastest-cache-premium/i", $each_plugin)){
			    			delete_transient('wpfc_premium_update_info');
			    		}
			    	}
		    	}
		    }
		}

		public function addJavaScript(){
			$this->start();

			if($this->current_version == $this->info->version){
				?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#wpfc-update-premium-button").closest("a").removeAttr("href");
						jQuery("#wpfc-update-premium-button").text("No Update");
					});
				</script>
				<?php
			}else{
				?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#wpfc-update-premium-button").closest("a").removeAttr("href");
						jQuery("#wpfc-update-premium-button").attr("class", "wpfc-btn primaryCta");
						
						jQuery("#wpfc-update-premium-button").click(function(){
							window.location.href = "update-core.php";
						});
					});
				</script>
				<?php
			}
		}

		public function start(){
			$this->set_current_version();
			$this->set_download_url();
			$this->set_info();
		}

		public function set_current_version(){
			if(preg_match("/\/pro\/library\//", plugin_dir_path( __FILE__ ))){
				$plugin_data = get_plugin_data(preg_replace("/\/pro\/library\//", "/wpFastestCachePremium.php", plugin_dir_path( __FILE__ )));
			}else if(preg_match("/\\\\pro\\\\library\//", plugin_dir_path( __FILE__ ))){
				$plugin_data = get_plugin_data(preg_replace("/\\\\pro\\\\library\//", "\wpFastestCachePremium.php", plugin_dir_path( __FILE__ )));
			}

			$this->current_version = $plugin_data["Version"];
		}

		public function set_download_url(){
			$this->download_url = "https://api.wpfastestcache.net/premium/newdownload/".str_replace(array("http://", "www."), "", $_SERVER["HTTP_HOST"])."/".get_option("WpFc_api_key");
		}

		public function set_info(){
			$this->info = get_transient("wpfc_premium_update_info");

			if(empty($this->info)){
				$response = wp_remote_get('https://api.wpfastestcache.net/updateinfo.php?v='.$this->current_version, array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json'
					) )
				);

				if(!$response || is_wp_error($response)){
					$this->info = false;
				}else{
					if(wp_remote_retrieve_response_code($response) == 200){
						if($json_data = wp_remote_retrieve_body($response)){
							$this->info = json_decode($json_data);
							set_transient("wpfc_premium_update_info", $this->info, 60*60*24*15);
						}
					}

				}
			}
		}

		public function wp_fastest_cache_premium_plugin_info( $res, $action, $args ){
			$this->start();

			// do nothing if this is not about getting plugin information
			if( $action !== 'plugin_information' ){
				return false;
			}
		 
			// do nothing if it is not our plugin	
			if($this->slug !== $args->slug ){
				return $res;
			}

			// do nothing if there is no new version number
			if($this->current_version == $this->info->version){
				return $res;
			}
		 
		 
		 
			if( $this->info ) {
		 

				$res = new stdClass();
				$res->name = $this->info->name;
				$res->slug = $this->slug;
				$res->version = $this->info->version;
				$res->tested = $this->info->tested;
				$res->requires = $this->info->requires;
				$res->author = 'Emre Vona'; // I decided to write it directly in the plugin
				$res->download_link = $this->download_url;
				$res->trunk = $this->download_url;
				$res->last_updated = $this->info->last_updated;
				$res->sections = array(
					'changelog' => $this->info->sections->changelog, // changelog tab
				);
		 
				$res->banners = array(
					'low' => 'https://ps.w.org/wp-fastest-cache/assets/banner-772x250.jpg?rev='.time(),
		            		'high' => ''
				);

				return $res;
			}
		 
			return false;
		}

		public function display_transient_update_plugins ($transient){
			$this->start();

			// do nothing if the info is empty
			if(!$this->info ){
				return $transient;
			}

			// do nothing if there is no new version number
			if($this->current_version == $this->info->version){
				if(isset($transient->response["wp-fastest-cache-premium/wpFastestCachePremium.php"])){
					unset($transient->response["wp-fastest-cache-premium/wpFastestCachePremium.php"]);
				}

				return $transient;
			}

			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->plugin = "wp-fastest-cache-premium/wpFastestCachePremium.php";
			$obj->new_version = $this->info->version;
			$obj->tested = $this->info->tested;
			$obj->package = $this->download_url;
			$obj->icons = array("2x" => "https://ps.w.org/wp-fastest-cache/assets/icon-256x256.png?rev=".time(), "1x" => "https://ps.w.org/wp-fastest-cache/assets/icon-128x128.png?rev=".time());



			$transient->response["wp-fastest-cache-premium/wpFastestCachePremium.php"] = $obj;

			return $transient;
		}

	}
?>