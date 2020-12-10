<?php

/**
 * Verify Email Adddress
 */
class VerifyEmail
{
    public $port = 25;

    protected $stream = false;

    protected $db;

    public $from = 'localhost'; // change this $from email to yours

    protected $stream_timeout_wait = 30;

    const CRLF = "\r\n";
    
    protected $email;
    
    protected $domain;
    
    protected $email_acc;
    
    public $debug;
    
    public $status;

    protected $mx_records;

    protected $print;

    protected $user_id;

    protected $catchAllCehked = false;

    protected $last_code;

    const EmailRegularExpr = "^([-!#\$%&'*+./0-9=?A-Z^_`a-z{|}~])+@([-!#\$%&'*+/0-9=?A-Z^_`a-z{|}~]+\\.)+[a-zA-Z]{2,24}\$";

    protected $EMAIL_VALIDATION_STATUS_SYNTAX_ERROR = 'syntax error';
    protected $EMAIL_VALIDATION_STATUS_OK = 0;
    protected $EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND = 'domain not found';
    protected $EMAIL_VALIDATION_STATUS_DOMAIN_MX_NOT_FOUND = 'domain mx record not found';
    protected $EMAIL_VALIDATION_STATUS_GET_HOST_FAILED = 'failed to verify host';
    protected $EMAIL_VALIDATION_STATUS_DISPOSABLE_EMAIL = 'email domain is disposable';
    protected $EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED = 'smtp connection Failed';
    protected $EMAIL_VALIDATION_STATUS_CATCH_ALL_SERVER = 'Catch-All mail server';
    protected $EMAIL_VALIDATION_STATUS_INVALID_USER = 'Invalid User Id Provided';

    protected $validation_status_code = '0';
    
    /**
     * Set Emails, Domains, Check DNS record and finaly verify email etc
     */
    public function __construct( $email, $db = null, $user_id = 0, $print = false )
    {
        $this->print = $print;

        $this->db = $db;

        if( $port = self::get_site_option( 'scan_port' ) ){

            $this->port = $port;
        }

        if( $frmEmail = self::get_site_option( 'scan_mail' ) ){

            $this->from = $frmEmail;
        }

        if( $stream_timeout_wait = self::get_site_option( 'scan_time_out' ) ){

            $this->stream_timeout_wait = $stream_timeout_wait;
        }

        $this->status = array( 0, 'status' => 'unknown', 'reasons' => 'Mail server error', 'safe_to_send' => 'No', 'email_score' => 0, 'bounce_type' => '', 'type' => '' );

        if ( $user_id == 0 ) {
        	
        	$this->markAsInvalid();
                        
            $this->setReason( $this->EMAIL_VALIDATION_STATUS_INVALID_USER );

            $this->debug[] = 'Invalid User Found...';

            return;
        }
        
        // first check if email is correct in format...
        if( filter_var( $email, FILTER_VALIDATE_EMAIL ) && preg_match( '/' . str_replace( '/', '\\/', self::EmailRegularExpr ) . '/', $email ) ) {
           // debugger;
            $this->email = $email;

            // get domain or hostname from email address
            $this->domain = $this->getDomainFromEmail( $this->email );
            $this->email_acc = $this->getEmailAccFromEmail( $this->email );

            $email_acc = $this->email_acc;

            if (strpos($email_acc, '\'')){
                $this->markAsInvalid();

                $this->setReason( $this->EMAIL_VALIDATION_STATUS_SYNTAX_ERROR );

                $this->debug[] = 'Incorrect Email Adddress Found...';

                return;
            }

            // check if email is disposible...
            $disposable = $this->db->select( "SELECT * FROM email_category WHERE e_type = 'Disposable Account' AND (name = '$this->email_acc' OR name = '{$this->domain}') " );

            // check if domain is in disposable db
            if ( mysqli_num_rows( $disposable ) > 0 ) {
                
                $this->debug[] = 'Email Domain is Disposable...';

                $this->markAsInvalid();

                $this->setReason( $this->EMAIL_VALIDATION_STATUS_DISPOSABLE_EMAIL );

                $this->status['type'] = 'Disposable Account';
            
            }else{

                // check if domain has MX DNS record
                if ( $this->checkDNS() ) {
                    
                    $this->debug[] = 'Valid Email Domain DNS Found...';

                    if ( $this->getMXrecords() ) {

                        $this->debug[] = 'Valid Email Domain MX Records Found...';

                        if ( $this->connect() ) {
                        
                            $this->verify(); 
                        }
                    
                    }else{

                        $this->markAsInvalid();
                        
                        $this->setReason( $this->EMAIL_VALIDATION_STATUS_DOMAIN_MX_NOT_FOUND );

    	                $this->debug[] = 'No MX Records Found...';
    	            }
                
                }else{

                    $this->markAsInvalid();

                    $this->setReason( $this->EMAIL_VALIDATION_STATUS_DOMAIN_NOT_FOUND );

                    $this->debug[] = 'Invalid Email Domain DNS Found...';
                }   
            }
        
        }else{

            $this->markAsInvalid();

            $this->setReason( $this->EMAIL_VALIDATION_STATUS_SYNTAX_ERROR );

            $this->debug[] = 'Incorrect Email Adddress Found...';
        }

        if ( $this->print ) {
            
            echo '<pre>';
                
                print_r( array_map( 'htmlentities', $this->debug ) );

            echo '</pre>';
        }
    }

    /** 
     * Get Domain part from full email address
     * @return string return only domain
     */ 
    private function getDomainFromEmail( $email )
    {
        $email_address = explode( '@', $email );

        $domain = array_pop( $email_address );

        return $domain;
    }
    
    /** 
     * Get Email username part from full email address
     * @return string return only domain
     */ 
    private function getEmailAccFromEmail( $email )
    {
        $email_address = explode( '@', $email );

        $domain = array_shift( $email_address );

        return $domain;
    }

    // set current email as Valid
    private function markAsValid(){

        $this->status[0] = true;
    }

    // set current email as Invalid
    private function markAsInvalid(){

        $this->status[0] = false;

        $this->status['status'] = 'invalid';

        $this->status['bounce_type'] = 'hard';
    }

    // set current email verify reason
    private function setReason( $reason ){

        $this->status['reasons'] = $reason;
    }
    
    /** 
     * Check Domain MX DNS Records 
     * @return boolean return MX records if found
     */ 
    private function checkDNS()
    {
        return checkdnsrr( $this->domain, 'MX' );
    }

    /** 
     * Get Domain MX Records 
     * @return array return MX records if found or empty array
     */ 
    private function getMXrecords()
    {
        $mx_records = array();
        
        $mx_weights = array();

        // Get the records
        if ( getmxrr( $this->domain, $mx_records, $mx_weights ) ) {
            
            $mx_records = array_combine( $mx_weights, $mx_records );
            
            // records sorted by MX Weight
            ksort( $mx_records );

            if ( ! empty( $mx_records ) ) {
                
                $this->mx_records = $mx_records;
                
                return true;
            }
        }

        return false;
    }

    /** 
     * Connect To Socket Server
     * @return boolean True if connection success
     */ 
    private function connect()
    {
        foreach ( $this->mx_records as $host ) {
            
            $this->stream = @fsockopen( $host, $this->port, $errno, $errstr, $this->stream_timeout_wait );

            if ( $this->stream !== false ) {

                stream_set_timeout( $this->stream, $this->stream_timeout_wait );
                
                stream_set_blocking( $this->stream, 1 ); 

                if ( $this->_streamCode( '220' ) > 0 ) {
                    
                    //$this->debug[] = "Connection success {$host}";
                    
                    break;
                
                } else { 
                    
                    fclose( $this->stream );
                    
                    $this->stream = false;
                } 
            
            }else{

                if ( $errno == 0 ) {
                    
                    $this->debug[] = 'Problem initializing the socket';
                    
                    $this->markAsInvalid();

                    $this->setReason( $this->EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED );
                }
            }
        }

        if ( $this->stream === false ) { 
            
            $this->debug[] = 'All connection fails';
            
            $this->markAsInvalid();

            $this->setReason( $this->EMAIL_VALIDATION_STATUS_SMTP_CONNECTION_FAILED );

            return false;
        }

        return true;
    }

    /** 
     * disconnect from Socket Server
     * @return null
     */ 
    private function disconnect()
    {
        @fclose( $this->stream );
    }

    /** 
     * Validate email
     * @param string $email Email address 
     * @return boolean True if the valid email exist 
     */ 
    private function verify()
    {

        if ( $this->is_domain_catch_all_enabled( $this->getDomainFromEmail( $this->email ) ) ) {

            $this->debug[] = "Email Domain is Catch-All Enabled";
            
            $this->markAsInvalid();

            $this->setReason( $this->EMAIL_VALIDATION_STATUS_CATCH_ALL_SERVER );

            $this->status['status'] = 'catch all';

            $this->status['safe_to_send'] = 'Risky';

            $this->status['email_score'] = '0.5';
        
        }else{

            if ( $this->validate( $this->email ) ) {
                
                /** 
                 * http://www.ietf.org/rfc/rfc0821.txt 
                 * 250 Requested mail action okay, completed 
                 * email address was accepted 
                 */ 
                $this->markAsValid();

                $this->setReason( 'success' );

                $this->status['status'] = 'valid';

                $this->status['safe_to_send'] = 'Yes';

                $this->status['email_score'] = '1';
            
            }else{

                $this->markAsInvalid();

                $this->setReason( 'invalid email address' );
            }
        
        }

        $this->disconnect();
    }

    /** 
     * check if email domain is Cache-All
     * @param string $domain Email domain 
     * @return boolean True if the domain is Cache-All
     */ 
    public function is_domain_catch_all_enabled( $domain )
    {
        $catch_all_check_status = '';

        $random_c = $this->getRandomString();

        $catchAll_email = $random_c . '@' . $domain;
        
        if( $this->db !== null ){

            $email_acc = $this->getEmailAccFromEmail( $this->email );
            
            $email_dom = $this->getDomainFromEmail( $this->email );

            $email_type_read = $this->db->select( "SELECT * FROM email_category WHERE name = '$email_acc' OR name = '$email_dom' " );
                
            if ( $email_type_read && mysqli_num_rows( $email_type_read ) > 0 ) {
                
                $type_row = $email_type_read->fetch_assoc();
                
                $email_type = $type_row['e_type'];
                
                $catch_all_check_status = $type_row['catch_all_check'];

                $this->status['type'] = $email_type;
            }
        }

        $skip_catchall = $this->mx_records;
		
		$public_catch_skip = preg_grep("/(google|outlook|ymail|yahoo|gmail)/i", $skip_catchall);
		
        if(!$public_catch_skip){
            if( $catch_all_check_status != '0' ) {

                $this->debug[] = 'Checking Catch-All Server...';

                $code = $this->validate( $catchAll_email );

                $this->catchAllCehked = true;

                if ( $code ) return true;
            }
         }
        return false;
    }

    /** 
     * Validate email
     * @param string $email Email address 
     * @return boolean True if the valid email exist 
     */ 
    private function validate( $email )
    {

    	$valid = true;

    	if ( $this->catchAllCehked == false ) {
    		
	        $this->_streamQuery( 'HELO ' . $this->getDomainFromEmail( $email ) );
	        
	        if ( $this->_streamCode( '250' ) > 0 ) {

	        	$this->_streamQuery( "MAIL FROM: <{$this->from}>" );
	        	
	        	if ( $this->_streamCode( '250' ) > 0 ) {
	        		
	        		$valid = true;
	        	
	        	}else{

		        	$valid = false;
		        }
	        
	        }else{

	        	$valid = false;
	        }
    	}

    	if ( $valid ) {

        	$this->_streamQuery( "RCPT TO: <{$email}>" );
        	
        	if ( $this->_streamCode( '250' ) > 0 ) {
	        		
	        	$valid = true;
	        
	        }else{

	        	$valid = false;
	        }
        
        	if ( $this->catchAllCehked !== false ) {
	        
		        $this->_streamQuery( "RSET" );

		        $this->_streamQuery( "QUIT" );
	    	}
    	}

        return $valid;
    }

    /** 
     * writes the contents of string to the file stream pointed to by handle 
     * If an error occurs, returns FALSE. 
     * @access protected 
     * @param string $string The string that is to be written 
     * @return string Returns a result code, as an integer. 
     */ 
    protected function _streamQuery( $query )
    {
        $this->debug[] = $query;
        
        return( @fputs( $this->stream, $query . self::CRLF ) );
    }

    /** 
     * Reads all the line long the answer and analyze it. 
     * If an error occurs, returns FALSE 
     * @access protected 
     * @return string Response 
     */ 
    protected function _streamResponse( $timed = 0 ) {

        for( $line = "";; ) {

        	$info = stream_get_meta_data( $this->stream );

        	if ( $info['timed_out'] ) return $line;
            
            if( @feof( $this->stream ) ) return( 0 );
            
            $line .= @fgets( $this->stream, 100 );
            
            $length = strlen( $line );
            
            if( $length >= 2 && substr( $line, $length-2, 2 ) == "\r\n" ) {
              
              $line = substr( $line, 0, $length-2 );

              $this->debug[] = $line;
              
              return( $line );
            }
        }
    }

    /** 
     * Get Response code from Response 
     * @param string $code 
     * @return string 
     */ 
    protected function _streamCode( $code ) {

        while( ( $line = $this->_streamResponse( $this->stream ) ) ) {
        	
        	$end = strcspn( $line, ' -' );
        	
        	$this->last_code = substr( $line, 0, $end );
          	
          	if( strcmp( $this->last_code, $code ) ) return( 0 );
          	
          	if( ! strcmp( substr( $line, strlen( $this->last_code ), 1 ), " " ) ) return( 1 );
        }

        return( 0 );
    }

    private function getRandomString( $length = 10 ) {
        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $charactersLength = strlen( $characters );
        
        $randomString = '';
        
        for ( $i = 0; $i < $length; $i++ ) {
        
            $randomString .= $characters[rand( 0, $charactersLength - 1 )];
        }
        
        return strtolower( $randomString );
    }

    static public function get_site_option( $option = '' ){

    	global $db;

    	if ( $db ) {

    		$site_options_sql = "SELECT * FROM site_options";

    		$site_options_read = $db->select( $site_options_sql );

    		if ( $site_options_read ) {
  
			  $site_options_check = mysqli_num_rows( $site_options_read );
			  
			  if ( $site_options_check > 0 ) {
			  
			    $site_options_row = $site_options_read->fetch_assoc();

			    if ( $option !== '' && isset( $site_options_row[$option] ) ) {
			    	
			    	return $site_options_row[$option];
			    }
			  
			    return $site_options_row;
			  }
			}

			return false;
    	}

    	return false;
    }
}
