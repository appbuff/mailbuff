<?php set_time_limit(0); ini_set( 'display_errors', 1 );

include 'pagename.php';

include $session;

Session::checkSession_f();

include '../config/'.$config;

include '../config/'.$database;

$db = new database();

function sanitize_text( $data ) { //filter value function
  
  global $db;
  
  return mysqli_real_escape_string( $db->link, htmlspecialchars( stripslashes( trim( $data ) ) ));
}

$filename = sanitize_text( $_POST['filename'] );

$user_id = sanitize_text( $_POST['uid'] );

$em_status = "Not Verify";

$emails = explode( ",", $_POST['email'] );

$emails = array_filter( $emails, 'strlen' );

$total = count( $emails );

$start_date = date( "Y-m-d h:i:s" );

$duplicate = $unsave = $save = 0;

$unique_emails = array_unique( $emails );

$count_after_uniq = count( $unique_emails );

$duplicate = $total - $count_after_uniq;

$coma_check = false;

$save_data_sql = "INSERT INTO user_email_list ( csv_file_name, email_name, email_status, create_date, user_id ) values";

foreach ( $unique_emails as $email ) {
  
  $email = sanitize_text( $email );
  
  if( ! empty( $email ) ){ $save++;

    $save_data_sql .= $coma_check ? ',' : '';
    
    $coma_check = true;
    
    $save_data_sql .=" ('$filename','$email','$em_status','$start_date','$user_id')";
  }
}

if( $coma_check ) $save_data_read = $db->insert( $save_data_sql );

$end_date = date("Y-m-d h:i:s");

$unsave = $total - ( $save + $duplicate );

echo json_encode(['save' => $save, 'total' => $total, 'unsave' => $unsave, 'duplicate' => $duplicate , 'end_time' => $end_date, 'start_time' => $start_date]);

exit;