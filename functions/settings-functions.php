<?php

include 'pagename.php';

include $session;

Session::checkSession_f();

include '../config/'.$config;

include '../config/'.$database;

$db = new database();

function sanitize_input( $data ) { //filter value function

  global $db;

  return mysqli_real_escape_string( $db->link, strtolower( htmlspecialchars( stripslashes( trim( $data ) ) ) ) );
}

function dataAdd($domain, $type, $user_id){
  global $db;
  $save = false;
  $type = ucwords($type);
  $catch_all_check = ($type == 'Free Account') ? 0 : 1;
  $domain_check_sql = "SELECT * FROM email_category WHERE user_id = '$user_id' AND name = '$domain' ";
  $domain_check_read = $db->select($domain_check_sql);
  if ($domain_check_read) {
    $count = mysqli_num_rows($domain_check_read);
    if ($count <= 0) {
      $query_insert = "INSERT INTO email_category ( name, e_type, catch_all_check, user_id) VALUES ('$domain','$type','$catch_all_check','$user_id')";
      $read_insert = $db->insert($query_insert);
      if ($read_insert) {
        $save = true;
      }
    }
  }
  return $save;
}
function dataUpdate($domain, $type, $target_id, $user_id){
  global $db;
  $update = false;
  $type = ucwords($type);
  $catch_all_check = ($type == 'Free Account') ? 0 : 1;
  $data_update_sql = "UPDATE email_category SET name = '$domain', e_type = '$type', catch_all_check = '$catch_all_check' WHERE user_id = '$user_id' AND id = '$target_id' ";
  $data_update_read = $db->update($data_update_sql);
  if ($data_update_read) {
    $update = true;
  }
  return $update;
}

// Product Item Add Function
function dataAddItem($item_name, $item_price, $credit_amount){
  global $db;
  $save = false;
  $prod_item_check_sql = "SELECT * FROM products WHERE item_name = '$item_name' ";
  $prod_item_check_read = $db->select($prod_item_check_sql);
  if ($prod_item_check_read) {
    $prod_item_count = mysqli_num_rows($prod_item_check_read);
    if ($prod_item_count <= 0) {
      $query_insert = "INSERT INTO products ( item_name, item_price, credit_amount) VALUES ('$item_name','$item_price','$credit_amount')";
      $read_insert = $db->insert($query_insert);
      if ($read_insert) {
        $save = true;
      }
    }
  }
  return $save;
}

// Product Item Edit/Update Function
function dataUpdateItem($item_name,$item_price,$credit_amount,$target_id_item){
  global $db;
  $update = false;
  $prod_item_update_sql = "UPDATE products SET item_name = '$item_name', item_price = '$item_price', credit_amount = '$credit_amount' WHERE id = '$target_id_item' ";
  $prod_item_update_read = $db->update($prod_item_update_sql);
  if ($prod_item_update_read) {
    $update = true;
  }
  return $update;
}


$error = false;
$message = '';
$action_std = false;
$action_cat = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['timer_btn'])) { //users send mail range and time update
          $action_std = true;
          $action_cat = 'send_mail';
          $range = sanitize_input($_POST['range']);
          $user_id = sanitize_input($_POST['user_id']);
          $time_range = sanitize_input($_POST['time_range']);
          $time_range = (int)$time_range;
          echo $time_range;
          $time_range = $time_range * 60;
          $query2 = "SELECT * FROM timer WHERE user_id = '$user_id' ";
          $read2 = $db->select($query2);
          if ($read2) {
            $count = mysqli_num_rows($read2);
            if ($count > 0) {
              $query18 = "UPDATE timer SET e_range = '$range', time_range = '$time_range' WHERE user_id = '$user_id' ";
              $read18 = $db->update($query18);
              if ($read18) {
                $error = false;
                $message = 'Update successfully';
              }else{
                $error = true;
                $message = 'Database Connection Error';
              }
            } else {
              $query_insert = "INSERT INTO timer ( user_id, e_range, time_range,  last_send)
              VALUES ('$user_id','$range','$time_range','0')";
              $read_insert = $db->insert($query_insert);
              if ($read_insert) {
                $error = false;
                $message = 'Insert successfully';
              }else{
                $error = false;
                $message = 'Database Connection Error';
              }
            }
          }else{
            $error = false;
            $message = 'Database Connection Error';
          }
        }elseif (isset($_POST['save_button'])) {
          $action_std = true;
          $action_cat = 'save_mail';
          $listing_value = $_POST['listing_value'];
          $user_id = sanitize_input($_POST['user_id']);
          $std = false;
          $correct = 0;
          $wrong = 0;
          foreach($listing_value as $value) {
            $value = sanitize_input($value);
            $domain = 'name_'.$value;
            $type = 'type_'.$value;
            $domain =  sanitize_input($_POST[$domain]);
            $type =  sanitize_input($_POST[$type]);
            if(!empty($domain) && !empty($type) && !empty($user_id)){
              $std = true;
              $result = dataAdd($domain, $type, $user_id);
              if($result){
                $correct++;
              }else{
                $wrong++;
              }
            }else{
              $wrong++;
            }
          }
          if($std){
            $error = ($correct > 0) ? false : true ;
            $message = (($correct > 0) ? $correct.' data save successfully, ' : '').(($wrong > 0) ? $wrong.' data not save!' : '');

          }else{
            $error = true;
            $message = 'No Data Found';
          }
        }elseif (isset($_POST['estimated_cost_btn'])) {
          $action_std = true;
          $action_cat = 'estimated_cost';
          $estimated_cost = sanitize_input($_POST['estimated_cost']);
          $cost_per_scan = sanitize_input($_POST['cost_per_scan']);
          if(!empty($estimated_cost) && is_numeric($estimated_cost) && !empty($cost_per_scan) && is_numeric($cost_per_scan)){
            $site_options_query = "SELECT * FROM site_options";
            $site_options_read = $db->select($site_options_query);
            if ($site_options_read) {
              $site_options_count = mysqli_num_rows($site_options_read);
              if($site_options_count > 0){
                $site_options_update_sql = "UPDATE site_options SET estimated_cost = '$estimated_cost',  cost_per_scan = '$cost_per_scan'";
              }else{
                $site_options_update_sql = "INSERT INTO site_options (estimated_cost, cost_per_scan) VALUES ('$estimated_cost', '$cost_per_scan') ";

              }
              $site_options_update_read = $db -> update($site_options_update_sql);
              if ($site_options_update_read) {
                $error = false;
                $message = "Update successfully.";
              }else{
                $error = true;
                $message = "Database connection failed";
              }
            }else{
              $error = true;
              $message = "Database connection failed";
            }

          }else{
            $error = true;
            $message = "Data not found";
          }


          // ------------------------------------------------------------------SCAN MAIL SETTINGS - site_options-----------------
        }elseif (isset($_POST['scan_mail_settings'])) {
          $action_std = true;
          $action_cat = 'scan_mail_settings';
          $scan_from = sanitize_input($_POST['scan_from']);
          $timeout = sanitize_input($_POST['timeout']);
          $scan_port = sanitize_input($_POST['scan_port']);
          if(!empty($scan_from) && !empty($timeout) && !empty($scan_port)){
            $site_options_query = "SELECT * FROM site_options";
            $site_options_read = $db->select($site_options_query);
            if ($site_options_read) {
              $site_options_count = mysqli_num_rows($site_options_read);
              if($site_options_count > 0){
                $site_options_update_sql = "UPDATE site_options SET scan_time_out = '$timeout',scan_port = '$scan_port',scan_mail = '$scan_from' ";
              }else{
                $site_options_update_sql = "INSERT INTO site_options (scan_time_out, scan_port, scan_mail) VALUES ('$timeout', '$scan_port', '$scan_from') ";
              }
              $site_options_update_read = $db -> update($site_options_update_sql);
              if ($site_options_update_read) {
                $error = false;
                $message = "Update successfully.";
              }else{
                $error = true;
                $message = "Database connection failed";
              }
            }else{
              $error = true;
              $message = "Database connection failed";
            }

          }else{
            $error = true;
            $message = "Data not found";
          }

           // ------------------------------------------------------------------PAYPAL SETTINGS - site_options-----------------
        


          // ------------------------------------------------------------------SITE TITLE - site_options-----------------
        }elseif (isset($_POST['site_options_change'])) {
                  $action_std = true;
                  $action_cat = 'logo_site';
                  $logo_exits = false;
                  $site_title = sanitize_input($_POST['site_title']);
                  $logo= sanitize_input($_FILES['logo_image']['name']);
                  if(!empty($logo)){
                    $logo_exits = true;
            // ---img functions---
            // Get Image Dimension
                    $fileinfo = @getimagesize($_FILES["logo_image"]["tmp_name"]);
                    $width = $fileinfo[0];
                    $height = $fileinfo[1];
                    $allowed_image_extension = array(
                      "png",
                      "jpg",
                      "jpeg"
                    );
            // Get image file extension
                    $file_extension = pathinfo($_FILES["logo_image"]["name"], PATHINFO_EXTENSION);
                    $file_extension = strtolower($file_extension);
            // Validate file input to check if is not empty
                    if (! file_exists($_FILES["logo_image"]["tmp_name"])) {
                      $error = true;
                      $message = "Choose image file to upload.";

            }    // Validate file input to check if is with valid extension
            else if (! in_array($file_extension, $allowed_image_extension)) {
              $error = true;
              $message = "Upload valiid images. Only PNG, JPG and JPEG are allowed.";
            }    // Validate image file size
            else if (($_FILES["logo_image"]["size"] > 500000)) {

              $error = true;
              $message = "Image size exceeds 500KB";

            }else {
             $logo = "logo.".$file_extension;
             $target = "../assets/img/" .$logo;
             unlink($target);
             if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $target)) {
               $error = false;
               $message = "Image uploaded successfully.";

             } else {

              $error = true;
              $message = "Problem in uploading image files.";

            }

          }
        }
        if(!$error){
          $site_options_query = "SELECT * FROM site_options";
          $site_options_read = $db->select($site_options_query);
          if ($site_options_read) {
            $site_options_count = mysqli_num_rows($site_options_read);
            if($site_options_count > 0){
              if($logo_exits){
                $site_options_update_sql = "UPDATE site_options SET logo = '$logo',site_title = '$site_title' ";
              }else{
                $site_options_update_sql = "UPDATE site_options SET site_title = '$site_title' ";
              }
            }else{
              if($logo_exits){
                $site_options_update_sql = "INSERT INTO site_options (logo, site_title) VALUES ('$logo', '$site_title') ";
              }else{
                $site_options_update_sql = "INSERT INTO site_options (site_title) VALUES ('$site_title') ";
              }
            }
          }


          $site_options_update_read = $db -> update($site_options_update_sql);
          if ($site_options_update_read) {
            $error = false;
            $message = "Update successfully.";
          }else{
            $error = true;
            $message = "Problem in uploading database files.";
          }
        }else{

        }
          // ----------------------------------------------------------------------------------------------------logo_title-----------------
      }elseif (isset($_POST['edit_button'])) {
        $action_std = true;
        $action_cat = 'save_mail';
        $user_id = sanitize_input($_POST['user_id']);
        $target_id = sanitize_input($_POST['target_id']);
        $name = sanitize_input($_POST['name']);
        $type = sanitize_input($_POST['type']);
        if(!empty($name) && !empty($type) && !empty($target_id) && !empty($user_id)){
          $result = dataUpdate($name, $type,$target_id, $user_id);
          if($result){
            $error = false;
            $message = 'Update successfull';
          }else{
            $error = true;
            $message = 'Update is not successfull';
          }

        }else{
          $error = true;
          $message = 'Data Not Found';
        }
      }elseif (isset($_POST['edit_button_item'])) {
        $action_std = true;
        $action_cat = 'prod_save_notice';
        $user_id = sanitize_input($_POST['user_id']);
        $target_id_item = sanitize_input($_POST['target_id_item']);

        $item_name =  sanitize_input($_POST['item_name']);
        $item_price =  sanitize_input($_POST['item_price']);
        $credit_amount =  sanitize_input($_POST['credit_amount']);
        $item_status =  sanitize_input($_POST['item_status']);

        if(!empty($item_name) && !empty($item_price) && !empty($credit_amount) && !empty($target_id_item)){
          $result = dataUpdateItem($item_name,$item_price,$credit_amount,$target_id_item);
          if($result){
            $error = false;
            $message = 'Update successfull';
          }else{
            $error = true;
            $message = 'Update is not successfull';
          }

        }else{
          $error = true;
          $message = 'Data Not Found';
        }
      }elseif (isset($_POST['registration_btn'])) {
        $action_std = true;
        $action_cat = 'registration';
        $user_id = sanitize_input($_POST['user_id']);
        $registration = sanitize_input($_POST['registration']);
        $error = true;
        $message = 'Something is wrong!';
        if(!empty($user_id) && !empty($registration) && ($registration == 'off' || $registration == 'active')){
          $users_check_sql = "SELECT * FROM users WHERE id = '$user_id' ";
          $users_check_read = $db->select($users_check_sql);
          if ($users_check_read) {
            $users_check_count = mysqli_num_rows($users_check_read);
            if ($users_check_count > 0) {
              $users_check_row = $users_check_read->fetch_assoc();
              $users_category = $users_check_row['category'];
              if ($users_category == 'admin') {
                $registration_check_sql = "SELECT * FROM site_options";
                $registration_check_read = $db->select($registration_check_sql);
                if ($registration_check_read) {
                  $registration_check_count = mysqli_num_rows($registration_check_read);
                  if ($registration_check_count > 0) {
                    $update_registration_sql = "UPDATE site_options SET registration_action = '$registration' ";
                    $update_registration_read = $db->update($update_registration_sql);
                    if($update_registration_read){
                      $error = false;
                      $message = 'registraion status update successfully';
                    }else{
                      $error = true;
                      $message = 'Database connection failed';
                    }
                  }
                }

              }
            }else{

            }
          }
        }else{
          $error = true;
          $message = 'Data Not Found';
        }
      }elseif (isset($_POST['delete-btn'])) {
        $action_std = true;
        $action_cat = 'save_mail';
        $user_id = sanitize_input($_POST['user_id']);
        $target_id = sanitize_input($_POST['target_id']);
        if(!empty($user_id) && !empty($target_id)){
          $delete_data_sql = "DELETE FROM email_category WHERE user_id = '$user_id' AND id = '$target_id' ";
          $delete_data_read = $db->delete($delete_data_sql);
          if($delete_data_read){
            $error = false;
            $message = 'Delete successfull';
          }else{
            $error = true;
            $message = 'Database connection failed';
          }
        }else{
          $error = true;
          $message = 'Data Not Found';
        }
      }elseif (isset($_POST['delete-btn-item'])) {
        $action_std = true;
        $action_cat = 'prod_save_notice';
        $target_id_item = sanitize_input($_POST['target_id_item']);
        if(!empty($target_id_item)){
          $delete_item_data_sql = "DELETE FROM products WHERE id = '$target_id_item' ";
          $delete_item_data_read = $db->delete($delete_item_data_sql);
          if($delete_item_data_read){
            $error = false;
            $message = 'Delete successfull';
          }else{
            $error = true;
            $message = 'Database connection failed';
          }
        }else{
          $error = true;
          $message = 'Data Not Found';
        }
      }else{
        header("Location: ../public/".$error_404_page);
      }
      if($action_std && !empty($action_cat)){
        Session::set("action_cat", $action_cat);
        Session::set("action", $error);
        Session::set("action_message", $message);
        header("Location: ../public/".$settings_page);
      }else{
        header("Location: ../public/".$error_404_page);
      }
    }else{
      header("Location: ../public/".$error_404_page);
    }
    ?>