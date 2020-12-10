<?php
/**
 * A Recursive Functional Method To Scan DB Fields & Process Them
 *
 * This Script Will Be Running Continuously & Look For New Not Verified
 * Emails & Will Check & update them accordingly
 *
 * Defining DB Connection Properties Below
 * 
 *
 * Will Fetch Fields From DB_TABLE.DB_BUNDLES Where DB_BUNDLE.ID = 'Running'
 * and then Will Fetch records from DB_TABLE.DB_EMAILS.
 */

 ini_set('display_errors', 1); 
 error_reporting(E_ALL);

if( isset( $_REQUEST['filename'] ) && isset( $_REQUEST['uid'] ) ){
	
// Get path
$phpver = (float)phpversion();
if ($phpver > 7.0) {
   $prev_path = dirname(__DIR__, 2);
   echo '7.0+';  
 } elseif ($phpver <= 5.6) {
   $prev_path = realpath(__DIR__ . '/../..');
   echo '<5.6';
 } elseif ($phpver <= 5.3) {
   echo '5.3';
   $prev_path = realpath(dirname(__FILE__) . '/../..');
}

include($prev_path.'/functions/pagename.php');

include($prev_path.'/functions'.'/'.$session);

  Session::checkSession_f();

  $filename = $_REQUEST['filename'];
  $user_id = $_REQUEST['uid'];

  //  Keep running the script no matter what
  ignore_user_abort( true );

  // Execute for an unlimited timespan
  set_time_limit( 0 );
  
  include($prev_path.'/config/'.$config);

  include($prev_path.'/functions/pagename.php');

  include('includes/database.php');


  $db = new DB();
  
  include('includes/class.verify.email.php');

  // --------------------------------
  $count_valid = $count_role_acc = $count_catch_all = $count_unknown = $count_invalid = $count_syntax_error = $count_disponsable_acc = $count_free_acc = 0;

  $user_id = intval( $user_id );
  
  $task_delete_current = "DELETE FROM task WHERE csv_name = '$filename' AND user_id = '$user_id'";

  // user_id is not valid
  if ( $user_id == 0 ) {
    
    $read_task_delete = $db->delete( $task_delete_current ); exit();
  }

  // add the new task for this user
  $task_insert = "INSERT INTO task (csv_name, user_id) VALUES ('$filename', '$user_id')";

  $read_task_insert = $db->insert( $task_insert );

  // delete other tasks for this user
  $task_delete = "DELETE FROM task WHERE csv_name <> '$filename' AND user_id = '$user_id'";
  
  $db->delete( $task_delete );

}else{
	
	// user_id is not provide so exit early	
	exit();
}

prefix_verify_emails();

function prefix_verify_emails(){

  global $db, $task_delete, $task_delete_current, $user_id, $filename, $count_valid, $count_role_acc, $count_catch_all, $count_unknown, $count_invalid, $count_syntax_error, $count_disponsable_acc, $count_free_acc;

  $cost_per_scan = VerifyEmail::get_site_option( 'cost_per_scan' );


	// each time fetch how many emails to verify
	$limit = 10;

	// if db connection is not there connect it again
	if ( ! $db->error ) {
		
		$db->connect();
    }
  
  // check if user verifying status is running...
	$running = $db->select( "SELECT * FROM task WHERE user_id = '$user_id' AND csv_name = '$filename' AND status = 'running'" );

  if ( mysqli_num_rows( $running ) == 0 ){

    $db->delete( $task_delete_current ); exit();
  }
  
  $check_email = "SELECT * FROM user_email_list WHERE csv_file_name = '$filename' AND user_id = '$user_id' AND email_status = 'Not Verify' LIMIT $limit";

  $email_st = $db->select( $check_email );
  
  $i = 0;
  
  $count_all_email = mysqli_num_rows( $email_st );
    

    if ( $count_all_email > 0 ){

      while ( $row = $email_st->fetch_assoc() ){

        // check if user verifying status is running...
        $running = $db->select( "SELECT * FROM task WHERE user_id = '$user_id' AND csv_name = '$filename' AND status = 'running'" );

        if ( mysqli_num_rows( $running ) == 0 ){

          $db->delete( $task_delete_current ); exit();
        }

        $status = array( 0, 'status' => 'unknown', 'reasons' => 'Mail server error', 'safe_to_send' => 'No', 'email_score' => 0, 'bounce_type' => '', 'type' => '' );

        $email_id = $row['id'];
        
        $toemail = $row['email_name'];

        $i++;

        $email_arr = explode( '@', $toemail );
        
        $email_acc = $email_arr[0];
        
        $email_dom = $email_arr[1];

        $verify = new VerifyEmail( $toemail, $db, $user_id );

        $status = $verify->status;

        $token_update_query = "UPDATE user_email_list SET 
          email_status = '{$status['status']}', 
          email_type = '{$status['type']}', 
          safe_to_send = '{$status['safe_to_send']}', 
          verification_response = '{$status['reasons']}', 
          score = '{$status['email_score']}', 
          bounce_type = '{$status['bounce_type']}', 
          email_acc = '$email_acc', 
          email_dom = '$email_dom' 
        WHERE id = '$email_id' ";
        
        $db->update( $token_update_query );

      }

      // all $LIMIT is done checking call again...
      prefix_verify_emails();

    }else{

      $db->delete( $task_delete_current ); exit();
    }
}