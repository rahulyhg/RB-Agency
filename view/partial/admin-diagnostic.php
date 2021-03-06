<?php


	require_once(RBAGENCY_PLUGIN_DIR ."diagnostic.php");
/*

	// Check Database
		$database = RBAgency_Diagnostic::has_database_permission();
		echo $database;
*/


?>
		<div class="welcome-panel-column" style="width: 40%; ">
			<h3><?php _e("WordPress Environment Check",RBAGENCY_TEXTDOMAIN); ?></h3>

				<table class="form-table" style="width: 100%">

				<tr valign="top">
					<th scope="row"><label><?php _e("PHP Version", RBAGENCY_TEXTDOMAIN); ?></label></th>
					<td class="installation_item_cell">
						<strong><?php echo phpversion(); ?></strong>
					</td>
					<td>
						<?php
							if(version_compare(phpversion(), '5.0.0', '>')){
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/checked.png\"/>";
							} else {
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/remove.png\"/>";
								echo "<span class=\"installation_item_message\">". __("RB Agency requires PHP 5 or above.", RBAGENCY_TEXTDOMAIN) ."</span>";
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e("MySQL Version", RBAGENCY_TEXTDOMAIN); ?></label></th>
					<td class="installation_item_cell">
						<strong><?php echo $wpdb->db_version();?></strong>
					</td>
					<td>
						<?php
							if(version_compare($wpdb->db_version(), '5.0.0', '>')){
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/checked.png\"/>";
							}
							else {
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/remove.png\"/>";
								echo "<span class=\"installation_item_message\">". __("RB Agency requires MySQL 5 or above.", RBAGENCY_TEXTDOMAIN) ."</span>";
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e("WordPress Version", RBAGENCY_TEXTDOMAIN); ?></label></th>
					<td class="installation_item_cell">
						<strong><?php echo get_bloginfo("version"); ?></strong>
					</td>
					<td>
						<?php
							if(version_compare(get_bloginfo("version"), '3.0', '>')){
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/checked.png\"/>";
							}
							else {
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/remove.png\"/>";
								echo "<span class=\"installation_item_message\">". printf(__("RB Agency requires WordPress v%s or greater.", RBAGENCY_TEXTDOMAIN), RBAGENCY_VERSION_WP_MIN) ."</span>";
							}
						?>
					</td>
				</tr>
			</table>

		</div>

		<div class="welcome-panel-column" style="width: 50%; margin-left: 50px;">
			<h3><?php _e("RB Agency Check"); ?></h3>

				<table class="form-table" >

				<tr valign="top">
					<th scope="row"><label><?php _e("Folder is Writable", RBAGENCY_VERSION_WP_MIN); ?></label></th>
					<td class="installation_item_cell">
						<strong>Uploads Folder</strong>
					</td>
					<td>
						<?php

							// Check Folder
							$folderWritable = RBAgency_Diagnostic::check_upload_folder();

							if($folderWritable){
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/checked.png\"/>";
							}
							else {
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/remove.png\"/>";
								echo "<span class=\"installation_item_message\">". __("Uploads folder is not writable! Contact your web host.", RBAGENCY_TEXTDOMAIN) ."</span>";
							}
						?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label><?php _e("Stylesheet Exists", RBAGENCY_VERSION_WP_MIN); ?></label></th>
					<td class="installation_item_cell">
						<strong>Styles.css</strong>
					</td>
					<td>
						<?php

							// Check Folder
							$stylesheetExists = RBAgency_Diagnostic::check_stylesheet_exists();

							if($stylesheetExists){
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/checked.png\"/>";
							}
							else {
								echo "<img src=\"". RBAGENCY_PLUGIN_URL ."assets/img/remove.png\"/>";
								echo "<span class=\"installation_item_message\">". __("File does not exist.  Go to <a href='admin.php?page=rb_agency_settings&ConfigID=2'>Agency > Settings > Styles</a> and initialize the file.", RBAGENCY_TEXTDOMAIN) ."</span>";
							}
						?>
					</td>
				</tr>


			</table>

		</div>
			<?php
