<?php header('Content-Type: application/json');

if( ! isset( $_POST['filename'] ) ){
  echo json_encode(['status' => 'file_not_found']); exit();
}

include 'pagename.php';
include '../config/'.$config;
include '../config/'.$database;

$db = new database();

function sanitize_text( $data ) { //filter value function
  
  global $db;
  
  return mysqli_real_escape_string( $db->link, strtolower( htmlspecialchars( stripslashes( trim( $data ) ) ) ) );
}

$filename = sanitize_text( $_POST['filename'] );

$user_id = intval( $_POST['uid'] );

if( ! empty( $filename ) ){
  
  $delete_task_sql = "SELECT * FROM task WHERE csv_name = '$filename' AND user_id = '$user_id' ";
  
  $delete_task_read = $db->select( $delete_task_sql );
  
  $count_task = mysqli_num_rows( $delete_task_read );
  
  if ( $count_task > 0 ) {
    
    $task_delete = "DELETE FROM task WHERE csv_name = '$filename' AND user_id = '$user_id'";
    
    $read_task_delete = $db->delete( $task_delete );
  }
}

echo json_encode(['status' => 'ok']); exit();