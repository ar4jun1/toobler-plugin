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
	if(isset($_POST['prep_upsell_fields']) && $_POST['prep_upsell_fields'] != null ){
		$eventId 	= $_POST['post_ID'];
		update_post_meta($eventId,'all_export_exclude_upsell',$_POST['prep_upsell_fields']);
	}
	if(isset($_POST['prep_order_fields']) && $_POST['prep_order_fields'] != null ){
		$eventId 	= $_POST['post_ID'];
		update_post_meta($eventId,'all_export_exclude_order',$_POST['prep_order_fields']);
	}
}

		/////////////   		metabox for : ATTENDEE   		/////////////////

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
		<p> Check those fields that do not need to include in Tribe-All-Events-Export (csv) file. </p> <br>
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



		/////////////   		metabox for : UPSELL   		/////////////////

function my_em_event_upsell_boxes(){ 
	add_meta_box('em-event-upsell', 'Upsell Information', 'my_em_event_upsell_metabox','tribe_events', 'side','low');
}
add_action('add_meta_boxes', 'my_em_event_upsell_boxes');

function my_em_event_upsell_metabox(){ 
	$eventId 			= get_the_ID(); 	//Currnt event id
	$fields 			= array();
	$fields 			= $variable = get_field('upsells_shortcode', $eventId); 
	if(!$fields) return false;

	$upsellIds 			= getShortcodeParam($fields);
	if(!$upsellIds) return false;

	$eventFields 		= array();
	$eventArray 		= array();
	$prep_event_fields_data = array();

	foreach ($upsellIds as $keyfields => $valuefields) {

		$eventFields['slug'] 	= $valuefields;
		$eventFields['name'] 	= get_the_title( $valuefields );
		$eventArray [] 			= $eventFields;			
	} 
	$prep_event_fields_data = get_post_meta($eventId ,'all_export_exclude_upsell',true);
	
	ob_start();  ?>
		<p> Check those fields that do not need to include in Tribe-All-Events-Export (csv) file. </p> <br>
	<?php
	foreach ($eventArray as $eventKey => $eventValue) {   
			?>
			<input type="checkbox" name="prep_upsell_fields[]" 
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
		/////////////   		metabox for : ORDER DETAILS   		/////////////////

function my_em_event_order_boxes(){ 
	add_meta_box('em-event-order', 'Order Details', 'my_em_event_order_metabox','tribe_events', 'side','low');
}
add_action('add_meta_boxes', 'my_em_event_order_boxes');

function my_em_event_order_metabox(){ 
	$eventId 			= get_the_ID(); 	//Currnt event id
	$fields 			= array('order_id'=>'Order Id','order_status'=>'Order Status','purchaser_name'=>'Customer Name','purchaser_email'=>'Customer email','purchase_time'=>'Registartion time','total'=>'Order total','payment_method_title'=>'Payment method','order_notes'=>'Order Notes');
	$eventFields 		= array();
	$eventArray 		= array();
	$prep_event_fields_data = array();
	foreach ($fields as $keyfields => $valuefields) {
		$eventFields['slug'] 	= $keyfields;
		$eventFields['name'] 	= $valuefields;
		$eventArray [] 			= $eventFields;			
	} 
	$prep_event_fields_data = get_post_meta($eventId ,'all_export_exclude_order',true);
	
	ob_start();  ?>
		<p> Check those fields that do not need to include in Tribe-All-Events-Export (csv) file. </p> <br>
	<?php
	foreach ($eventArray as $eventKey => $eventValue) {   
			?>
			<input type="checkbox" name="prep_order_fields[]" 
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


function getShortcodeParam($shortCode){
	$result = array();
	//get shortcode regex pattern wordpress function
	$pattern = get_shortcode_regex();
	if (   preg_match_all( '/'. $pattern .'/s', $shortCode, $matches ) )
		{
		    $keys = array();
		    $result = array();
		    foreach( $matches[0] as $key => $value) {
		        // $matches[3] return the shortcode attribute as string
		        // replace space with '&' for parse_str() function
		        $get = str_replace(" ", "&" , $matches[3][$key] );
		        parse_str($get, $output);

		        //get all shortcode attribute keys
		        $keys = array_unique( array_merge(  $keys, array_keys($output)) );
		        $result[] = $output;

		    }
		    if( $keys && $result ) {
		        // Loop the result array and add the missing shortcode attribute key
		        foreach ($result as $key => $value) {
		            // Loop the shortcode attribute key
		            foreach ($keys as $attr_key) {
		                $result[$key][$attr_key] = isset( $result[$key][$attr_key] ) ? $result[$key][$attr_key] : NULL;
		            }
		            //sort the array key
		            ksort( $result[$key]);              
		        }
		    }
		    if(isset($result[0])){
			    foreach ($result[0] as $key => $value) {
			    	$implode = explode(',', $key);
			    	if($implode && count($implode)>0){
			    		break;
			    	}
			    }
			    return $implode;
			}
			return false;
		}
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
?>
	<a href="<?php echo $csvpath; ?>"> Download Csv </a>
<?php
	global $woocommerce;
	$testing 				= tribe_get_events (array('posts_per_page' => '-1'));
	$AllEventAttendeesExport= Array();
	$titleFields 			= array();
	$upsellIds 				= array();
 	foreach ($testing as $keysas => $valueas) {
 		$lastKey            = '';
		$fields 			= array();
		$fields 			= Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_event( $valueas->ID  );
		$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Ticket';	

		// edited on - 21-Aug : AJN 
		$prep_event_fields_data = array();
		$prep_event_fields_data = get_post_meta($valueas->ID ,'all_export_exclude',true);

		$prep_order_fields_data = array();
		$prep_order_fields_data = get_post_meta($valueas->ID ,'all_export_exclude_order',true);

		$prep_upsell_fields_data = array();
		$prep_upsell_fields_data = get_post_meta($valueas->ID ,'all_export_exclude_upsell',true);
		$categorieseUp = array();
		foreach ($prep_upsell_fields_data as $upKey => $upValue) {
			global $productObj;
			$term_listseUp = wp_get_post_terms($upValue,'product_cat',array('fields'=>'all'));
					  	foreach ( $term_listseUp as $termlsteUp ){
			          		$categorieseUp[]  	= $termlsteUp->slug;
			          	}
			$productObj 			= wc_get_product( $upValue );
			if( $productObj->is_type( 'variable' ) ){
				$categorieseUp[] 	= 'variationExist';
			}
		}
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
		$fields 			= $variable = get_field('upsells_shortcode', $eventId); 
		$upsellIds 			= getShortcodeParam($fields);
		if ( !in_array( 'order_id', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Id';
		}
		if ( !in_array( 'order_status', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Status';
		}
		if ( !in_array( 'purchaser_name', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'custommer-name';	
		}
		if ( !in_array( 'purchaser_email', $prep_order_fields_data ) ) { 	
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'custommer-email';
		}
		if ( !in_array( 'purchase_time', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Registration Date';
		}
		if ( !in_array( 'evaluation', $categorieseUp ) ) {  
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Evaluation Purchased';
		}
		if ( !in_array( 'insurance', $categorieseUp ) ) {  
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Insurance Purchased';
		}
		if ( !in_array( 'lifetime', $categorieseUp ) ) {  
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Rm Created';
		}
		if ( !in_array( 'variationExist', $categorieseUp ) ) {  
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'T shirt Purchased';
		}

		if ( !in_array( 'total', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Total';
		}
		if ( !in_array( 'payment_method_title', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Payment Title';
		}
		if ( !in_array( 'order_notes', $prep_order_fields_data ) ) { 
			$AllEventAttendeesExport[$valueas->ID]['titles'][] =  'Order Notes';
		}
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

				if ( !in_array( 'order_id', $prep_order_fields_data ) ) { 
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['order_id'];
				}
				if ( !in_array( 'order_status', $prep_order_fields_data ) ) {
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['order_status'];
				}
				if ( !in_array( 'purchaser_name', $prep_order_fields_data ) ) {
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchaser_name'];
				}
				if ( !in_array( 'purchaser_email', $prep_order_fields_data ) ) {
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchaser_email'];
				}
				if ( !in_array( 'purchase_time', $prep_order_fields_data ) ) {
					$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $valueI['purchase_time'];
				}
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
				    if ( !in_array( 'evaluation', $categorieseUp ) ) {  
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
				    }
				    if ( !in_array( 'insurance', $categorieseUp ) ) {  
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
				    }
				    if ( !in_array( 'lifetime', $categorieseUp ) ) {  
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
				    }
				    if ( !in_array( 'variationExist', $categorieseUp ) ) {  
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
					}
					if ( !in_array( 'total', $prep_order_fields_data ) ) { 
			       		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $order->total;
			       	}
			       	if ( !in_array( 'payment_method_title', $prep_order_fields_data ) ) { 
				 		$AllEventAttendeesExport[$valueas->ID]['Values'][$keyI][] = $order->payment_method_title;
				 	}
				 	if ( !in_array( 'order_notes', $prep_order_fields_data ) ) { 
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
	}
	// echo '<pre>';
	// print_r($AllEventAttendeesExport);
	// echo '</pre>';
	prep_export_csv($AllEventAttendeesExport);

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
    			'Content-Type' => 'text/csv',
				);
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
