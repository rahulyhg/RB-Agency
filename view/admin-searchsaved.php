<?php

$siteurl = get_option('siteurl');
	// Casting Class
	include(rb_agency_BASEREL ."app/casting.class.php");
	include(rb_agency_BASEREL ."ext/easytext.php");

	global $wpdb;

	// Get Options
	$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_agencyname		= $rb_agency_options_arr['rb_agency_option_agencyname'];
		$rb_agency_option_agencyemail	= $rb_agency_options_arr['rb_agency_option_agencyemail'];
		$rb_agency_option_agencyheader	= $rb_agency_options_arr['rb_agency_option_agencyheader'];

	// Declare Hash
	$SearchMuxHash			=  isset($_GET["SearchMuxHash"]) && !empty($_GET["SearchMuxHash"]) ?$_GET["SearchMuxHash"]:""; // Set Hash

	if (isset($_POST['action'])) {

		$SearchID			= isset($_POST['SearchID']) ? $_POST['SearchID']: "";
		$SearchTitle		= isset($_POST['SearchTitle'])?$_POST['SearchTitle']:"";
		$SearchType			= isset($_POST['SearchType'])?$_POST['SearchType']:"";
		$SearchProfileID	= isset($_POST['SearchProfileID'])?$_POST['SearchProfileID']:"";
		$SearchOptions		= isset($_POST['SearchOptions'])?$_POST['SearchOptions']:"";

		// What is action?
		$action = $_POST['action'];

		switch($action) {

			// Add
			case 'addRecord':

				// Ensure a Title is Created
				if (!empty($SearchTitle)) {

					// Create Record
					$insert = "INSERT INTO " . table_agency_searchsaved . " (
						SearchTitle,
						SearchType,
						SearchProfileID,
						SearchOptions
					)" . "VALUES (
						'" . esc_sql($SearchTitle) . "',
						'" . esc_sql($SearchType) . "',
						'" . esc_sql($SearchProfileID) . "',
						'" . esc_sql($SearchOptions) . "'
					)";

					$results = $wpdb->query($insert);
					$lastid = $wpdb->insert_id;

					echo '<div id="message" class="updated"><p>Search saved successfully! <a href="'. admin_url("admin.php?page=". $_GET['page']) .'&action=emailCompose&SearchID='. $lastid .'&SearchMuxHash='.RBAgency_Common::generate_random_string(8).'">Send Email</a></p></div>'; 

				} else {

					echo ('<div id="message" class="error"><p>Error creating record, please ensure you have filled out all required fields.</p></div>'); 
				}

			break;


			// Delete bulk
			case 'deleteRecord':

				foreach($_POST as $SearchID) {
					if($SearchID !="deleteRecord" &&  $SearchID !="Delete"){
						$wpdb->query("DELETE FROM " . table_agency_searchsaved . " WHERE SearchID=$SearchID");
					}
				}

				echo ('<div id="message" class="updated"><p>Profile deleted successfully!</p></div>');

			break;


			// Email
			case 'emailSend':

				if (!empty($SearchID)) {
					echo RBAgency_Casting::cart_email_send_process();
				}

			break;

		}


	} elseif (isset($_GET['action']) && $_GET['action'] == "deleteRecord") {
	/* 
	 * Delete Email
	 */

		$SearchID = $_GET['SearchID'];

		// Verify Record
		$queryDelete = "SELECT * FROM ". table_agency_searchsaved ." WHERE SearchID =  \"". $SearchID ."\"";
		$resultsDelete = $wpdb->get_results($wpdb->prepare($queryDelete), ARRAY_A);

		foreach ($resultsDelete as $dataDelete) {
			// Remove Casting
			$delete = "DELETE FROM " . table_agency_searchsaved . " WHERE SearchID = \"". $SearchID ."\"";
			$results = $wpdb->query($delete);
			echo ('<div id="message" class="updated"><p>Record deleted successfully!</p></div>');
		}
    } elseif ((isset($_GET['action']) && $_GET['action'] == "emailCompose") && isset($_GET['SearchID'])) {
	/* 
	 * Compose Email
	 */

		$SearchID = $_GET['SearchID'];

		$dataSearchSavedMux = $wpdb->get_row("SELECT * FROM " . table_agency_searchsaved_mux ." WHERE SearchID=".$SearchID." ", ARRAY_A ,0);

		?>
		<div style="width:500px; float:left;">
		 <h2><?php echo __("Search Saved", rb_agency_TEXTDOMAIN); ?></h2>
		  <form method="post" enctype="multipart/form-data" action="<?php echo admin_url("admin.php?page=". $_GET['page'])."&SearchID=".$_GET['SearchID']."&SearchMuxHash=".$_GET["SearchMuxHash"]; ?>">
		   <input type="hidden" name="action" value="cartEmail" />
		   <div><label for="SearchMuxToEmail"><strong>From Name:(Leave as blank to use admin name)</strong></label><br/><input  style="width:300px;" type="text" id="SearchMuxFromName" name="SearchMuxFromName" value="<?php echo $dataSearchSavedMux["SearchMuxToName"]; ?>" /></div>
		   <div><label for="SearchMuxToEmail"><strong>From Email:(Leave as blank to use admin email)</strong></label><br/><input  style="width:300px;" type="text" id="SearchMuxFromEmail" name="SearchMuxFromEmail" value="<?php echo $dataSearchSavedMux["SearchMuxToEmail"]; ?>" /></div>
		   <div><label for="SearchMuxToName"><strong>Send to Name:</strong></label><br/><input style="width:300px;" type="text" id="SearchMuxToName" name="SearchMuxToName" value="<?php echo $dataSearchSavedMux["SearchMuxToName"]; ?>" /></div>
		   <div><label for="SearchMuxToEmail"><strong>Send to Email:</strong></label><br/><input  style="width:300px;" type="text" id="SearchMuxToEmail" name="SearchMuxToEmail" value="<?php echo $dataSearchSavedMux["SearchMuxToEmail"]; ?>" /></div>
		   
		   <div><label for="SearchMuxBccEmail"><strong>Bcc:</strong></label><br/><input  style="width:300px;" type="text" id="SearchMuxBccEmail" name="SearchMuxBccEmail" value="" /></div>
		   
		   <div><label for="SearchMuxSubject"><strong>Subject:</strong></label><br/><input  style="width:300px;" type="text" id="SearchMuxSubject" name="SearchMuxSubject" value="<?php echo $rb_agency_option_agencyname; ?> Casting Cart" /></div>
		   <div><label for="SearchMuxMessage"><strong>Message: (copy/paste: [link-place-holder] )</strong></label><br/>
			<textarea id="SearchMuxMessage" name="SearchMuxMessage" style="width: 500px; height: 300px; "><?php if(!isset($_GET["SearchMuxHash"])){ echo @$dataSearchSavedMux["SearchMuxMessage"];}else{echo @"Click the following link (or copy and paste it into your browser): [link-place-holder]";} ?></textarea>
			</div>
		   <p class="submit">
			   <input type="hidden" name="SearchID" value="<?php echo $SearchID; ?>" />
			   <input type="hidden" name="action" value="emailSend" />
			   <input type="submit" name="submit" value="Send Email" class="button-primary" />
		   </p>

		  </form>
		</div>
		<?php

		$query = "SELECT search.SearchTitle, search.SearchProfileID, search.SearchOptions, searchsent.SearchMuxHash FROM ". table_agency_searchsaved ." search LEFT JOIN ". table_agency_searchsaved_mux ." searchsent ON search.SearchID = searchsent.SearchID WHERE search.SearchID = \"". $_GET["SearchID"]."\"";
	
		/*
		TODO: CLeanup
		$SearchMuxHash = $dataSearchSavedMux["SearchMuxHash"];
		$query = "SELECT search.SearchTitle, search.SearchProfileID, search.SearchOptions, searchsent.SearchMuxHash FROM ". table_agency_searchsaved ." search LEFT JOIN ". table_agency_searchsaved_mux ." searchsent ON search.SearchID = searchsent.SearchID WHERE searchsent.SearchMuxHash = \"". $SearchMuxHash ."\"";
	  	*/
		$data =  $wpdb->get_row($query);
		$query ="SELECT * FROM ". table_agency_profile ." profile, "
				. table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN ("
				.(isset($data->SearchProfileID)? $data->SearchProfileID:"''").") ORDER BY ProfileContactNameFirst ASC";
		$results = $wpdb->get_results($query, ARRAY_A);
		$count = $wpdb->num_rows;

		 ?>
		<div style="padding:10px;max-width:580px;float:left;">
			<b>Preview: <?php echo  $count." Profile(s)"; ?></b>
				<div style="height:550px; width:580px; overflow-y:scroll;">
					<?php
					foreach ($results as $data2 ) {
					echo " <div style=\"background:black; color:white;float: left; max-width: 100px; height: 180px; margin: 2px; overflow:hidden;  \">";
					echo " <div style=\"margin:3px;max-width:250px; max-height:300px; overflow:hidden;\">";
					echo stripslashes($data2['ProfileContactNameFirst']) ." ". stripslashes($data2['ProfileContactNameLast']);
					echo "<br /><a href=\"". rb_agency_PROFILEDIR . $data2['ProfileGallery'] ."/\" target=\"_blank\">";
					echo "<img style=\"max-width:130px; max-height:150px; \" src=\"". rb_agency_UPLOADDIR ."". $data2['ProfileGallery'] ."/". $data2['ProfileMediaURL'] ."\" /></a>";
					echo "</div>\n";
					echo "</div>\n";
					}
					?>
				</div>
		</div>
		<?php

	}else {
	/* 
	 * View
	 */

		?>
		<div style="clear:both"></div>

		<div class="wrap" style="min-width: 1020px;">
		 <div id="rb-overview-icon" class="icon32"></div>
		 <h2>Profile Search</h2>

			<?php

			if (isset($_GET["action"]) && $_GET["action"] == "searchSave") { // Add to Cart

				// Set Casting Cart Session
				if (isset($_SESSION['cartArray'])) {

					$cartArray = $_SESSION['cartArray'];
					$cartString = ltrim(implode(",", array_unique($cartArray)),",");

					?>
					<h3 class="title">Save Search and Email</h3>


				   <form method="post" enctype="multipart/form-data" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">
				   <table class="form-table">
				   <tbody>
					   <tr valign="top">
						   <th scope="row">Group Title:</th>
						   <td>
							   <input type="text" id="SearchTitle" name="SearchTitle" value="<?php echo isset($CastingCompany)?$CastingCompany:""; ?>" />
						   </td>
					   </tr>
					   <tr valign="top">
						   <th scope="row">Profiles:</th>
						   <td>

								<?php
										   
								$query = "SELECT * FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (".$cartString.") ORDER BY ProfileContactNameFirst ASC";
								$results = $wpdb->get_results($query, ARRAY_A);
								$count = $wpdb->num_rows;

								

								foreach ($results as $data) {

									echo " <div style=\"float: left; width: 80px; height: 100px; margin-right: 5px; overflow: hidden; \">". stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "<br /><a href=\"". rb_agency_PROFILEDIR . $data['ProfileGallery'] ."/\" target=\"_blank\"><img style=\"width: 80px; \" src=\"". rb_agency_UPLOADDIR ."". $data['ProfileGallery'] ."/". $data['ProfileMediaURL'] ."\" /></a></div>\n";

								}

								?>
							<input type="hidden" name="SearchProfileID" value="<?php echo $cartString; ?>" />
						   </td>
					   </tr>
					   </tbody>
				   </table>
				   <p class="submit">
					Click Save to get the email code<br />
					   <input type="hidden" name="action" value="addRecord" />
					   <input type="submit" name="Submit" value="Save Search" class="button-primary" />
				   </p>
				   </form>

					<hr />

					<?php
			   } else {

					echo "Session expired. Please search again.";

				}
		   } // End Serach Save


	?>
  <div style="clear:both"></div>
		<h3 class="title">Recently Saved Searches</h3>

		<?php  
		$sqldata = "";

		if(isset($_REQUEST["m"]) && $_REQUEST['m'] == '1' ) {
			// Message of successful mail form mass email 
			echo "<div id=\"message\" class=\"updated\"><p>Email Messages successfully sent!</p></div>";
		}

		$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_option_locationtimezone = (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];

		// Sort By
		$sort = "";
		if (isset($_GET['sort']) && !empty($_GET['sort'])){
			$sort = $_GET['sort'];
		} else {
			$sort = "search.SearchDate";
		}

		// Sort Order
		$dir = "";
		if (isset($_GET['dir']) && !empty($_GET['dir'])){
			$dir = $_GET['dir'];
			if ($dir == "desc" || !isset($dir) || empty($dir)){
				$sortDirection = "desc";
			} else {
				$sortDirection = "desc";
			}
		} else {
			$sortDirection = "desc";
			$dir = "desc";
		}

		// Filter
		$filter = "WHERE search.SearchID > 0 ";
		if (isset($_GET['SearchTitle']) && !empty($_GET['SearchTitle'])){
			$selectedTitle = $_GET['SearchTitle'];
			$query .= "&SearchTitle=". $selectedTitle ."";
			$filter .= " AND search.SearchTitle='". $selectedTitle ."'";
		}

		//Paginate
		$sqldata  = "SELECT * FROM ". table_agency_searchsaved ." search LEFT JOIN ". table_agency_searchsaved_mux ." searchsent ON search.SearchID = search.SearchID ". $filter  .""; // number of total rows in the database
		$results=  $wpdb->get_results($sqldata);
		
		$items =$wpdb->num_rows; // number of total rows in the database
		if($items > 0) {

			$p = new rb_agency_pagination;
			$p->items($items);
			$p->limit(50); // Limit entries per page
			$p->target("admin.php?page=". (isset($_GET['page'])?$_GET['page']:"") .(isset($query)?$query:""));
			$p->currentPage(isset($_GET[isset($p->paging)?$p->paging:0])?$_GET[$p->paging]:0); // Gets and validates the current page
			$p->calculate(); // Calculates what to show
			$p->parameterName('paging');
			$p->adjacents(1); //No. of page away from the current page

			if(!isset($_GET['paging'])) {
				$p->page = 1;
			} else {
				$p->page = $_GET['paging'];
			}

			//Query for limit paging

			$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;

		} else {
			$limit = "";
		}

		?>
		<div class="tablenav">
			<div class='tablenav-pages'>
				<?php
				if($items > 0) {
					echo $p->show();  // Echo out the list of paging. 
				}
				?>
			</div>
		</div>

		<table cellspacing="0" class="widefat fixed">
			<thead>
				<tr>
					<td style="width: 360px;" nowrap="nowrap">
						<form method="GET" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">
						 <input type='hidden' name='page_index' id='page_index' value='<?php echo isset($_GET['page_index'])?$_GET['page_index']:""; ?>' />  
						 Search by : 
						 Title: <input type="text" name="SearchTitle" value="<?php echo isset($SearchTitle)?$SearchTitle:""; ?>" style="width: 100px;" />
							<input type="submit" value="Filter" class="button-primary" />
						</form>
					</td>
					<td style="width: 300px;" nowrap="nowrap">
						<form method="GET" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">
						 <input type='hidden' name='page_index' id='page_index' value='<?php echo isset($_GET['page_index'])?$_GET['page_index']:""; ?>' />  
						 <input type='hidden' name='page' id='page' value='<?php echo $_GET['page']; ?>' />
						 <input type="submit" value="Clear Filters" class="button-secondary" />
						</form>
					</td>
					<td>&nbsp;</td>
				</tr>
		</thead>
		</table>

		<form method="post" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">	
		<table cellspacing="0" class="widefat fixed">
		<thead>
			<tr class="thead">
				<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
				<th class="column" scope="col" style="width:50px;"><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=SearchID&dir=<?php echo $sortDirection; ?>">ID</a></th>
				<th class="column" scope="col" style="width:200px;"><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=SearchTitle&dir=<?php echo $sortDirection; ?>">Title</a></th>
				<th class="column" scope="col" style="width:80px;"><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=SearchDate&dir=<?php echo $sortDirection; ?>">Profiles</a></th>
				<th class="column" scope="col">History (Sent/To/Link)</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
				<th class="column" scope="col">ID</th>
				<th class="column" scope="col">Title</th>
				<th class="column" scope="col">Profiles</th>
				<th class="column" scope="col">History</th>
			</tr>
		</tfoot>
		<tbody>

		<?php

		$query2 = "SELECT search.SearchID, search.SearchTitle, search.SearchProfileID, search.SearchDate FROM ". table_agency_searchsaved ." search ". $filter  ." ORDER BY $sort $dir $limit";
		//$query2 = "SELECT search.SearchID, search.SearchTitle, search.SearchProfileID, search.SearchOptions, search.SearchDate FROM ". table_agency_searchsaved_mux ." searchsent LEFT JOIN ". table_agency_searchsaved ." search ON searchsent.SearchID = search.SearchID ". $filter  ." ORDER BY $sort $dir $limit";

		$results2 = $wpdb->get_results($query2, ARRAY_A);
		$count2 = $wpdb->num_rows;

		foreach ($results2 as $data2) {
			$SearchID = $data2['SearchID'];
			$SearchTitle = stripslashes($data2['SearchTitle']);
			$SearchProfileID = stripslashes($data2['SearchProfileID']);
			$SearchDate = stripslashes($data2['SearchDate']);
			$query3 = "SELECT SearchID,SearchMuxHash, SearchMuxToName, SearchMuxToEmail, SearchMuxSent FROM ". table_agency_searchsaved_mux ." WHERE SearchID = %d";
			$results3 = $wpdb->get_results($wpdb->prepare($query3, $SearchID), ARRAY_A);
			$count3 = $wpdb->num_rows;

		?>
		<tr<?php echo isset($rowColor)?$rowColor:""; ?>>
			<th class="check-column" scope="row">
				<input type="checkbox" value="<?php echo $SearchID; ?>" class="administrator" id="<?php echo $SearchID; ?>" name="<?php echo $SearchID; ?>"/>
			</th>
			<td>
				<?php echo $SearchID; ?>
			</td>
			<td>
				<?php echo $SearchTitle; ?>
				<div class="row-actions">
				<?php
				if($count3<=0){
				?>
					<span class="send"><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=emailCompose&SearchID=<?php echo $SearchID."&SearchMuxHash=".RBAgency_Common::generate_random_string(8); ?>">Create Email</a> | </span>
				<?php
				}else{
				?>
						<span class="send"><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=emailCompose&SearchID=<?php echo $SearchID; ?>">Create Email</a> | </span>
				<?php } ?>
						<span class="delete"><a class='submitdelete' title='Delete this Record' href='<?php echo admin_url("admin.php?page=". $_GET['page']); ?>&amp;action=deleteRecord&amp;SearchID=<?php echo $SearchID; ?>' onclick="if ( confirm('You are about to delete this record\'\n \'Cancel\' to stop, \'OK\' to delete.') ) { return true;}return false;">Delete</a></span>
				</div>
			</td>
			<td>
				<?php  // echo $SearchProfileID; ?>
			</td>
			<td>
				<?php
				$pos = 0;
				foreach ($results3 as $data3 ) {
				$pos++;
					 if($pos == 1){
						echo "Link: <a href=\"". get_bloginfo("url") ."/client-view/". $data3["SearchMuxHash"] ."/\" target=\"_blank\">". get_bloginfo("url") ."/client-view/". $data3["SearchMuxHash"] ."/</a><br />\n";
					}
					echo "(". rb_agency_makeago(rb_agency_convertdatetime( $data3["SearchMuxSent"]), $rb_agency_option_locationtimezone) .") ";
					echo "<strong>". $data3["SearchMuxToName"]."&lt;".$data3["SearchMuxToEmail"]."&gt;"."</strong> ";
					echo "<br/>";
				}
				//mysql_free_result($results2);
				if ($count3 < 1) {
					echo "Not emailed yet\n";
				}
				?>
			</td>
		</tr>
		<?php
		}
		//	mysql_free_result($results2);
			if ($count2 < 1) {
				if (isset($filter)) { 
		?>
		<tr>
			<th class="check-column" scope="row"></th>
			<td class="name column-name" colspan="3">
				<p>No profiles found with this criteria.</p>
			</td>
		</tr>
		<?php
				} else {
		?>
		<tr>
			<th class="check-column" scope="row"></th>
			<td class="name column-name" colspan="3">
				<p>There aren't any Profiles loaded yet!</p>
			</td>
		</tr>
		<?php
				}
		?>
		<?php } ?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class='tablenav-pages'>
			<?php 
			if($items > 0) {
				echo $p->show();  // Echo out the list of paging. 
			}
			?>
		</div>
	</div>

	<p class="submit">
		<input type="hidden" value="deleteRecord" name="action" />
		<input type="submit" value="<?php echo __('Delete','rb_agency_profiles'); ?>" class="button-primary" name="submit" />		
	</p>
	</form>
	<?php 
	} // End All
	?>