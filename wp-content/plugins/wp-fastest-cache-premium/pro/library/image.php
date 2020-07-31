<?php
	class WpFastestCacheImageOptimisation{
		public $uploadDir;
		private $id = false;
		private $metadata = array();
		private $name = "";
		private $path = "";
		private $url = "";
		private $images = array();
		private $images_clone = array();
		private $location = "";

		public function __construct(){
			if(isset($_GET["id"]) && $_GET["id"]){
				$this->id = intval($_GET["id"]);
			}

			$this->create_api_key();
			$this->uploadDir = wp_upload_dir();
		}

		public function create_api_key(){
			if(!get_option("WpFc_api_key")){
				update_option("WpFc_api_key", md5(microtime(true)));
			}
		}


		public function get_template_path($file_name){
			return WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/templates/".$file_name;
		}

		public function get_total_reduction_image_number(){
			global $wpdb;
			$query = "SELECT sum(`meta_value`) as total FROM `".$wpdb->prefix."postmeta` WHERE `meta_key`= 'wpfc_optimisation_reduction'";
			$result = $wpdb->get_row( $query );

			if($result->total){
				return ($result->total && $result->total > 0) ? $result->total : 0;
			}else{
				return 0;
			}
		}

		public function get_total_optimized_image_number(){
			$query_images_args = array();
			
			$query_images_args['post_type'] = 'attachment';
			$query_images_args['post_mime_type'] = array('image/jpeg', 'image/png');
			$query_images_args['post_status'] = 'inherit';
			$query_images_args['posts_per_page'] = -1;
			$query_images_args['meta_query'] = array(
										array(
											'key' => 'wpfc_optimisation',
											'compare' => 'EXISTS'
											),
										array(
											'key' => 'wpfc_optimisation',
											'value' => base64_encode('"destination_path"'),
											'compare' => 'LIKE'
											)
										);
			return $this->get_count_only($query_images_args);
		}

		public function get_total_image_error_number(){
			$query_images_args = array();
			
			$query_images_args['post_type'] = 'attachment';
			$query_images_args['post_mime_type'] = array('image/jpeg', 'image/png');
			$query_images_args['post_status'] = 'inherit';
			$query_images_args['posts_per_page'] = -1;
			$query_images_args['meta_query'] = array(
										array(
											'key' => 'wpfc_optimisation',
											'compare' => 'EXISTS'
											),
										array(
											'key' => 'wpfc_optimisation',
											'value' => base64_encode('"error_code"'),
											'compare' => 'LIKE'
											)
										);
			return $this->get_count_only($query_images_args);
		}

		public function null_posts_groupby(){
			return "";
		}

		public function count_posts_fields(){
			return "COUNT(*) as post_count_wpfc";
		}

		public function get_count_only($query_images_args){
			add_filter( 'posts_fields', array($this,'count_posts_fields'));
			add_filter( 'posts_groupby', array($this, 'null_posts_groupby'));

			unset($query_images_args["offset"]);
			unset($query_images_args["order"]);
			unset($query_images_args["orderby"]);

			$query_images_args['posts_per_page'] = -1;

			$query_image = new WP_Query( $query_images_args );

			return $query_image->posts[0]->post_count_wpfc;
		}

		public function get_total_image_number(){
			$query_images_args = array();
			
			$query_images_args['post_type'] = 'attachment';
			$query_images_args['post_mime_type'] = array('image/jpeg', 'image/png');
			$query_images_args['post_status'] = 'inherit';
			$query_images_args['posts_per_page'] = -1;
			$query_images_args['meta_query'] = array(
										array(
											'key' => '_wp_attachment_metadata',
											'compare' => 'EXISTS'
											)
										);

			return $this->get_count_only($query_images_args);
		}

		public function get_total_pending_image_number(){
			$query_images_args = array();
			
			$query_images_args['post_type'] = 'attachment';
			$query_images_args['post_mime_type'] = array('image/jpeg', 'image/png');
			$query_images_args['post_status'] = 'inherit';
			$query_images_args['posts_per_page'] = -1;
			$query_images_args['meta_query'] = array(
										array(
											'key' => '_wp_attachment_metadata',
											'compare' => 'EXISTS'
											),
										array(
											'key' => 'wpfc_optimisation',
											'compare' => 'NOT EXISTS'
											)
										);

			return $this->get_count_only($query_images_args);
		}

		public function hook(){
			add_action('wp_ajax_wpfc_revert_image_ajax_request', array($this, "wpfc_revert_image_ajax_request"));
			add_action('wp_ajax_wpfc_statics_ajax_request', array($this, "wpfc_statics_ajax_request"));
			add_action('wp_ajax_wpfc_optimize_image_ajax_request', array($this, "wpfc_optimize_image_ajax_request"));
			add_action('wp_ajax_wpfc_update_image_list_ajax_request', array($this, "wpfc_update_image_list_ajax_request"));
		}

		public function get_not_optimized_before(){
			$tmp_image = array();

			$valueJson = get_post_meta($this->id, 'wpfc_optimisation', true);

			$tmpvalueJson = base64_decode($valueJson);
			if($tmpvalueJson){
				$std = json_decode($tmpvalueJson);
				$metaOptimized = $this->object_to_array($std);
				$percentage = count($metaOptimized)*100/count($this->images);
			}else{
				$percentage = 100/count($this->images);
				return array("metaOptimized" => array(), "images" => array_slice($this->images, 0, 1), "total_reduction" => 0, "percentage" => $percentage);
			}
			
			foreach ($this->images as $key => $value) {
				$exist = false;

				foreach ($metaOptimized as $meta_key => $meta_value) {
					if($value["file"] == $meta_value["file"]){
						$exist = true;
						//break;
					}
				}
				if(!$exist){
					array_push($tmp_image, $value);
				}
			}

			//START: total reduction
			$total_reduction = 0;
			foreach ($metaOptimized as $m_key => $m_value) {
				$m_value["reduction"] = isset($m_value["reduction"]) ? $m_value["reduction"] : 0;
				$total_reduction += $m_value["reduction"];
			}
			//END: total reduction

			if(count($tmp_image) > 0){

				if(isset($_GET["last"])){
					if(preg_match("/last-(\d+)/", $_GET["last"], $last_number)){
						if(count($tmp_image) > 5){
							$tmp_image = array_slice($tmp_image, $last_number[1]*-1, 1);
						}
					}
				}

				return array("metaOptimized" => $metaOptimized, "images" => array_slice($tmp_image, 0, 1), "total_reduction" => $total_reduction, "percentage" => $percentage);
			}else{
				return array("metaOptimized" => array(), "images" => array(), "total_reduction" => 0);
			}
		}

		public function object_to_array($obj) {
		    if(is_object($obj)) $obj = (array) $obj;
		    if(is_array($obj)) {
		        $new = array();
		        foreach($obj as $key => $val) {
		            $new[$key] = $this->object_to_array($val);
		        }
		    }
		    else $new = $obj;
		    return $new;       
		}

		public function re_order_by_dimensions(){
			$tmp = $this->images;

			foreach ($tmp as $key => $value) {
				$width_list[$key] = $value['width'];
			}

			array_multisort($width_list, SORT_DESC, $tmp);

			return $tmp;
		}

		public function optimizeFirstImage(){
			$this->setId();
			$this->setMetaData();
			$this->set_server_location();

			if(!$this->id){
				return array("finish", "success"); 
			}else if(!isset($this->metadata["file"]) && $this->id){
				$metaOptimized = array();
				$metaOptimized[0]["time"] = time();
				$metaOptimized[0]["id"] = $this->id;
				$metaOptimized[0]["error_code"] = 17;

				update_post_meta($this->id, 'wpfc_optimisation_reduction', 0);
				update_post_meta($this->id, 'wpfc_optimisation', base64_encode(json_encode($metaOptimized)));

				return array("Image has been optimizedxx", "success", $this->id, 100);
			}else{
				$this->setName();
				$this->setPath();
				$this->setUrl();
				$this->setImages();
			}

			if($this->id && count($this->images) == 0){
				$metaOptimized = array();
				$metaOptimized[0]["time"] = time();
				$metaOptimized[0]["id"] = $this->id;
				$metaOptimized[0]["error_code"] = 18;

				update_post_meta($this->id, 'wpfc_optimisation_reduction', 0);
				update_post_meta($this->id, 'wpfc_optimisation', base64_encode(json_encode($metaOptimized)));

				return array("Image has been optimizedxx", "success", $this->id, 100);
			}

			

			if(!$this->backup_folder_exist()){
				return array("Please Create folder ".$this->uploadDir["basedir"]."/wpfc-backup/", "error");
			}else{
				$error_exist = false;
				$metaOptimized = array();
				$total_reduction = 0;

				$this->images = $this->image_arr_unique($this->images);
				$this->images = $this->re_order_by_dimensions();

				$this->images_clone = $this->images;

				$optimized_before = $this->get_not_optimized_before();

				$this->images = $optimized_before["images"];
				$metaOptimized = $optimized_before["metaOptimized"];
				$total_reduction = $optimized_before["total_reduction"];
				$percentage = isset($optimized_before["percentage"]) && $optimized_before["percentage"] ? $optimized_before["percentage"] : 0;

				if(count($this->images) == 0){
					return array("Image has been optimizedxx", "success", "", 100);
				}

				foreach ($this->images as $key => $value) {
					$res = $this->compress($value);

					if($res["success"]){
						$value["destination_path"] = $res["destination_path"];
						$value["reduction"] = $res["reduction"];

						$total_reduction += $value["reduction"];

						$value["time"] = time();
						$value["id"] = $this->id;

						array_push($metaOptimized, $value);
					}else{
						if(!isset($res["error_code"]) && isset($res["error_message"])){
							return array($res["error_message"], "error");
							break;
						}

						if(in_array($res["error_code"] , array(2, 6, 7, 11, 19, 20))){
							return array($res["error_message"], "error");
							break;
						}

						$value["error_code"] = $res["error_code"];
						$error_exist = true;
					}

					$value["time"] = time();
					$value["id"] = $this->id;

					if(isset($value["error_code"]) && $value["error_code"]){
						if($value["error_code"] != 8 || ($value["error_code"] == 8 && $key === 0)){
							array_push($metaOptimized, $value);
						}
					}
				}

				// if(!$error_exist){
				// 	$this->clean_backups($metaOptimized);
				// }

				$percentage = $this->update_meta($total_reduction, $metaOptimized);

				return array("Image has been optimizedxx", "success", $this->id, $percentage);
			}
		}

		public function update_meta($total_reduction, $metaOptimized){
			if(isset($metaOptimized[0]) && isset($metaOptimized[0]["error_code"]) && $metaOptimized[0]["error_code"]){

				update_post_meta($this->id, 'wpfc_optimisation', base64_encode(json_encode($metaOptimized)));
				update_post_meta($this->id, 'wpfc_optimisation_reduction', 0);

				return 100;
			}else{
				$percentage = 0;
				$meta_temp = array();
				foreach ($this->images_clone as $key => $value){
					$backup_file = str_replace($this->uploadDir["basedir"], $this->uploadDir["basedir"]."/wpfc-backup", $value["file"]);
					
					if(file_exists($value["file"]) && file_exists($backup_file)){
						$diff = filesize($backup_file) - filesize($value["file"]);
						$diff = $diff > 0 ? $diff : 0;

						$value["destination_path"] = $backup_file;
						$value["reduction"] = $diff;
						$value["time"] = time();
						$value["id"] = $this->id;

						array_push($meta_temp, $value);
					}
				}

				foreach($metaOptimized as $m_key => $m_value){
					if(isset($m_value["error_code"]) && $m_value["error_code"]){
						$exist = false;

						for($i=0; $i < count($meta_temp); $i++){
							if($meta_temp["file"] == $m_value["file"]){
								$exist = true;
							}
						}

						if(!$exist){
							$m_value["destination_path"] = "";
							$m_value["reduction"] = 0;
							$m_value["time"] = time();
							$m_value["id"] = $this->id;

							array_push($meta_temp, $m_value);
						}
					}
				}
				

				if(count($meta_temp) > 0){
					$percentage = count($meta_temp)*100/count($this->images_clone);
				}else{
					$percentage = 0;
				}

				update_post_meta($this->id, 'wpfc_optimisation_reduction', $total_reduction);
				update_post_meta($this->id, 'wpfc_optimisation', base64_encode(json_encode($meta_temp)));

				return $percentage;
			}
		}

		public function clean_backups($meta){
			foreach ($meta as $key => $value) {
				if(file_exists($value["destination_path"])){
					unlink($value["destination_path"]);
				}
			}
		}

		public function compress($source_image){
			/*
				Error Codes
				2 = in backup folder parent folder not writable
				3 = no need to optimize
				4 = source is not writable
				5 = destination is not writable
				6 = ImageMagick library is not avaliable
				7 = Error via api
				8 = Source file does not exist
				9 = Image size exceed 5mb limit while processing
			   11 = Empty Name
			   12 = Forbidden
			   13 = CloudFlare to restrict access
			   14 = No Extension
			   15 = Image size is 0Kb
			   16 = Corrupted Image
			   17 = Empty Metadata
			   18 = No Image
			   19 = destination_move_source_path is not saved
			   20 = file size of destination_move_source_path is zero
			   21 = Unacceptable file type
			*/

			// if the url starts with /wp-content
			if(preg_match("/^\/wp-content/i", $source_image["url"])){
				$source_image["url"] = home_url().$source_image["url"];
			}

			$source_path = $source_image["file"];

			$resBackup = array("success" => true, "error_message" => "");

			$destination_path = str_replace($this->uploadDir['basedir'], $this->uploadDir['basedir']."/wpfc-backup", $source_path);
			$destination_path = $destination_path."_tmp";

			if($resBackup = $this->createBackupFolder($destination_path)){

				if(strlen($this->name) === 0){
					return array("success" => false, "error_code" => 11);
				}

				if(!file_exists($source_path)){
					return array("success" => false, "error_code" => 8);
				}

				if(!pathinfo($source_image["url"], PATHINFO_EXTENSION)){
					return array("success" => false, "error_code" => 14);
				}

				// if(@filesize($source_path) > 5000000){
				// 	return array("success" => false, "error_code" => 9);
				// }

				if(!$this->acceptable_mime_type($source_path)){
					return array("success" => false, "error_code" => 21);
				}


				// $image_exist = wp_remote_get($source_image["url"], array('timeout' => 5 ) );
				
				// if (!$image_exist || is_wp_error($image_exist)){
				// 	if($image_exist->get_error_message() == "Failure when receiving data from the peer"){
				// 		//true for now
				// 	}else{
				// 		return array("success" => false, "error_message" => $image_exist->get_error_message());
				// 	}
				// }else{
				// 	if(wp_remote_retrieve_response_code($image_exist) == 403){
				// 		if(preg_match("/CloudFlare\s+to\s+restrict\s+access/", wp_remote_retrieve_body( $image_exist ))){
				// 			return array("success" => false, "error_code" => 13);
				// 		}else{
				// 			return array("success" => false, "error_code" => 12);
				// 		}
				// 	}

				// 	if(wp_remote_retrieve_response_code($image_exist) != 200){
				// 		return array("success" => false, "error_code" => 8);
				// 	}
				// }






				if(!$this->is_image($source_path)){
					return array("success" => false, "error_code" => 16);
				}

				if(filesize($source_path) == 0){
					return array("success" => false, "error_code" => 15);
				}

				if(@rename($source_path, $source_path."_writabletest")){
					rename($source_path."_writabletest", $source_path);
				}else{
					return array("success" => false, "error_message" => $source_path." is not writable", "error_code" => 4);
				}

				if (@copy($source_path, $destination_path."_writabletest")) {
					unlink($destination_path."_writabletest");
				}else{
					return array("success" => false, "error_message" => $destination_path." is not writable", "error_code" => 5);
				}

				if($resBackup["success"]){
					$compressed_result = $this->compress_image_external($source_image["url"]);

					if($compressed_result["success"]){
						if($image_data = $compressed_result["url"]){
							@file_put_contents($destination_path, $image_data);
						}
					}else{
						return array("success" => false, "error_code" => 7, "error_message" => $compressed_result["error_message"]);
					}


					$source_move_backup_path = str_replace($this->uploadDir['basedir'], $this->uploadDir['basedir']."/wpfc-backup", $source_path);
					$destination_move_source_path = str_replace("/wpfc-backup/", "/", $destination_path);

					if (@copy($source_path, $source_move_backup_path)) {
						if(@copy($destination_path, $destination_move_source_path)){

							if(file_exists($destination_move_source_path)){
								if(filesize($destination_move_source_path) > 0){
									$diff = $this->compareSizes($source_path, $destination_path);
									unlink($source_path);
									unlink($destination_path);
									rename($destination_move_source_path, preg_replace("/\_tmp$/", "", $destination_move_source_path));

									$this->save_webp($source_image["url"], $destination_move_source_path);
									
									return array("success" => true, "destination_path" => preg_replace("/\_tmp$/", "", $destination_path), "reduction" => $diff);
								}else{
									return array("success" => false, "error_code" => 20, "error_message" => $destination_path." file size of destination_move_source_path is zero");
								}
							}else{
								return array("success" => false, "error_code" => 19, "error_message" => $destination_path." destination_move_source_path is not saved");
							}
							
						}else{
							return array("success" => false, "error_code" => 5);
						}
					}else{
						return array("success" => false, "error_code" => 4);
					}

				}else{
					return $resBackup;
				}
			}

		}

		public function is_image($source_path){
			$size = getimagesize($source_path);

			if($size){
				return true;
			}

			return false;
		}

		public function save_webp($url, $path){
			$webp = true;
										
			if($webp){
				$path = preg_replace("/\_tmp$/", ".webp", $path);

				$response = wp_remote_get($this->location."/image/webp/".$url, 
							array('user-agent' => $_SERVER["HTTP_HOST"], 
								'timeout' => 20,
								'headers' => array(
									// READ: cache-control causes an issue so removed => Notice:  Array to string conversion in /wp-includes/class-http.php on line 1484
									//"cache-control" => array("no-store, no-cache, must-revalidate", "post-check=0, pre-check=0")
									)
								) 
							);

				if ( !$response || is_wp_error( $response ) ) {
					//return array("success" => false, "error_message" => $response->get_error_message());
				}else{
					if(wp_remote_retrieve_response_code($response) == 200){
						$server_output = wp_remote_retrieve_body($response);

						if($server_output){
							@file_put_contents($path, $server_output);
						}
					}
				}
			}
		}

		public function compress_image_external($url){

			// WP Hide & Security Enhancer
			global $wph;
			if(isset($wph) && property_exists($wph, 'functions')){
				$url = $wph->functions->content_urls_replacement($url, $wph->functions->get_replacement_list());
			}





			$response = wp_remote_get($this->location."/image/compress/".$url, 
						array('user-agent' => $_SERVER["HTTP_HOST"], 
							'timeout' => 20,
							'headers' => array(
								// READ: cache-control causes an issue so removed => Notice:  Array to string conversion in /wp-includes/class-http.php on line 1484
								//"cache-control" => array("no-store, no-cache, must-revalidate", "post-check=0, pre-check=0")
								)
							) 
						);

			if ( !$response || is_wp_error( $response ) ) {
				return array("success" => false, "error_message" => $response->get_error_message());
			}else{
				if(wp_remote_retrieve_response_code($response) == 200){
					$server_output = wp_remote_retrieve_body( $response );

					if($server_output && $server_output[0] != "{"){
						return array("success" => true, "url" => $server_output);
					}else{
						if($server_output){
							$res = json_decode($server_output);
							return array("success" => false, "error_message" => $res->error_message);
						}else{
							return array("success" => false, "error_message" => "Error Code 103");
						}
					}
				}else if(wp_remote_retrieve_response_code($response) == 500){
					// sometimes 500 error returns for the sub images
					if(preg_match("/\d+x\d+\./", $url)){
						return array("success" => false, "error_message" => "500");
					}else{
						return array("success" => false, "error_message" => "Internal Server Error: ".$url);
					}
				}else{
					return array("success" => false, "error_message" => "Could not connect server");
				}
			}
		}

		public function getQuality($img){
			$d = $img->getImageGeometry();
			if($d['width'] < 200 && $d['height'] < 200){
				return 85;
			}

			return 90;
		}

		public function compareSizes($source_path, $destination_path){
			$diff = filesize($source_path) - filesize($destination_path);

			return ($diff > 0) ? $diff : 1;
		}

		public function createBackupFolder($destination_path){
			$destination_path = str_replace($this->uploadDir['basedir'], "", $destination_path);
			$pathArr = explode("/", $destination_path);

			$path = $this->uploadDir['basedir'];

			for ($i=1; $i < count($pathArr) - 1; $i++) {
				$parentPath = $path;
				$path = $path."/".$pathArr[$i];

				if(!is_dir($path)){
					if(@mkdir($path, 0755, true)){
						
					}else{
						//warning
						if($pathArr[$i] == "wpfc-backup"){
							//toDO: to stop cron job and warn the user
						}
						return array("success" => false, "error_message" => $parentPath." is not writable", "error_code" => 2);
					}
				}	
			}

			return array("success" => true, "error_message" => "");
		}

		public function set_server_location(){
			if(isset($_GET["server_url"]) && $_GET["server_url"]){
				$this->location = $_GET["server_url"];
			}else{
				$this->location = "https://api.wpfastestcache.ga";
			}
		}

		public function setId(){
			if(isset($_GET["id"]) && $_GET["id"]){
				$this->id = intval($_GET["id"]);
			}else{
				$this->id = $this->getFirstId();
			}
		}

		public function setImages(){
			if(isset($this->metadata["file"]) && $this->metadata["file"]){
				$arr = array("file" => $this->uploadDir['basedir']."/".$this->metadata["file"],
							"url" => $this->uploadDir['baseurl']."/".$this->metadata["file"],
							"width" => $this->metadata["width"],
							"height" => $this->metadata["height"],
							"mime_type" => "");
				
				array_push($this->images, $arr);

				$i = 0;
				$image_error = false;

				if(is_array($this->metadata["sizes"])){
					foreach((array)$this->metadata["sizes"] as $key => $value){
						$value["url"] = $this->url.$value["file"];
						$value["file"] = $this->path.$value["file"];
						$value["mime_type"] = isset($value["mime-type"]) ? $value["mime-type"] : "";

						unset($value["mime-type"]);

						if($i == 0){
							if($this->is_url_404($this->get_correct_url($this->uploadDir['baseurl']."/".$this->metadata["file"]))){
								$image_error = true;
								break;
							}
						}

						if(!$this->is_url_404($this->get_correct_url($value["url"])) && $this->acceptable_mime_type($value["file"])){
							array_push($this->images, $value);
						}

						$i++;
					}
				}

				if(!$image_error){
					$this->images_not_in_metadata();
				}

				//$this->images = array_slice($this->images, 0, 10);
			}
		}

		public function get_correct_url($path){
			if(preg_match("/^\/wp-content/i", $path)){
				//content_url() must return HTTP but it return /wp-content so we need to check
				if(content_url() == "/wp-content"){
					if(home_url() == site_url()){
						$path = home_url().$path;
					}
				}
			}

			return $path;
		}

		public function images_not_in_metadata(){
			$paths = array();

			foreach ($this->images as $key => $value) {
				array_push($paths, $value["file"]);
			}
			
			$files = glob($this->path.$this->name."-"."*");

			foreach ((array)$files as $dosya) {
				if(@filesize($dosya) > 1000000){
					continue;
				}

				if(!preg_match("/\.(jpg|jpeg|jpe|png)$/i", $dosya)){
					continue;
				}

				$exp_dos = explode('/',$dosya);
				$basename = end($exp_dos);

				if(!in_array($dosya, $paths)){
					if(preg_match("/".preg_quote($this->name, "/")."-(\d+)x(\d+)\..+/", $basename, $dimensions)){
						$value = array();
						$value["url"] = $this->url.$basename;
						$value["file"] = $dosya;
						$value["width"] = 0;
						$value["height"] = 0;

						$value["width"] = $dimensions[1];
						$value["height"] = $dimensions[2];

						if(!$this->is_url_404($value["url"])){
							array_push($this->images, $value);
						}
					}
				}
			}
		}

		public function setPath(){
			$this->path = $this->uploadDir['basedir']."/".preg_replace("/".preg_quote($this->name, "/").".+/", "", $this->metadata["file"]);
		}

		public function setUrl(){
			$this->url = $this->uploadDir['baseurl']."/".preg_replace("/".preg_quote($this->name, "/").".+/", "", $this->metadata["file"]);
		}

		public function setName(){
			if($this->metadata){
				if(count($this->metadata["sizes"]) > 0){
					$array_values = array_values($this->metadata["sizes"]);
					$this->name = preg_replace("/-".$array_values[0]["width"]."x".$array_values[0]["height"].".+/", "", $array_values[0]["file"]);

					if(!$this->name){
						$this->name = substr($this->metadata["file"], strrpos($this->metadata["file"], "/") + 1);
					}
				}else{
					$info = pathinfo($this->metadata["file"]);
					$this->name =  basename($this->metadata["file"],'.'.$info['extension']);

					//$this->name = substr($this->metadata["file"], strrpos($this->metadata["file"], "/") + 1);
				}
			}
		}

		public function setMetaData(){
			$this->metadata = wp_get_attachment_metadata($this->id);
		}

		//to get last image which is not optimized
		public function getFirstId(){
			// global $wpdb;
			// $query = "SELECT SQL_CALC_FOUND_ROWS  ".$wpdb->prefix."posts.ID FROM ".$wpdb->prefix."posts  LEFT JOIN ".$wpdb->prefix."postmeta ON (".$wpdb->prefix."posts.ID = ".$wpdb->prefix."postmeta.post_id AND ".$wpdb->prefix."postmeta.meta_key = 'wpfc_optimisation' )  LEFT JOIN ".$wpdb->prefix."postmeta AS mt1 ON ( ".$wpdb->prefix."posts.ID = mt1.post_id ) WHERE 1=1  AND (".$wpdb->prefix."posts.post_mime_type = 'image/jpeg')  AND ".$wpdb->prefix."posts.post_type = 'attachment' AND ((".$wpdb->prefix."posts.post_status = 'inherit')) AND ( 
			// 		  ".$wpdb->prefix."postmeta.post_id IS NULL 
			// 		  AND 
			// 		  mt1.meta_key = '_wp_attachment_metadata'
			// 		) GROUP BY ".$wpdb->prefix."posts.ID ORDER BY ".$wpdb->prefix."posts.ID DESC LIMIT 0, 1";

			// $result = $wpdb->get_row( $query );

			// if($result && $result->ID){
			// 	return $result->ID;
			// }else{
			// 	// FOR PNG
			// 	$query = "SELECT SQL_CALC_FOUND_ROWS  ".$wpdb->prefix."posts.ID FROM ".$wpdb->prefix."posts  LEFT JOIN ".$wpdb->prefix."postmeta ON (".$wpdb->prefix."posts.ID = ".$wpdb->prefix."postmeta.post_id AND ".$wpdb->prefix."postmeta.meta_key = 'wpfc_optimisation' )  LEFT JOIN ".$wpdb->prefix."postmeta AS mt1 ON ( ".$wpdb->prefix."posts.ID = mt1.post_id ) WHERE 1=1  AND (".$wpdb->prefix."posts.post_mime_type = 'image/png')  AND ".$wpdb->prefix."posts.post_type = 'attachment' AND ((".$wpdb->prefix."posts.post_status = 'inherit')) AND ( 
			// 		  ".$wpdb->prefix."postmeta.post_id IS NULL 
			// 		  AND 
			// 		  mt1.meta_key = '_wp_attachment_metadata'
			// 		) GROUP BY ".$wpdb->prefix."posts.ID ORDER BY ".$wpdb->prefix."posts.ID DESC LIMIT 0, 1";
				
			// 	$result = $wpdb->get_row( $query );

			// 	if($result && $result->ID){
			// 		return $result->ID;
			// 	}else{
			// 		return false;
			// 	}
			// }

			$query_images_args = array(
				'order' => 'DESC',
				'orderby' => 'ID',
			    'post_type' => 'attachment', 
			    'post_mime_type' =>'image/jpeg, image/png', 
			    'post_status' => 'inherit',
			    'posts_per_page' => 1,
			    'meta_query' => array(
					array(
						'key' => 'wpfc_optimisation',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key' => '_wp_attachment_metadata',
						'compare' => 'EXISTS'
					)
				)
			);

			$query_image = new WP_Query( $query_images_args );

			return count($query_image->posts) == 1 ? $query_image->posts[0]->ID : false;
		}

		public function wpfc_update_image_list_ajax_request(){
			if(current_user_can('manage_options')){
				$query_images_args = array();
				
				$query_images_args["offset"] = $_GET["page"]*$_GET["per_page"];
				$query_images_args['order'] = 'DESC';
				$query_images_args['orderby'] = 'ID';
				$query_images_args['post_type'] = 'attachment';
				$query_images_args['post_mime_type'] = array('image/jpeg', 'image/png');
				$query_images_args['post_status'] = 'inherit';
				$query_images_args['posts_per_page'] = $_GET["per_page"];
				$query_images_args['meta_query'] = array(
											array(
												'key' => 'wpfc_optimisation',
												'compare' => 'EXISTS'
												)
											);

				if(isset($_GET["search"]) && $_GET["search"]){
					$query_images_args["s"] = isset($_GET["search"]) ? $_GET["search"] : "";
				}

				if(isset($_GET["filter"]) && $_GET["filter"]){
					if($_GET["filter"] == "error_code"){
						
						$filter = array(
										'key' => 'wpfc_optimisation',
										'value' => base64_encode('"error_code"'),
										'compare' => 'LIKE'
										);
						$filter_second = array(
										'key' => 'wpfc_optimisation',
										'compare' => 'NOT LIKE'
										);

						array_push($query_images_args['meta_query'], $filter);
						array_push($query_images_args['meta_query'], $filter_second);
					}
				}


				$result = array("content" => $this->image_list_content($query_images_args),
								"result_count" => $this->get_count_only($query_images_args)
						  );
				echo json_encode($result);
				exit;
			}else{
				wp_die("Must be admin");
			}
		}

		public function get_credit(){
			if($credit = get_transient("WpFc_credit")){
				if(isset($_GET["credit"]) && $_GET["credit"] && $_GET["credit"] == "true"){
					$credit = $credit - 1;
					
					
					set_transient("WpFc_credit", $credit, 100);

					return $credit;
				}else{
					return $credit;
				}
			}else{
				$domain = preg_replace("/^www\d*\./", "", $_SERVER["HTTP_HOST"]);

				$response = wp_remote_get("https://api.wpfastestcache.net/user/".$domain."/xcredit/".get_option("WpFc_api_key"), 
					array('timeout' => 5 ) 
				);

				if ( !$response || is_wp_error( $response ) ) {
					return $response->get_error_message();
				}else{
					if(wp_remote_retrieve_response_code($response) == 200){
						$credit = wp_remote_retrieve_body( $response );

						if (!is_numeric($credit)) {
							return "Error Occured"; 
						}

						set_transient("WpFc_credit", $credit, 100);

						return $credit;
					}else{
						if($credit = get_transient("WpFc_credit")){
							return $credit;
						}

						return "Error Occured";
					}
				}
			}
		}

		public function wpfc_statics_ajax_request(){
			if(current_user_can('manage_options')){
				$res = array(
							"total_image_number" => $this->get_total_image_number(),
							"error" => $this->get_total_image_error_number(),
							"optimized" => 0,
							//"optimized" => $this->get_total_optimized_image_number(),
							"pending" => $this->get_total_pending_image_number(),
							"reduction" => $this->get_total_reduction_image_number(),
							"percent" => 0,
							"credit" => $this->get_credit()
							);
				$res["optimized"] = $res["total_image_number"] - $res["pending"] - $res["error"];

				if($res["total_image_number"] > 0){
					$res["percent"] = ($res["total_image_number"] - $res["pending"] - $res["error"])*100/$res["total_image_number"];
				}else{
					$res["percent"] = 0;
				}
				
				$res["percent"] = number_format($res["percent"], 2);
				$res["reduction"] = $res["reduction"]/1000;
				
				die(json_encode($res));
			}else{
				wp_die("Must be admin");
			}
		}

		public function wpfc_optimize_image_ajax_request(){
			if(current_user_can('manage_options')){
				$res = $this->optimizeFirstImage();
				$res[1] = isset($res[1]) ? $res[1] : "";
				$res[2] = isset($res[2]) ? $res[2] : "";
				$res[3] = isset($res[3]) ? $res[3] : "";

				if(isset($res[1]) && $res[1] == "error"){
					if(isset($res[0]) && preg_match("/Buy\s+the\s+premium/", $res[0])){
						if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'rm_folder_recursively')){
							$GLOBALS["wp_fastest_cache"]->rm_folder_recursively(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/library");
							deactivate_plugins("wp-fastest-cache-premium/wpFastestCachePremium.php");
						}
					}
				}

				die('{"message" : "'.$res[0].'", "success" : "'.$res[1].'", "id" : "'.$res[2].'", "percentage" : "'.$res[3].'"}');
			}else{
				wp_die("Must be admin");
			}
		}

		public function wpfc_revert_image_ajax_request(){
			if(current_user_can('manage_options')){
				if($this->id){
					$valueJson = get_post_meta($this->id, 'wpfc_optimisation', true);

					$tmpvalueJson = base64_decode($valueJson);
					$std = json_decode($tmpvalueJson);

					if(count($std) == 1 && isset($std[0]) && $std[0]){
						if(isset($std[0]->error_code) && $std[0]->error_code){
							if($std[0]->error_code == 18){
								delete_post_meta($this->id, "wpfc_optimisation");
								delete_post_meta($this->id, "wpfc_optimisation_reduction");

								die('{"success" : "true"}');
							}
						}
					}

					$result = false;

					$std = array_reverse($std);

					$error_numbers = 0;

					foreach ($std as $key => $image) {
						if(@rename($image->file, $image->file."_tmp")){

							if(isset($image->destination_path) && file_exists($image->destination_path)){
								if(@copy($image->destination_path, $image->file)){
									unlink($image->destination_path);
									unlink($image->file."_tmp");

									if(file_exists($image->file.".webp")){
										unlink($image->file.".webp");
									}

									delete_post_meta($this->id, "wpfc_optimisation");
									delete_post_meta($this->id, "wpfc_optimisation_reduction");
									$result = '{"success" : "true"}';
								}else{
									$result = '{"success" : "false"}';
									//toDo not writeable folder
								}
							}else{
								rename($image->file."_tmp", $image->file);

								if(isset($image->error_code) && $image->error_code){
									if(isset($this->metadata["file"]) && preg_match("/".preg_quote($this->metadata["file"], "/")."/", $image->url)){
										delete_post_meta($this->id, "wpfc_optimisation");
										delete_post_meta($this->id, "wpfc_optimisation_reduction");
										$result = '{"success" : "true"}';
									}else{
										$error_numbers++;

										if($error_numbers == count($std)){
											delete_post_meta($this->id, "wpfc_optimisation");
											delete_post_meta($this->id, "wpfc_optimisation_reduction");
											$result = '{"success" : "true"}';
										}

									}
								}else{
									if(preg_match("/".preg_quote($this->metadata["file"], "/")."/", $image->url)){
										delete_post_meta($this->id, "wpfc_optimisation");
										delete_post_meta($this->id, "wpfc_optimisation_reduction");
										$result = '{"success" : "true"}';
									}
								}
							}
						}else{
							if(preg_match("/".preg_quote($this->metadata["file"], "/")."/", $image->url)){
								if(file_exists($image->file)){
									$result = '{"success" : "false", "message" : "'.$image->file.' is not writable"}';
									break;
								}
							}
							//toDo file is not writeable
						}
					}//end of loop
				}else{
					//toDO id not found
					$result = '{"success" : "false"}';
				}
				die($result);
			}else{
				wp_die("Must be admin");
			}
		}

		public function getErrorText($id){
			/*
				Error Codes
				2 = in backup folder parent folder not writable
				3 = no need to optimize
				4 = source is not writable
				5 = destination is not writable
				6 = ImageMagick library is not avaliable
				7 = Error via api
				8 = Source file does not exist
			*/
			$errors = array(
							2 => "In backup folder parent folder not writable",
							3 => "No need to optimize",
							4 => "Source is not writable",
							5 => "Destination is not writable",
							7 => "Error via api",
							8 => "Source file does not exist",
							9 => "Image size exceed 5mb limit while processing",
						   11 => "Empty Name",
						   12 => "Forbidden",
						   13 => "CloudFlare to restrict access",
						   14 => "No Extension",
						   15 => "Image size is 0Kb",
						   16 => "Corrupted Image",
						   17 => "Empty metadata",
						   18 => "No Image",
						   19 => "destination_move_source_path is not saved",
						   20 => "file size of destination_move_source_path is zero",
						   21 => "Unacceptable file type"
						);
			return isset($errors[$id]) ? $errors[$id] : "Unkown error code";
		}

		public function backup_folder_exist(){
			$backup_folder_path = $this->uploadDir["basedir"]."/wpfc-backup";

			if(is_dir($backup_folder_path)){
				return true;
			}else{
				if(@mkdir($backup_folder_path, 0755, true)){
					return true;
				}
				return false;
			}
		}

		public function push_main_image($arr){
			if(isset($arr[0]) && !isset($arr[0]->error_code)){
				$main = clone $arr[0];
				$total_reduction = 0;

				foreach($arr as $stdKey => $stdValue){
					if(!isset($stdValue->error_code)){
						if(isset($stdValue->reduction) && $stdValue->reduction){
							$total_reduction = $total_reduction + $stdValue->reduction;
						}
					}
				}

				$main->reduction = $total_reduction;

				array_unshift($arr, $main);
			}

			return $arr;
		}

		public function image_list_content($query_images_args = array()){
			$query_image = new WP_Query( $query_images_args );

			$return_output = "";

			if(count($query_image->posts) > 0){
				foreach ($query_image->posts as $key => $post) {
					$valueJson = get_post_meta( $post->ID, 'wpfc_optimisation', true);

					$tmpvalueJson = base64_decode($valueJson);
					$std = json_decode($tmpvalueJson);

					$revert = true;
					// $revert = false;

					// foreach ($std as $keyTmp => $valueTmp) {
					// 	if (array_key_exists("error_code", $valueTmp)){
					// 		$revert = true;
					// 		break;
					// 	}
					// }

					$std = $this->push_main_image($std);

					foreach ($std as $stdKey => $stdValue){
						$template_name = $stdKey === 0 ? "image_line_first.html" : "image_line.html";

						if($content = @file_get_contents($this->get_template_path($template_name))){

							$stdValue->destination_path = isset($stdValue->destination_path) ? $stdValue->destination_path : "";
							$stdValue->reduction = isset($stdValue->reduction) ? $stdValue->reduction : 0;
							
							if($stdKey === 0 && $revert){
								$revert_button = "";
							}else{
								$revert_button = "display:none;";
							}

							if(isset($stdValue->error_code) && $stdValue->error_code == 8){
								$revert_button = "display:none;";
							}

							if(file_exists($stdValue->destination_path)){
								$backup_url = str_replace($this->uploadDir['baseurl'], $this->uploadDir['baseurl']."/wpfc-backup",$stdValue->url)."?v=".time();
								$backup_title = "Backup Image";
								$backup_error_style = "";
							}else{
								if(isset($stdValue->error_code) && $stdValue->error_code){
									$backup_url = "#";
									$backup_title = $this->getErrorText($stdValue->error_code);
									$backup_error_style = "color: #FF0000;cursor:auto;font-weight:bold;";
								}else{
									$backup_url = "#";
									$backup_title = "";
									$backup_error_style = "";
								}
							}

							if(file_exists($stdValue->file)){
								$stdValue->url = $stdValue->url."?v=".time();
							}else{
								$stdValue->url = plugins_url("wp-fastest-cache")."/images/no-image.gif";
							}


							$short_code = array("{{post_id}}",
											 "{{attachment}}",
											 "{{post_title}}",
											 "{{url}}",
											 "{{width}}",
											 "{{height}}",
											 "{{reduction}}",
											 "{{date}}",
											 "{{revert_button}}",
											 "{{backup_url}}",
											 "{{backup_title}}",
											 "{{backup_error_style}}"
										);
							$datas = array($stdValue->id,
											 $stdValue->url,
											 $post->post_title,
											 $stdValue->url,
											 $stdValue->width,
											 $stdValue->height,
											 $stdValue->reduction/1000,
											 date("d-m-Y <br> H:i:s", $stdValue->time),
											 $revert_button,
											 $backup_url,
											 $backup_title,
											 $backup_error_style
										);

							$return_output .= str_replace($short_code, $datas, $content);
						}
					}
				}
			}else{
				$return_output = @file_get_contents($this->get_template_path("empty_image_line.html"));
			}

			return $return_output;
		}

		public function imageList(){ ?>
			<div id="wpfc-image-list" style="display:none;">
				<?php $this->paging(); ?>
				<div style="float:left;">
					<table class="wp-list-table widefat fixed media" style="width: 95%; margin-left: 20px;">
						<thead>
							<tr style="height: 35px;">
								<th scope="col" id="icon" class="manage-column column-icon" style=""></th>
								<th scope="col" id="title" class="manage-column column-title sortable desc" style="width: 350px;">
									<span style="padding-left: 8px;">File Name</span>
								</th>
								<th scope="col" id="author" class="manage-column column-author sortable desc" style="width: 93px;text-align: center;">
									<span>Reduction</span>
								</th>
								<th scope="col" id="date" class="manage-column column-date sortable asc" style="width: 91px;text-align: center;">
									<span>Date</span>
								</th>
								<th scope="col" id="date" class="manage-column column-date sortable asc" style="width: 60px;text-align: center;">
									<span>Revert</span>
								</th>	
							</tr>
						</thead>
						<tbody id="the-list"></tbody>
					</table>
				</div>
			</div>
			<div id="revert-loader"></div>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					WpFcStatics.init("<?php echo admin_url(); ?>admin-ajax.php", "<?php echo plugins_url(); ?>", "<?php echo $this->get_country_code(); ?>");
					WpFcStatics.set_click_event_revert_image();
					jQuery("#wpfc-imageOptimisation").change(function(e){
						WpFcStatics.update_statics();
						WpFcStatics.wpfc_get_servers();
					});
				});
			</script><?php
		}

		public function get_country_code(){
			$country = get_transient("wpfc_country_code");

			if($country){
				return $country;
			}else{
				$response = wp_remote_get("https://detect-country.ehlibeyt.workers.dev/", array('timeout' => 5, 'user-agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:64.0) Gecko/20100101 Firefox/64.0"));

				if ( !$response || is_wp_error( $response ) ) {
					return "";
				}else{
					if(wp_remote_retrieve_response_code($response) == 200){
						$country = trim(wp_remote_retrieve_body($response));
						
						if(strlen($country) == 2){
							set_transient("wpfc_country_code", $country, 60*60*24*3);

							return $country;
						}
					}
				}

				return "";
			}
		}

		public function paging(){
			include_once($this->get_template_path("paging.html"));
		}

		public function statics(){
			include_once($this->get_template_path("image-statics.php"));
		}

		public function acceptable_mime_type($filename){
			$filename = $filename;
		    $mimetype = false;

		    if(function_exists('finfo_open')) {
		       $finfo = finfo_open(FILEINFO_MIME_TYPE);
		       $mimetype = finfo_file($finfo, $filename);
		       finfo_close($finfo);
		    }else if(function_exists('getimagesize')) {
		       $img = getimagesize($filename);
		       $mimetype = $img["mime"];
		    }else{
		    	echo "not found mime_content_type";
		    	exit;
		    }

		    if(preg_match("/jpg|jpeg|jpe|png/i", $mimetype)){
		    	return true;
		    }

		    return false;
		}

		public function is_url_404($url){
			return false;
			
			$res = wp_remote_head($url, array('timeout' => 3 ));

			if(is_wp_error($res)){
				$url_header = @get_headers($url);

				if(preg_match("/200\s+OK/i", $url_header[0])){
					return false;
				}else{
					return true;
				}
			}else{
				if($res["response"]["code"] == 200){
					return false;
				}
			}

			return true;
		}

		public function image_arr_unique($images){
			if(count($images) > 1){
				$arr = array();
				$images_tmp = array();
				foreach ($images as $key => $value) {
					if(!in_array($value["file"], $arr)){
						array_push($arr, $value["file"]);
						array_push($images_tmp, $value);
					}
				}
				return $images_tmp;
			}
			return $images;
		}
	}
?>