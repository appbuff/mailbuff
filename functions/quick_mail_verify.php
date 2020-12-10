<?php

include 'pagename.php';

include $session;

Session::checkSession_f();

include '../config/' . $config;
include '../config/' . $database;
// include '../functions/' . $user_wallet_func;

$user_id = $_SESSION['id'];

$email = 0;

extract( $_POST );

set_time_limit(0);

// --------------------------------------------------------------------------

include 'scripts/includes/class.verify.email.php';

echo json_encode(['index' => $index, 'safetosend' => $status['safe_to_send'], 'status' => $status['status'], 'type' => $status['type'], 'reasons' => $status['reasons'],'debug' => $verify->debug]);

exit();