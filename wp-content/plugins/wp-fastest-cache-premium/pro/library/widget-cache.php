<?php
	class WpfcWidgetCache{
		public static function action(){
			add_filter('widget_display_callback', array("WpfcWidgetCache", "create_cache"), 10, 3);
		}
		
		public static function add_filter_admin(){
			add_filter('widget_update_callback', array("WpfcWidgetCache", "widget_update"), 5, 3);
			add_action('in_widget_form', array("WpfcWidgetCache", 'in_widget_form'), 5, 3);
		}

		public static function in_widget_form($widget, $return, $instance){
	        $wpfcnot = isset( $instance['wpfcnot'] ) ? $instance['wpfcnot'] : '';

	        ?>
	            <p>
	                <input class="checkbox" type="checkbox" id="<?php echo $widget->get_field_id('wpfcnot'); ?>" name="<?php echo $widget->get_field_name('wpfcnot'); ?>" <?php checked( true , $wpfcnot ); ?> />
	                <label for="<?php echo $widget->get_field_id('wpfcnot'); ?>">
	                    <?php _e('Don\'t cache this widget'); ?>
	                </label>
	            </p>
	        <?php
		}

		public static function widget_update($instance, $new_instance){
			$GLOBALS["wp_fastest_cache"]->rm_folder_recursively($GLOBALS["wp_fastest_cache"]->getWpContentDir("/cache/wpfc-widget-cache/"));

		    if(isset($new_instance['wpfcnot'])){
		        $instance['wpfcnot'] = 1;
		    }else{
		    	if(isset($instance['wpfcnot'])){
		    		unset($instance['wpfcnot']);
		    	}
		    }
		 
		    return $instance;
		}

		public static function create_cache($instance, $widget, $args){
			if(!isset($args["widget_id"]) || !$args["widget_id"]){
				return $instance;
			}

			if($instance === false){
				return $instance;
			}

			// to return instance if not to cache widget
			if(isset($instance["wpfcnot"])){
				return $instance;
			}

			// to exclude WooCommerce Product Categories automatically if show_children_only has been set
			if(isset($instance["show_children_only"])){
				return $instance;
			}

			// to exclude fixed widget Q2W3 Fixed Widget
			if(isset($instance["q2w3_fixed_widget"])){
				return $instance;
			}

			// to exclude Ninja Forms
			if(preg_match("/^ninja_forms_widget/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude WPML Multilingual Language Switcher
			if(preg_match("/^icl_lang_sel_widget/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude Yuzo Related Posts
			if(preg_match("/^yuzo_widget/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude Amazon Affiliate for WordPress
			if(preg_match("/^aawp_widget_/i", $args["widget_id"])){
				return $instance;
			}

			// Flagman theme
			if(preg_match("/^ct_slider_widget_/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude woocommerce product filter
			if(preg_match("/^woof_widget/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude woocommerce product filter
			if(preg_match("/^woocommerce_price_filter/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude Bridge Woocommerce Dropdown Cart
			if(preg_match("/^woocommerce-dropdown-cart/i", $args["widget_id"])){
				return $instance;
			}

			// to exclude woocommerce product filter
			// https://mihajlovicnenad.com/product-filter/
			if(preg_match("/^prdctfltr/i", $args["widget_id"])){
				return $instance;
			}


			$create_cache = false;
			$path = $GLOBALS["wp_fastest_cache"]->getWpContentDir("/cache/wpfc-widget-cache/".$args["widget_id"].".html");

			//to get cache
			if(file_exists($path)){
				if($data = @file_get_contents($path)){
					echo $data;
					return false;
				}
			}

			//to get the content of Widget
	        ob_start();
	        $widget->widget( $args, $instance );
	        $cached_widget = ob_get_clean();

	        //to create cache
	        if($cached_widget){
	        	if(!is_dir($GLOBALS["wp_fastest_cache"]->getWpContentDir("/cache/wpfc-widget-cache"))){
	        		if(@mkdir($GLOBALS["wp_fastest_cache"]->getWpContentDir("/cache/wpfc-widget-cache"), 0755, true)){
	        			$create_cache = true;
	        		}
	        	}else{
	        		$create_cache = true;
	        	}

	        	//to exclude the widgets which contains nonce value
	        	//<input type="hidden" id="poll_1_nonce" name="wp-polls-nonce" value="fdd28cece7" />
	        	if(preg_match("/<input[^\>]+hidden[^\>]+nonce[^\>]+>/", $cached_widget) || preg_match("/<input[^\>]+nonce[^\>]+hidden[^\>]+>/", $cached_widget)){
	        		$create_cache = false;
	        	}

	        	if($create_cache){
					@file_put_contents($path, $cached_widget);
	        	}
	        }

	        echo $cached_widget;
	        return false;
		}
	}
?>