<?php
if( isset( $_POST['filename'] ) && isset( $_POST['uid'] ) ){
	
	function app_url($s)
	{
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
		$sp = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port = $s['SERVER_PORT'];
		$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
		$host = isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
		return $protocol . '://' . $host . $port . $s['REQUEST_URI'];
	}

	$host = dirname(dirname(app_url($_SERVER)));
	$host = htmlspecialchars($host,ENT_QUOTES,'UTF-8');
  
  $curl = curl_init();

  curl_setopt( $curl, CURLOPT_URL, "$host/functions/scripts/v_email.php" );
  curl_setopt( $curl, CURLOPT_POST, TRUE );
  curl_setopt( $curl, CURLOPT_POSTFIELDS, $_POST );

  curl_setopt( $curl, CURLOPT_TIMEOUT, 1 ); 
  curl_setopt( $curl, CURLOPT_HEADER, 0 );
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, false );
  curl_setopt( $curl, CURLOPT_FORBID_REUSE, true );
  curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 1 );
  curl_setopt( $curl, CURLOPT_FRESH_CONNECT, true );

  curl_exec( $curl );

  curl_close( $curl );
}