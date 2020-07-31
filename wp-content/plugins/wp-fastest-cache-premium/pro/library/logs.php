<?php
	class WpFastestCacheLogs{
		private $name = "";
		private $type = "";
		private $limit = 0;
		private $logs = array();

		public function __construct($type){
			$this->type = $type;

			if($this->type == "delete"){
				$this->name = "WpFcDeleteCacheLogs";
				$this->limit = 25;
			}

			$this->setLogs();
		}

		public function action($from = ""){
			if($this->type == "delete"){
				$log = new stdClass();
				$log->date = date("d-m-Y @ H:i:s", current_time('timestamp'));
				if($from && $from->prefix != "all"){
					$log->via = new stdClass();
					$log->via->function = "- Cache Timeout / ".$from->prefix." ".$from->content;
				}else{
					$log->via = $this->getVia();
				}
			}

			if($log->via !== false){
				if(!in_array($log, $this->logs)){
					array_unshift($this->logs, $log);

					if($this->limit < count($this->logs)){
						array_pop($this->logs);
					}

					$this->updateDB();
				}
			}
		}

		public function updateDB(){
			if(get_option($this->name)){
				update_option($this->name, json_encode($this->logs));
			}else{
				add_option($this->name, json_encode($this->logs), null, "no");
			}
		}

		public function setLogs(){
			if($json = get_option($this->name)){
				$this->logs = (array)json_decode($json);
			}
		}

		public function getVia(){
			$arr = array();
			$via = debug_backtrace();

			if(isset($via[8]) && ($via[8]["function"] == "wp_set_comment_status") && ($via[2]["function"] == "deleteHomePageCache") && ($via[3]["function"] == "singleDeleteCache")){
				return false;
			}

			if($via[4]["function"] == "clear_cache_after_woocommerce_checkout_order_processed"){
				$arr["args"] = array();
				$arr["function"] = $via[4]["function"];

				$order = wc_get_order($via[4]["args"][0]);

				if($order){
					foreach($order->get_items() as $item_key => $item_values ){
						array_push($arr["args"], $item_values->get_product_id());
					}
				}

				
			}else if($via[3]["function"] == "on_all_status_transitions"){
				$arr["args"] = $via[3]["args"];
				$arr["function"] = $via[3]["function"];
			}else if($via[4]["function"] == "deleteCssAndJsCacheToolbar"){
				$arr["function"] = "deleteCssAndJsCacheToolbar";
			}else if(isset($via[7]) && ($via[7]["function"] == "wp_set_comment_status")){
				$arr["args"] = $via[7]["args"];
				$arr["function"] = $via[7]["function"];
			}else{
				$arr["function"] = $via[3]["function"];

				if(isset($via[3]["file"]) && $via[3]["file"]){
					if(preg_match("/\/plugins\/([^\/]+)\//", $via[3]["file"], $plugin_name)){
						$arr["function"] = $arr["function"]." (".$plugin_name[1].")";
					}
				}
			}

			return $arr;
		}

		//to detect which function called deleteCache()
		public function decodeVia($data){
			if($data->function == "setSchedule"){
				return "- Cache Timeout";
			}else if($data->function == "optionsPageRequest"){
				return "- Delete Cache Button";
			}else if($data->function == "deleteCssAndJsCache"){
				return "- Delete Cache and Minified CSS/JS Button";
			}else if($data->function == "on_all_status_transitions"){
				$type = $data->args[2]->post_type;
				if($data->args[0] == "publish" && $data->args[1] == "publish"){
					return "<span>- The ".$type." has been updated</span><br><span>- #ID:".$data->args[2]->ID."</span><br><span>- One cached file has been removed</span>";
				}else if($data->args[0] == "publish" && $data->args[1] != "publish"){
					return "<span>- New ".$type." has been published</span><br><span>- ".$type." ID:".$data->args[2]->ID."</span>";
				}
				return "<span>- The ".$type." status has been changed.</span><br><span>- ".$data->args[1]." > ".$data->args[0]."</span><span> #ID:".$data->args[2]->ID."</span>";
			}else if($data->function == "wp_set_comment_status"){
				if(isset($data->args[0]->comment_ID)){
					return "<span>- Comment has been marked as </span>"."<span>".$data->args[1]."</span><br><span>- Comment ID: ".$data->args[0]->comment_ID."</span><br><span>- One cached file has been removed</span>";
				}else{
					return "<span>- Comment has been marked as </span>"."<span>".$data->args[1]."</span><br><span>- Comment ID: ".$data->args[0]."</span><br><span>- One cached file has been removed</span>";
				}
			}else if($data->function == "clear_cache_after_woocommerce_checkout_order_processed"){
				if(isset($data->args) && is_array($data->args)){
					return "<span>- New item has been ordered</span><br><span>- Product ID: ".implode(",", $data->args)."</span>";
				}
			}

			return $data->function;
		}

		public function printLogs(){
			?>
			<div id="delete-logs" style="display:none; padding-bottom:10px;">

				<h2 style="padding-left:20px;padding-bottom:10px;">Delete Cache Logs</h2>

				<table style="border:0;border-top:1px solid #DEDBD1;border-radius:0;margin-left: 20px;width: 95%;box-shadow:none;border-bottom: 1px solid #E5E5E5;" class="widefat fixed">
					<thead>
						<tr>
							<th style="border-left:1px solid #DEDBD1;border-top-left-radius:0;" scope="col">Date</th>
							<th style="border-right:1px solid #DEDBD1;border-top-right-radius:0;" scope="col">Via</th>
						</tr>
					</thead>
						<tbody>
							<?php if(count($this->logs) > 0){ ?>
								<?php foreach ($this->logs as $key => $log) { ?>
									<tr>
										<th style="vertical-align:top;border-left:1px solid #DEDBD1;" scope="row"><?php echo $log->date;?></th>
										<th style="border-right:1px solid #DEDBD1;"><?php echo isset($log->via) ? $this->decodeVia($log->via) : ""; ?></th>
									</tr>
								<?php } ?>
							<?php }else{ ?>
									<tr>
										<th style="border-left:1px solid #DEDBD1;" scope="row"><label>No Log</label></th>
										<th style="border-right:1px solid #DEDBD1;"></th>
									</tr>
							<?php } ?>
						</tbody>
				</table>
			</div>
			<script>
				jQuery("#container-show-hide-logs").show();

				jQuery("#show-delete-log, #hide-delete-log").click(function(e){
					if(e.target.id == "show-delete-log"){
						jQuery(e.target).hide();
						jQuery("#hide-delete-log").show();
						jQuery("#delete-logs").show();
						jQuery("div.tab2 form.delete-line").hide();
						jQuery("#delete-cache-h2").hide();
						jQuery("#wpfc-cache-statics").hide();
						jQuery("#cache-statics-h2").hide();
					}else if(e.target.id == "hide-delete-log"){
						jQuery(e.target).hide();
						jQuery("#delete-logs").hide();
						jQuery("#show-delete-log").show();
						jQuery("div.tab2 form.delete-line").show();
						jQuery("#delete-cache-h2").show();
						jQuery("#wpfc-cache-statics").show();
						jQuery("#cache-statics-h2").show();
					}
				});
			</script>
			<?php
		}
	}
?>