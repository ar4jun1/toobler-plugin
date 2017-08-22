<?php
/*
  Plugin Name: Tribe Event Atteendee Exporter
  Plugin URI: http://www.toobler.com
  Description:  Wordpress plugin to export Tribe Events Attendee List.
  Version: 1.0
  Author: Ajith
  Author URI: http://www.toobler.com
  
  */

add_action('admin_menu', 'live_create_menu');
$root = dirname(__FILE__) . '/../../..';
require_once($root . '/wp-admin/includes/user.php');
include('class-rs_csv_helper.php');
/**
 *
 */


/**
* Adding metabox for removing unnecessary Attendee informations from csv file
* @param null
* Edited on 21-08-17 : AJN
*/ 


add_action( 'save_post', 'my_em_event_attendee_save' ); 
function my_em_event_attendee_save(){

	if(isset($_POST['prep_event_fields']) && $_POST['prep_event_fields'] != null ){
		$eventId 	= $_POST['post_ID'];
		update_post_meta($eventId,'all_export_exclude',$_POST['prep_event_fields']);
	}
}


function my_em_event_attendee_boxes(){ 
	add_meta_box('em-event-attendee', 'Attendee Information', 'my_em_event_attendee_metabox','tribe_events', 'side','low');
}
add_action('add_meta_boxes', 'my_em_event_attendee_boxes');

function my_em_event_attendee_metabox(){ 
	$eventId 			= get_the_ID(); 	//Currnt event id
	$fields 			= array();
	$fields 			= Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_event( $eventId ); // for Getting all fields
	$eventFields 		= array();
	$eventArray 		= array();
	$prep_event_fields_data = array();

	foreach ($fields as $keyfields => $valuefields) {
		$eventFields['slug'] 	= $valuefields->slug;
		$eventFields['name'] 	= $valuefields->label;
		$eventArray [] 			= $eventFields;			
	} 
	$prep_event_fields_data = get_post_meta($eventId ,'all_export_exclude',true);
	
	ob_start();  ?>
		<p> Check those fields that do not need to include in all export csv file. </p> <br>
	<?php
	foreach ($eventArray as $eventKey => $eventValue) {   
			?>
			<input type="checkbox" name="prep_event_fields[]" 
				<?php 
					if(in_array($eventValue['slug'], $prep_event_fields_data)) 
						echo "checked";
				?>
				value="<?php echo $eventValue['slug']  ?>"
			>
			<?php echo $eventValue['name'] ?>
			<br>
			<?php 
	}

	$output .= ob_get_contents();
	ob_end_clean();
	print_r($output);
	
}
/*
* Ends here:
* 
*****************/ 

function live_create_menu() {
    add_menu_page('Tribeevents', 'Tribe Export', 'administrator', 'tribe_option', 'tribe_events_export_page');
}

function tribe_events_export_page(){
	$csvpath = getdwpath();

global $woocommerce;
	$AllEvents 				= tribe_get_events (array('posts_per_page' => '-1'));
// echo '<pre>';
// print_r($AllEvents);
// echo '</pre>';
?>
<div class='wrap'>
<form method="post" name="exportform" onsubmit="return validateexportevents();">
	<div>
		<select name="exportselectedevents" id="seloptnid">
			<option value="">Select Action</option>
			<option value="1">Export Selected</option> 
		</select>
		<input type="submit" name="exportselectsubmit" value="Apply">
	</div>
	<table class="widefat" id="eventxports">
	<thead>
	    <tr>
	        <th><input type="checkbox" class="seltall" value="" name="alleventsexport" onclick="selectallevents();"></th>
	        <th>Event Name</th>       
	    </tr>
	</thead>
	<tbody>
	<?php
	foreach ($AllEvents as $keyEvnts => $valueEvnts) {
		
	?>
	   <tr>
	     <td><input type="checkbox" name="allevent[]" class="actionallevents" value="<?php echo $valueEvnts->ID; ?>"></td>
	     <td><?php echo $valueEvnts->post_title; ?></td>
	   </tr>
	  <?php } ?>
	</tbody>
	</table>
</form>
</div>
<script type="text/javascript">
  	/*
  	* Select all events.
  	* @Author Ajith @tblr
  	*/
	function selectallevents(){
		var seltall = jQuery('.seltall').is(':checked');	
		if(seltall){
			 jQuery(".actionallevents").prop('checked', true);
		}else{
			jQuery(".actionallevents").prop('checked', false);
		}
	}
	/*
  	* Validate Export.
  	* @Author Ajith @tblr
	*/
	function validateexportevents(){
		var seloptnid = jQuery('#seloptnid').val();
		if(seloptnid ==''){
			alert('Select An Action.');
			return false;
		}
		if(jQuery('#eventxports').find('input[type=checkbox]:checked').length == 0 && seloptnid !='')
	    {
	     	alert('Please select atleast one Event.');
	     	return false;
	    }else{
	    	return true;
	    }
	}
</script>

<?php
$url 				= getDirCsv('check');
$csvPath 			= getdwpath();

$filename = $url."/Tribe-All-Events-Export.csv";

if (file_exists($filename)) { ?>
   
<?php }

?>

<a href="#0" id="triggerdownload" style="display: none;" onclick="downloadCsvfile()">Download</a>
<script type="text/javascript">
	function downloadCsvfile(){
 		document.location = '<?php echo $csvPath; ?>';
	}
</script>
<?php
	if(isset($_POST['exportselectedevents']) && $_POST['exportselectsubmit'] == 'Apply'){
echo '<pre>';
print_r($_POST);
echo '</pre>';

		global $woocommerce;
		$testing 				= tribe_get_events (array('posts_per_page' => '-1'));
		$AllEventAttendeesExport= Array();
		$titleFields 			= array();
	 	foreach ($testing as $keysas => $valueas) {
	 		$lastKey            = '';
			$fields 			= array();
			$fields 			= Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_event( $valueas->ID  );
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Ticket';	

			// edited on - 21-Aug : AJN 
			$prep_event_fields_data = array();
			$prep_event_fields_data = get_post_meta($valueas->ID ,'all_export_exclude',true);
			// ends here 


			// $AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Primary-Information';	
			// $AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Security Code';
			// $AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Check in';
			foreach ($fields as $keyfields => $valuefields) {
				if(!in_array($valuefields->slug, $prep_event_fields_data)){
					$AllEventAttendeesExport[$valueas->ID]['titles'][] = $valuefields->label;
					$lastKey = $keyfields;
				}
			} 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Id';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Status';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'custommer-name';		
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'custommer-email';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Registration Date';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Evaluation Purchased';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Insurance Purchased';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Rm Created';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'T shirt Purchased';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Total';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Payment Title';
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Notes';
			$items = Tribe__Tickets__Tickets::get_event_attendees( $valueas->ID  );
			foreach ($items as $keyI => $valueI) {	
				$meta_data = get_post_meta( $valueI['attendee_id'], Tribe__Tickets_Plus__Meta::META_KEY, true );

				if(!empty($meta_data)){
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueas->post_title;	
					// $AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = '';
					// $AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['security'];
					// $AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['check_in'];
					$CelKeyMta = '0';
					foreach ($meta_data as $keyMta => $valueMta) {
						if($CelKeyMta <= $lastKey){
							if(!in_array($keyMta, $prep_event_fields_data)){ 
								if($valueMta !='' && isset($valueMta)){
									$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = stripslashes($valueMta);
								}else{
									$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'NULL';
								}
							}
						}
						$CelKeyMta++;
					}
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['order_id'];
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['order_status'];
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchaser_name'];
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchaser_email'];
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchase_time'];
					if($valueI['order_id'] != ''){
					 	$order              = wc_get_order( (int) $valueI['order_id'] );  
		   				$itemvals       	= $order->get_items();
			  		 	$Names              = $meta_data['player-email']; 
				    	//$categoriesexp      = '';
				        $isLfpurchased 		= '0';
				        $isInsurePched 		= '0';
				        $isEvalPurchsd 		= '0';
				        $isTshirtpursd 		= '0';
				        $isEventPdtz		= '0';
						$iSEvFlagz			= '0';
						$EvntQtyz			= '0';
						$MorePlayersz		= '0';
					 	foreach ($itemvals as $atky => $Atendepage) {
							$isEventPdtz    = get_post_meta($Atendepage['product_id'],'_tribe_wooticket_for_event',true);
			                if($isEventPdtz){
			                    $iSEvFlagz = '1';
			                    $EvntQtyz  =  $Atendepage['quantity'];
			                }
			                if($iSEvFlagz == '1' && $EvntQtyz > 1){
			                    $MorePlayersz = '1';
			                }
							$term_listsexp = wp_get_post_terms($Atendepage['product_id'],'product_cat',array('fields'=>'all'));
						  	foreach ( $term_listsexp as $termlstexp ){
				          		$categoriesexp[]  	= $termlstexp->slug;
				               	if ( in_array( 'lifetime', $categoriesexp ) ) {        
				                	$isLfpurchased  = '1';
				               	}
				               	if ( in_array( 'evaluation', $categoriesexp ) ) {        
				                   	$isEvalPurchsd  = '1';
				               	}
				               	if ( in_array( 'insurance', $categoriesexp ) ) {        
				                   	$isInsurePched  = '1';
				               	} 
				               	$categoriesexp      = array();               
				           	}
				           	$product_variation_id   = $Atendepage['variation_id'];
				           	if($product_variation_id > 0){
				               $isTshirtpursd      	= '1';
				               $product_name       	= $Atendepage['name'];
				               $quantity           	= $Atendepage['quantity'];
				               $iSTshirt           	= $product_name.' : '.$quantity;
				           	}
					    }
				      	if($isEvalPurchsd == '1'){
				            $evalplayers       	= get_post_meta( $valueI['order_id'], 'evals_selected_players', true );
				            $evalArray         	= explode(',', $evalplayers );
				            if(!empty($evalplayers)){
				            	if(in_array($Names, $evalArray)){
									$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
				            	}else{
									$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
				            	}
				            }else if($MorePlayersz == '1'){
				            	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Not Tracked';
				            }else{
				            	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
				            }		       		
				       	}else{
				        	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
				       	}
				       	if($isInsurePched == '1'){
		 					$insuplayers       	= get_post_meta( $valueI['order_id'], 'insure_selected_players', true );
		            		$insArray          	= explode(',', $insuplayers );
		            		if(!empty($insuplayers)){
			            		if(in_array($Names, $insArray)){
			              			$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
			            		}else{
			               			$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
			            		}
			            	}else if($MorePlayersz == '1'){
			            		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Not Tracked';
			            	}else{
			            		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
			            	}		       		
				       	}else{
				       		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
				       	}
				       	if($isLfpurchased == '1'){
							$rmplayers        	= get_post_meta( $valueI['order_id'], 'rm_created_player', true );
					        $rmArray          	= explode(',', $rmplayers );
					        if(!empty($rmplayers)){
					            if(in_array($Names, $rmArray)){
					              	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
					            }else{
					               	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
					            }     
					        }else if($MorePlayersz == '1'){
								$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Not Tracked';
					        }else{
					        	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'Yes';
					        }		       		
				       	}else{
				       		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
				       	}
						if($isTshirtpursd == '1'){		
							$tSht = array();
							foreach ( $itemvals as $itemvs ) {
							    $product_variation_id   = $itemvs['variation_id'];
							    if($product_variation_id > 0){
							        $product_name       = $itemvs['name'];
							        $quantity           = $itemvs['quantity'];
							        $tSht[] 			= $product_name.'_'.$quantity;					        
							    }
							}
							$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = implode(' -', $tSht);
						}else{
							$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = 'No';
						}
				       	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $order->total;
					 	$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $order->payment_method_title;
			            $notes_array    = array();
			            $NoteZ          = array();
			            $notes_array    =  tblr_export_get_all_order_notes($valueI['order_id']);
			            if ( count( $notes_array ) != 0) {
			                foreach ( $notes_array as $notes ){
			                   $NoteZ[] = trim(strip_tags($notes)); 
			                }
			                $nts = implode(' | ', $NoteZ);
			                $AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $nts;
			            }else{
			                $AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = '';
			            }
					}
				}
			}
		}
		// echo '<pre>';
		// print_r($AllEventAttendeesExport);
		// echo '</pre>';
		prep_export_csv($AllEventAttendeesExport);
	}

}
/*
*
*/
function prep_export_csv($AllEventAttendeesExport){
 	$url 				= getDirCsv('check');
	$fh 				= fopen( $url."/Tribe-All-Events-Export.csv", 'w' );
	$arrkey  			= '1';
	foreach ($AllEventAttendeesExport as $key1 => $value1) {
		$xpvals 		= array();
		$celKey 		= 'A';
		foreach ($value1['titles'] as $key2 => $value2) {		
			$xpvals[$celKey] 	= $value2; 
			$celKey++;
		}
		fputcsv($fh,$xpvals);
		foreach ($value1['Values'] as $key3 => $value3) {
		$xpvals3 		= array();
		$celKeys 		= 'A';	
			foreach ($value3 as $key => $value4) {
				$xpvals3[$celKeys] = $value4; 
				$celKeys++;
			}
			fputcsv($fh,$xpvals3);
		}		
		$arrkey++;
	}
	fclose($fh);
	$headers = array(
    			'Content-Type' => 'application/csv',
    			'Content-Disposition' => 'attachment',
				);
?>
<script type="text/javascript">
 jQuery("#triggerdownload").trigger('click');
 jQuery("#triggerdownload").trigger("click");
</script>
<?php

	exit;
  }

  /**
 * cretae csv
 * @param unknown_type $uuid
 */
function getDirCsv($_dir){
	$upload 		= wp_upload_dir();
	$upload_dir 	= $upload['basedir'];
	$upload_dir 	= $upload_dir . '/prep-csv';
	if (! is_dir($upload_dir)) {
		wp_mkdir_p( $upload_dir, 0705 );
	}
	$child_dir 		= $upload_dir."/".$_dir;
	if (! is_dir($child_dir)) {
		wp_mkdir_p( $child_dir, 0705 );
	}
	return $child_dir;
}
/*
*
*/
function getdwpath(){
	$upload 		= wp_upload_dir();
	return $upload['baseurl']. '/prep-csv/check/Tribe-All-Events-Export.csv';
}


/**
 * 
 * @param int $order_id
 * @return array
 */
function tblr_export_get_all_order_notes( $order_id ){
    $order_notes    =   array();
    $args = array (
            'post_id'   => $order_id,
            'orderby'   => 'comment_ID',
            'order'     => 'DESC',
            'approve'   => 'approve',
            'type'      => 'order_note'
    );
    remove_filter ( 'comments_clauses', array (
            'WC_Comments',
            'exclude_order_comments'
    ), 10, 1 );    
    $notes = get_comments ( $args );
    if ($notes) {
        foreach ( $notes as $note ) {
            $order_notes[]  = wpautop ( wptexturize ( wp_kses_post ( $note->comment_content ) ) );
        }
    }     
    return $order_notes;
}
