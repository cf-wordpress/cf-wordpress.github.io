<?php
	class WpFastestCacheDatabaseCleanup{
		public static function clean($type){
	        global $wpdb;

            $statics = array();

            switch ($type){
            	case "all_warnings":
            		$wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_type = 'revision';");
            		$wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_status = 'trash';");
            		$wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;");
            		$wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;");
            		$wpdb->query("DELETE FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' ;");

            		break;
                case "post_revisions":
                    $wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_type = 'revision';");

                    break;
                case "trashed_contents":
                    $wpdb->query("DELETE FROM `$wpdb->posts` WHERE post_status = 'trash';");

                    break;
                case "trashed_spam_comments":
                    $wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam' OR comment_approved = 'trash' ;");

                    break;
                case "trackback_pingback":
                    $wpdb->query("DELETE FROM `$wpdb->comments` WHERE comment_type = 'trackback' OR comment_type = 'pingback' ;");

                    break;
                case "transient_options":
                    $wpdb->query("DELETE FROM `$wpdb->options` WHERE option_name LIKE '%\_transient\_%' ;");

                    break;
            }

            die(json_encode(array("success" => true)));
		}
	}
?>