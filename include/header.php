<?php
error_reporting( E_ALL );
ini_set( 'display_errors', 1);
if(isset($title)){
include (($title == 'dashboard') ? '': '../').'functions/pagename.php';
include (($title == 'dashboard') ? '': '../').'functions/'.$session;

if($title == 'dashboard'){
  Session::checkSession_d(); //check login session for index page
}else{
  Session::checkSession(); // check login for others page
}

if(isset($_GET['action']) && $_GET['action'] == "logout"){
  if($title == 'dashboard'){
    Session:: destroy_d();
  }else{
    Session:: destroy();
  }
}

global $user_id;
global $s_path;

$user_id = $_SESSION['id'];
$fname = $_SESSION['fname'];
$image = $_SESSION['image'];


include (($title == 'dashboard') ? '': '../').'config/'.$config;
include (($title == 'dashboard') ? '': '../').'config/'.$database;
$db = new database();

$user_data_sql = "SELECT * FROM users WHERE id = '$user_id'";
$user_data_read = $db->select($user_data_sql);

$scan_timeout = '';
$scan_port = '';
$scan_mail = '';
$d_estimated_cost = '';

$site_options_sql = "SELECT * FROM site_options";
$site_options_read = $db->select($site_options_sql);
if ($site_options_read) {
    $site_options_check = mysqli_num_rows($site_options_read);
    if ($site_options_check > 0) {
      $site_options_row = $site_options_read->fetch_assoc();
      $logo_image = $site_options_row['logo'];
      $app_title = $site_options_row['site_title'];
      $scan_timeout = $site_options_row['scan_time_out'];
      $scan_port = $site_options_row['scan_port'];
      $scan_mail = $site_options_row['scan_mail'];
      $d_estimated_cost = $site_options_row['estimated_cost'];
      $s_path =  $site_options_row['script_path'];
      $s_url =  $site_options_row['script_url'];
      if(empty($logo_image)){
        $logo_image = 'default_logo.png';
      }
      if(empty($app_title)){
        $app_title = 'Mailbuff';
      }
    }else{
      $logo_image = 'default_logo.png';
      $app_title = 'Mailbuff';
    }
  }



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Opensource and Professional Mail Verification Solution.">
  <meta name="author" content="Appbuff">
  <title><?php echo ucfirst($title); ?></title>
  <!-- Custom fonts for this template-->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/sb-admin-2.css" rel="stylesheet">
  <!-- custom css -->
  <link href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/css/style.css" rel="stylesheet">
  <!--Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/site.webmanifest">
  <link rel="mask-icon" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="<?php echo (($title == 'dashboard') ? '': '../')?>assets/img/favicon/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
  <!-- Bootstrap core JavaScript-->
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/jquery.min.js"></script>
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/chart.js"></script>
  <script src="<?php echo (($title == 'dashboard') ? '': '../')?>assets/js/sweetalert.min.js"></script>
</head>

<body id="page-top" class="sidebar-toggled">
  <noscript>
    <div class="js_stop">
      <strong>Browser Do Not support JavaScript! </strong>
      We're sorry, but 'Mailbuff' doesn't work without JavaScript enabled. If you can't enable JavaScript in this browser then try a different browser which support JavaScript.
    </div>
      <style>#wrapper { display:none; }</style>
   </noscript>
  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav sidebar sidebar-dark accordion toggled" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo (($title == 'dashboard') ? '': '../').$index_page;?>">
        <div class="sidebar-brand-icon rotate-n-15">
          <img class="logo-img" src="<?php echo (($title == 'dashboard') ? '': '../').'';?>assets/img/<?php echo $logo_image?>" alt="mailbuff">
        </div>
        <div class="sidebar-brand-text"><?php echo $app_title?></div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item <?php echo (($title == 'dashboard') ? 'active' : '')?>">
        <a class="nav-link" href="<?php echo (($title == 'dashboard') ? '': '../').$index_page;?>">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li>
       <li class="nav-item <?php echo (($title == 'My List') ? 'active' : '')?>">
        <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'public/': '').$my_list_page;?>">
          <i class="fas fa-fw fa-table"></i>
          <span>Mail List</span></a>
      </li>
      <li class="nav-item <?php echo (($title == 'Email Listing') ? 'active' : '')?>">
       <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'public/': '').$lear_management_page;?>">
         <i class="fas fa-fw fa-table"></i>
         <span>Leads</span></a>
     </li>
     <li class="nav-item <?php echo (($title == 'Send Mail') ? 'active' : '')?>">
      <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'public/': '').$send_mail_page;?>">
        <i class="fas fa-fw fa-table"></i>
        <span>Mail</span></a>
    </li>

    <li class="nav-item <?php echo (($title == 'Settings') ? 'active' : '')?>">
      <a class="nav-link" href="<?php echo (($title == 'dashboard') ? 'public/': '').$settings_page;?>">
        <i class="fas fa-fw fa-cogs"></i>
        <span>Settings</span></a>
    </li>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-lg-block">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" style="overflow-x: inherit;" class="d-flex flex-column">
      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-lg-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
            <div class="topbar-divider d-none d-sm-block"></div>
            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 text-capitalize"><?php echo $fname; ?></span>
                <img class="img-profile rounded-circle" src="<?php echo (($title == 'dashboard') ? '': '../')?>uploads/<?php echo (!empty($image) && $image != '0' ) ? $image : 'thumb.png'; ?>">
              </a>

              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?php echo (($title == 'dashboard') ? 'public/': '').$profile_page?>">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="<?php echo (($title == 'dashboard') ? 'public/': '').$settings_page?>">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>

        </nav>
		
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

<?php }?>
