<div template-id="wpfc-modal-mobiletheme" style="top: 10.5px; left: 226px; position: absolute; padding: 6px; height: auto; width: 360px; z-index: 10001;display:none;">
	<div style="height: 100%; width: 100%; background: none repeat scroll 0% 0% rgb(0, 0, 0); position: absolute; top: 0px; left: 0px; z-index: -1; opacity: 0.5; border-radius: 8px;">
	</div>
	<div style="z-index: 600; border-radius: 3px;">
		<div style="font-family:Verdana,Geneva,Arial,Helvetica,sans-serif;font-size:12px;background: none repeat scroll 0px 0px rgb(255, 161, 0); z-index: 1000; position: relative; padding: 2px; border-bottom: 1px solid rgb(194, 122, 0); height: 35px; border-radius: 3px 3px 0px 0px;">
			<table width="100%" height="100%">
				<tbody>
					<tr>
						<td valign="middle" style="vertical-align: middle; font-weight: bold; color: rgb(255, 255, 255); text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.5); padding-left: 10px; font-size: 13px; cursor: move;">Mobile Theme Switcher</td>
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
					<div class="wiz-cont" style="">
						<h1>Choose a Theme</h1>		
						<p>You can choose a mobile theme if you want the mobile devices to see a mobile theme.</p>
						
						<div class="wiz-input-cont">
							<label class="mc-input-label" style="margin-right: 5px;">
								<select id="wpFastestCacheMobileTheme_themename" name="wpFastestCacheMobileTheme_themename" style="width:100%;">
										<option value="">I don't wanna use</option>
									<?php
										$themes = wp_get_themes();
										
										foreach ( $themes as $key => $theme ) {
											$name = $theme->get('Name');
											
											if($wpFastestCacheMobileTheme_themename == $key){
												echo '<option value="'.$key.'" selected>'.$name.'</option>';
											}else{
												echo '<option value="'.$key.'">'.$name.'</option>';
											}
										}
									?>
								</select>
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="window-buttons-wrapper" style="padding: 0px; display: inline-block; width: 100%; border-top: 1px solid rgb(255, 255, 255); background: none repeat scroll 0px 0px rgb(222, 222, 222); z-index: 999; position: relative; text-align: right; border-radius: 0px 0px 3px 3px;">
			<div style="padding: 12px; height: 23px;text-align: center;">
				<button class="wpfc-dialog-buttons buttons" type="button" action="close">
					<span>OK</span>
				</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery("#wpFastestCacheMobileTheme").click(function(){
		if(jQuery(this).is(':checked')){
			Wpfc_New_Dialog.dialog("wpfc-modal-mobiletheme", {close: function(){
				Wpfc_New_Dialog.set_values_from_tmp_to_real();
				Wpfc_New_Dialog.clone.remove();
			}}, function(dialog){
				dialog.clone.find("select").val(jQuery("#wpFastestCacheMobileTheme_themename").val());
			});

			Wpfc_New_Dialog.show_button("close");
		}
	});

</script>
