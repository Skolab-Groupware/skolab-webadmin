<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2003, Richard Heyes                                     |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// | Co-Author: Damian Fernandez Sosa <damlists@cnba.uba.ar>               |
// +-----------------------------------------------------------------------+

// Warning: This is a patched version of Net_Sieve 1.0.1

require_once('Net/Socket.php');

/**
* TODO
*
* o supportsAuthMech()
*/

/**
* Disconnected state
* @const NET_SIEVE_STATE_DISCONNECTED
*/
define('NET_SIEVE_STATE_DISCONNECTED',  1, true);

/**
* Authorisation state
* @const NET_SIEVE_STATE_AUTHORISATION
*/
define('NET_SIEVE_STATE_AUTHORISATION', 2, true);

/**
* Transaction state
* @const NET_SIEVE_STATE_TRANSACTION
*/
define('NET_SIEVE_STATE_TRANSACTION',   3, true);

/**
* A class for talking to the timsieved server which
* comes with Cyrus IMAP. the HAVESPACE
* command which appears to be broken (Cyrus 2.0.16).
*
* @author  Richard Heyes <richard@php.net>
* @author  Damian Fernandez Sosa <damlists@cnba.uba.ar>
* @access  public
* @version 0.9.1
* @package Net_Sieve
*/

class Net_Sieve
{
    /**
    * The socket object
    * @var object
    */
    var $_sock;

    /**
    * Info about the connect
    * @var array
    */
    var $_data;

    /**
    * Current state of the connection
    * @var integer
    */
    var $_state;

    /**
    * Constructor error is any
    * @var object
    */
    var $_error;


    /**
    * To allow class debuging
    * @var boolean
    */
    var $_debug = false;


    /**
    * The auth methods this class support
    * @var array
    */

    var $supportedAuthMethods=array('DIGEST-MD5', 'CRAM-MD5', 'PLAIN' , 'LOGIN');
    //if you have problems using DIGEST-MD5 authentication  please commente the line above and discomment the following line
    //var $supportedAuthMethods=array( 'CRAM-MD5', 'PLAIN' , 'LOGIN');

    //var $supportedAuthMethods=array( 'PLAIN' , 'LOGIN');


    /**
    * The auth methods this class support
    * @var array
    */
    var $supportedSASLAuthMethods=array('DIGEST-MD5', 'CRAM-MD5');



    /**
    * Handles posible referral loops
    * @var array
    */
    var $_maxReferralCount = 15;

    /**
    * Constructor
    * Sets up the object, connects to the server and logs in. stores
    * any generated error in $this->_error, which can be retrieved
    * using the getError() method.
    *
    * @access public
    * @param  string $user      Login username
    * @param  string $pass      Login password
    * @param  string $host      Hostname of server
    * @param  string $port      Port of server
    * @param  string $logintype Type of login to perform
    * @param  string $euser     Effective User (if $user=admin, login as $euser)
    */
    function Net_Sieve($user = null , $pass  = null , $host = 'localhost', $port = 2000, $logintype = '', $euser = '', $debug = false)
    {
        $this->_state = NET_SIEVE_STATE_DISCONNECTED;
        $this->_data['user'] = $user;
        $this->_data['pass'] = $pass;
        $this->_data['host'] = $host;
        $this->_data['port'] = $port;
        $this->_data['logintype'] = $logintype;
        $this->_data['euser'] = $euser;
        $this->_sock = &new Net_Socket();
        $this->_debug  = $debug;
        /*
        * Include the Auth_SASL package.  If the package is not available,
        * we disable the authentication methods that depend upon it.
        */
        if ((@include_once 'Auth/SASL.php') === false) {
            if($this->_debug){
                echo "AUTH_SASL NOT PRESENT!\n";
            }
            foreach($this->supportedSASLAuthMethods as $SASLMethod){
                $pos = array_search( $SASLMethod, $this->supportedAuthMethods );
                if($this->_debug){
                    echo "DISABLING METHOD $SASLMethod\n";
                }
                unset($this->supportedAuthMethods[$pos]);
            }
        }
        if( ($user != null) && ($pass != null) ){
            $this->_error = $this->_handleConnectAndLogin();
        }
    }



    /**
    * Handles the errors the class can find
    * on the server
    *
    * @access private
    * @return PEAR_Error
    */

    function _raiseError($msg, $code)
    {
    include_once 'PEAR.php';
    return PEAR::raiseError($msg, $code);
    }





    /**
    * Handles connect and login.
    * on the server
    *
    * @access private
    * @return mixed Indexed array of scriptnames or PEAR_Error on failure
    */
    function _handleConnectAndLogin(){
        if (PEAR::isError($res = $this->connect($this->_data['host'] , $this->_data['port'] ))) {
            return $res;
        }
        if (PEAR::isError($res = $this->login($this->_data['user'], $this->_data['pass'], $this->_data['logintype'] , $this->_data['euser'] ) ) ) {
            return $res;
        }
        return true;

    }




    /**
    * Returns an indexed array of scripts currently
    * on the server
    *
    * @access public
    * @return mixed Indexed array of scriptnames or PEAR_Error on failure
    */
    function listScripts()
    {
        if (is_array($scripts = $this->_cmdListScripts())) {
            $this->_active = $scripts[1];
            return $scripts[0];
        } else {
            return $scripts;
        }
    }

    /**
    * Returns the active script
    *
    * @access public
    * @return mixed The active scriptname or PEAR_Error on failure
    */
    function getActive()
    {
        if (!empty($this->_active)) {
            return $this->_active;

        } elseif (is_array($scripts = $this->_cmdListScripts())) {
            $this->_active = $scripts[1];
            return $scripts[1];
        }
    }

    /**
    * Sets the active script
    *
    * @access public
    * @param  string $scriptname The name of the script to be set as active
    * @return mixed              true on success, PEAR_Error on failure
    */
    function setActive($scriptname)
    {
        return $this->_cmdSetActive($scriptname);
    }

    /**
    * Retrieves a script
    *
    * @access public
    * @param  string $scriptname The name of the script to be retrieved
    * @return mixed              The script on success, PEAR_Error on failure
    */
    function getScript($scriptname)
    {
        return $this->_cmdGetScript($scriptname);
    }

    /**
    * Adds a script to the server
    *
    * @access public
    * @param  string $scriptname Name of the script
    * @param  string $script     The script
    * @param  bool   $makeactive Whether to make this the active script
    * @return mixed              true on success, PEAR_Error on failure
    */
    function installScript($scriptname, $script, $makeactive = false)
    {
        if (PEAR::isError($res = $this->_cmdPutScript($scriptname, $script))) {
            return $res;

        } elseif ($makeactive) {
            return $this->_cmdSetActive($scriptname);

        } else {
            return true;
        }
    }

    /**
    * Removes a script from the server
    *
    * @access public
    * @param  string $scriptname Name of the script
    * @return mixed              True on success, PEAR_Error on failure
    */
    function removeScript($scriptname)
    {
        return $this->_cmdDeleteScript($scriptname);
    }

    /**
    * Returns any error that may have been generated in the
    * constructor
    *
    * @access public
    * @return mixed False if no error, PEAR_Error otherwise
    */
    function getError()
    {
        return PEAR::isError($this->_error) ? $this->_error : false;
    }

    /**
    * Handles connecting to the server and checking the
    * response is valid.
    *
    * @access private
    * @param  string $host Hostname of server
    * @param  string $port Port of server
    * @return mixed        True on success, PEAR_Error otherwise
    */
    function connect($host, $port)
    {
        if (NET_SIEVE_STATE_DISCONNECTED != $this->_state) {
            $msg='Not currently in DISCONNECTED state';
            $code=1;
            return $this->_raiseError($msg,$code);
        }

        if (PEAR::isError($res = $this->_sock->connect($host, $port, null, 5))) {
            return $res;
        }

        if(PEAR::isError($res = $this->_doCmd() )) {
            $msg='Failed to connect, server said: ' . $res->getMessage();
            $code=2;
            return $this->_raiseError($msg,$code);
        }
        // Get logon greeting/capability and parse
        $this->_parseCapability($res);
        $this->_state = NET_SIEVE_STATE_AUTHORISATION;
        return true;
    }

    /**
    * Logs into server.
    *
    * @access public
    * @param  string $user      Login username
    * @param  string $pass      Login password
    * @param  string $logintype Type of login method to use
    * @param  string $euser     Effective UID (perform on behalf of $euser)
    * @return mixed             True on success, PEAR_Error otherwise
    */
    function login($user, $pass, $logintype = null , $euser = '')
    {
        if (NET_SIEVE_STATE_AUTHORISATION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
//          return PEAR::raiseError('Not currently in AUTHORISATION state');
        }

        if(PEAR::isError($res=$this->_cmdAuthenticate($user , $pass , $logintype, $euser ) ) ){
            return $res;
        }
/*
        if (PEAR::isError($res = $this->_doCmd() )) {
            return $res;
        }
*/
        $this->_state = NET_SIEVE_STATE_TRANSACTION;
        return true;
    }



     /* Handles the authentication using any known method
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The method to use ( if $usermethod == '' then the class chooses the best method (the stronger is the best ) )
     * @param string The effective uid to authenticate as.
     *
     * @return mixed  string or PEAR_Error
     *
     * @access private
     * @since  1.0
     */
    function _cmdAuthenticate($uid , $pwd , $userMethod = null , $euser = '' )
    {


        if ( PEAR::isError( $method = $this->_getBestAuthMethod($userMethod) ) ) {
            return $method;
        }
        switch ($method) {
            case 'DIGEST-MD5':
                $result = $this->_authDigest_MD5( $uid , $pwd , $euser );
                return $result;
                break;
            case 'CRAM-MD5':
                $result = $this->_authCRAM_MD5( $uid , $pwd, $euser);
                break;
            case 'LOGIN':
                $result = $this->_authLOGIN( $uid , $pwd , $euser );
                break;
            case 'PLAIN':
                $result = $this->_authPLAIN( $uid , $pwd , $euser );
                break;
            default :
                $result = new PEAR_Error( "$method is not a supported authentication method" );
                break;
        }


        if (PEAR::isError($res = $this->_doCmd() )) {
            return $res;
        }
        return $res;
    }









     /* Authenticates the user using the PLAIN method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authPLAIN($user, $pass , $euser )
    {

        if ($euser != '') {
            $cmd=sprintf('AUTHENTICATE "PLAIN" "%s"', base64_encode($euser . chr(0) . $user . chr(0) . $pass ) ) ;
        } else {
            $cmd=sprintf('AUTHENTICATE "PLAIN" "%s"', base64_encode( chr(0) . $user . chr(0) . $pass ) );
        }
        return  $this->_sendCmd( $cmd ) ;

    }



     /* Authenticates the user using the PLAIN method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The effective uid to authenticate as.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authLOGIN($user, $pass , $euser )
    {
        $this->_sendCmd('AUTHENTICATE "LOGIN"');
        $this->_doCmd(sprintf('"%s"', base64_encode($user)));
        $this->_doCmd(sprintf('"%s"', base64_encode($pass)));

    }




     /* Authenticates the user using the CRAM-MD5 method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The cmdID.
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authCRAM_MD5($uid, $pwd, $euser)
    {
    /*
        if ( PEAR::isError($error = $this->_sendCmd( 'AUTHENTICATE "CRAM-MD5"' ) ) ) {
            $this->_error=$error;
            return $error;
        }
    */

        if ( PEAR::isError( $challenge = $this->_doCmd( 'AUTHENTICATE "CRAM-MD5"' ) ) ) {
            $this->_error=challenge ;
            return challenge ;
        }
        $challenge = base64_decode( $challenge );
        $cram = &Auth_SASL::factory('crammd5');
        $auth_str = base64_encode( $cram->getResponse( $uid , $pwd , $challenge ) );
        if ( PEAR::isError($error = $this->_sendStringResponse( $auth_str ) ) ) {
            $this->_error=$error;
            return $error;
        }

    }



     /* Authenticates the user using the DIGEST-MD5 method.
     *
     * @param string The userid to authenticate as.
     * @param string The password to authenticate with.
     * @param string The efective user
     *
     * @return array Returns an array containing the response
     *
     * @access private
     * @since  1.0
     */
    function _authDigest_MD5($uid, $pwd, $euser)
    {
        /*
        if ( PEAR::isError($error = $this->_sendCmd( 'AUTHENTICATE "DIGEST-MD5"' ) ) ) {
            $this->_error=$error;
            return $error;
        }
        */

        if ( PEAR::isError( $challenge = $this->_doCmd('AUTHENTICATE "DIGEST-MD5"') ) ) {
            $this->_error=challenge ;
            return challenge ;
        }
        $challenge = base64_decode( $challenge );
        $digest = &Auth_SASL::factory('digestmd5');
        $auth_str = base64_encode($digest->getResponse($uid, $pwd, $challenge, "localhost", "sieve" , $euser));

        if ( PEAR::isError($error = $this->_sendStringResponse( $auth_str  ) ) ) {
            $this->_error=$error;
            return $error;
        }

///*

        if ( PEAR::isError( $challenge = $this->_doCmd() ) ) {
            $this->_error=$challenge ;
            return $challenge ;
        }

	if( strtoupper(substr($challenge,0,2))== 'OK' ){
		return true;
	}
	
//echo "CHALL:$challenge\n";
//*/
	/*
         * We don't use the protocol's third step because SIEVE doesn't allow
         * subsequent authentication, so we just silently ignore it.
         */

///*	
        if ( PEAR::isError($error = $this->_sendStringResponse( '' ) ) ) {
            $this->_error=$error;
            return $error;
        }

	if (PEAR::isError($res = $this->_doCmd() )) {
            return $res;
        }

//*/	
    }




    /**
    * Removes a script from the server
    *
    * @access private
    * @param  string $scriptname Name of the script to delete
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdDeleteScript($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }
        if (PEAR::isError($res = $this->_doCmd(sprintf('DELETESCRIPT "%s"', $scriptname) ) )) {
            return $res;
        }
        return true;
    }

    /**
    * Retrieves the contents of the named script
    *
    * @access private
    * @param  string $scriptname Name of the script to retrieve
    * @return mixed              The script if successful, PEAR_Error otherwise
    */
    function _cmdGetScript($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('GETSCRIPT "%s"', $scriptname) ) ) ) {
            return $res;
        }

        return preg_replace('/{[0-9]+}\r\n/', '', $res);
    }

    /**
    * Sets the ACTIVE script, ie the one that gets run on new mail
    * by the server
    *
    * @access private
    * @param  string $scriptname The name of the script to mark as active
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdSetActive($scriptname)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('SETACTIVE "%s"', $scriptname) ) ) ) {
            return $res;
        }

        $this->_activeScript = $scriptname;
        return true;
    }

    /**
    * Sends the LISTSCRIPTS command
    *
    * @access private
    * @return mixed Two item array of scripts, and active script on success,
    *               PEAR_Error otherwise.
    */
    function _cmdListScripts()
    {

        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in AUTHORISATION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        $scripts = array();
        $activescript = null;

        if (PEAR::isError($res = $this->_doCmd('LISTSCRIPTS'))) {
            return $res;
        }

        $res = explode("\r\n", $res);

        foreach ($res as $value) {
            if (preg_match('/^"(.*)"( ACTIVE)?$/i', $value, $matches)) {
                $scripts[] = $matches[1];
                if (!empty($matches[2])) {
                    $activescript = $matches[1];
                }
            }
        }

        return array($scripts, $activescript);
    }

    /**
    * Sends the PUTSCRIPT command to add a script to
    * the server.
    *
    * @access private
    * @param  string $scriptname Name of the new script
    * @param  string $scriptdata The new script
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function _cmdPutScript($scriptname, $scriptdata)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in TRANSACTION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf("PUTSCRIPT \"%s\" {%d+}\r\n%s", $scriptname, strlen($scriptdata),$scriptdata ) ))) {
            return $res;
        }

        return true;
    }

    /**
    * Sends the LOGOUT command and terminates the connection
    *
    * @access private
    * @return mixed True on success, PEAR_Error otherwise
    */
    function _cmdLogout($sendLogoutCMD=true)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        if($sendLogoutCMD){
            if (PEAR::isError($res = $this->_doCmd('LOGOUT'))) {
                return $res;
            }
        }

        $this->_sock->disconnect();
        $this->_state = NET_SIEVE_STATE_DISCONNECTED;
        return true;
    }

    /**
    * Sends the CAPABILITY command
    *
    * @access private
    * @return mixed True on success, PEAR_Error otherwise
    */
    function _cmdCapability()
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in TRANSACTION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        if (PEAR::isError($res = $this->_doCmd('CAPABILITY'))) {
            return $res;
        }
        $this->_parseCapability($res);
        return true;
    }


    /**
    * Checks if the server has space to store the script
    * by the server
    *
    * @access public
    * @param  string $scriptname The name of the script to mark as active
    * @return mixed              True on success, PEAR_Error otherwise
    */
    function haveSpace($scriptname,$quota)
    {
        if (NET_SIEVE_STATE_TRANSACTION != $this->_state) {
            $msg='Not currently in TRANSACTION state';
            $code=1;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently in TRANSACTION state');
        }

        if (PEAR::isError($res = $this->_doCmd(sprintf('HAVESPACE "%s" %s', $scriptname, $quota) ) ) ) {
        //if (PEAR::isError($res = $this->_doCmd(sprintf('HAVESPACE %d "%s"',  $quota,$scriptname ) ) ) ) {
            return $res;
        }

        return true;
    }




    /**
    * Parses the response from the capability command. Storesq
    * the result in $this->_capability
    *
    * @access private
    */
    function _parseCapability($data)
    {
        $data = preg_split('/\r?\n/', $data, -1, PREG_SPLIT_NO_EMPTY);

        for ($i = 0; $i < count($data); $i++) {
            if (preg_match('/^"([a-z]+)" ("(.*)")?$/i', $data[$i], $matches)) {
                switch (strtolower($matches[1])) {
                    case 'implementation':
                        $this->_capability['implementation'] = $matches[3];
                        break;

                    case 'sasl':
                        $this->_capability['sasl'] = preg_split('/\s+/', $matches[3]);
                        break;

                    case 'sieve':
                        $this->_capability['extensions'] = preg_split('/\s+/', $matches[3]);
                        break;

                    case 'starttls':
                        $this->_capability['starttls'] = true;
                }
            }
        }
    }

    /**
    * Sends a command to the server
    *
    * @access private
    * @param string $cmd The command to send
    */
    function _sendCmd($cmd)
    {
        $status = $this->_sock->getStatus();
        if (PEAR::isError($status) || $status['eof']) {
            return new PEAR_Error( 'Failed to write to socket: (connection lost!) ' );
        }
        if ( PEAR::isError( $error = $this->_sock->write( $cmd . "\r\n" ) ) ) {
            return new PEAR_Error( 'Failed to write to socket: ' . $error->getMessage() );
        }

        if( $this->_debug ){
            // C: means this data was sent by  the client (this class)
            echo "C:$cmd\n";
        }
        return true;


    }



    /**
    * Sends a string response to the server
    *
    * @access private
    * @param string $cmd The command to send
    */
    function _sendStringResponse($str)
    {
        $response='{' .  strlen($str) . "+}\r\n" . $str  ;
        return $this->_sendCmd($response);
    }




    function _recvLn()
    {
        $lastline='';
        if (PEAR::isError( $lastline = $this->_sock->gets( 8192 ) ) ) {
            return new PEAR_Error('Failed to write to socket: ' . $lastline->getMessage() );
        }
        $lastline=rtrim($lastline);
        if($this->_debug){
            // S: means this data was sent by  the IMAP Server
            echo "S:$lastline\n" ;
        }

/*        if( $lastline === '' ){
            return new PEAR_Error('Failed to receive from the  socket: '  );
        }
*/
        return $lastline;
    }





    /**
    * Send a command and retrieves a response from the server.
    *
    *
    * @access private
    * @param string $cmd The command to send
    * @return mixed Reponse string if an OK response, PEAR_Error if a NO response
    */
    function _doCmd($cmd = '' )
    {

        $referralCount=0;
        while($referralCount < $this->_maxReferralCount ){


            if($cmd != '' ){
                if(PEAR::isError($error = $this->_sendCmd($cmd) )) {
                    return $error;
                }
            }
            $response = '';

            while (true) {
                    if(PEAR::isError( $line=$this->_recvLn() )){
                        return $line;
                    }
                    if ('ok' === strtolower(substr($line, 0, 2))) {
                        $response .= $line;
                        return rtrim($response);

                    } elseif ('no' === strtolower(substr($line, 0, 2))) {
                        // Check for string literal error message
                        if (preg_match('/^no {([0-9]+)\+?}/i', $line, $matches)) {
                            $line .= str_replace("\r\n", ' ', $this->_sock->read($matches[1] + 2 ));
                            if($this->_debug){
                                echo "S:$line\n";
                            }
                        }
                        $msg=trim($response . substr($line, 2));
                        $code=3;
                        return $this->_raiseError($msg,$code);
                        //return PEAR::raiseError(trim($response . substr($line, 2)));
                    } elseif ('bye' === strtolower(substr($line, 0, 3))) {

                        if(PEAR::isError($error = $this->disconnect(false) ) ){
                            $msg="Can't handle bye, The error was= " . $error->getMessage() ;
                            $code=4;
                            return $this->_raiseError($msg,$code);
                            //return PEAR::raiseError("Can't handle bye, The error was= " . $error->getMessage() );
                        }
                        if (preg_match('/^bye \(referral "([^"]+)/i', $line, $matches)) {
                            // Check for referral, then follow it.  Otherwise, carp an error.
                            $this->_data['host'] = $matches[1];
                            if (PEAR::isError($error = $this->_handleConnectAndLogin() ) ){
                                $msg="Can't follow referral to " . $this->_data['host'] . ", The error was= " . $error->getMessage() ;
                                $code=5;
                                return $this->_raiseError($msg,$code);
                                //return PEAR::raiseError("Can't follow referral to " . $this->_data['host'] . ", The error was= " . $error->getMessage() );
                            }
                            break;
                            // Retry the command
                            if(PEAR::isError($error = $this->_sendCmd($cmd) )) {
                                return $error;
                            }
                            continue;
                        }
                        $msg=trim($response . $line);
                        $code=6;
                        return $this->_raiseError($msg,$code);
                        //return PEAR::raiseError(trim($response . $line));
                    } elseif (preg_match('/^{([0-9]+)\+?}/i', $line, $matches)) {
                        // Matches String Responses.
                        $line = $this->_sock->read($matches[1] + 2 );
                        if($this->_debug){
                            echo "S:$line\n";
                        }
                        return $line;
                    }
                    $response .= $line . "\r\n";
                    $referralCount++;
                }
        }
        $msg="Max referral count reached ($referralCount times) Cyrus murder loop error?";
        $code=7;
        return $this->_raiseError($msg,$code);
        //return PEAR::raiseError("Max referral count reached ($referralCount times) Cyrus murder loop error?" );
    }




    /**
    * Sets the bebug state
    *
    * @access public
    * @return void
    */
    function setDebug($debug=true)
    {
        $this->_debug=$debug;
    }

    /**
    * Disconnect from the Sieve server
    *
    * @access public
    * @param  string $scriptname The name of the script to be set as active
    * @return mixed              true on success, PEAR_Error on failure
    */
    function disconnect($sendLogoutCMD=true)
    {
        return $this->_cmdLogout($sendLogoutCMD);
    }


    /**
     * Returns the name of the best authentication method that the server
     * has advertised.
     *
     * @param string if !=null,authenticate with this method ($userMethod).
     *
     * @return mixed    Returns a string containing the name of the best
     *                  supported authentication method or a PEAR_Error object
     *                  if a failure condition is encountered.
     * @access private
     * @since  1.0
     */
    function _getBestAuthMethod($userMethod = null)
    {

       if( isset($this->_capability['sasl']) ){
           $serverMethods=$this->_capability['sasl'];
       }else{
           // if the server don't send an sasl capability fallback to login auth
           //return 'LOGIN';
           return new PEAR_Error("This server don't support any Auth methods SASL problem?");
       }

        if($userMethod != null ){
            $methods = array();
            $methods[] = $userMethod;
        }else{

            $methods = $this->supportedAuthMethods;
        }
        if( ($methods != null) && ($serverMethods != null)){
            foreach ( $methods as $method ) {
                if ( in_array( $method , $serverMethods ) ) {
                    return $method;
                }
            }
            $serverMethods=implode(',' , $serverMethods );
            $myMethods=implode(',' ,$this->supportedAuthMethods);
            return new PEAR_Error("$method NOT supported authentication method!. This server " .
                "supports these methods= $serverMethods, but I support $myMethods");
        }else{
            return new PEAR_Error("This server don't support any Auth methods");
        }
    }





    /**
    * Return the list of extensions the server supports
    *
    * @access public
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function getExtensions()
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        return $this->_capability['extensions'];
    }





    /**
    * Return true if tyhe server has that extension
    *
    * @access public
    * @param string  the extension to compare
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function hasExtension($extension)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        if(is_array($this->_capability['extensions'] ) ){
            foreach( $this->_capability['extensions'] as $ext){
                if( trim( strtolower( $ext ) ) === trim( strtolower( $extension ) ) )
                    return true;
            }
        }
        return false;
    }



    /**
    * Return the list of auth methods the server supports
    *
    * @access public
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function getAuthMechs()
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }
        if(!isset($this->_capability['sasl']) ){
            $this->_capability['sasl']=array();
        }
        return $this->_capability['sasl'];
    }





    /**
    * Return true if tyhe server has that extension
    *
    * @access public
    * @param string  the extension to compare
    * @return mixed              array  on success, PEAR_Error on failure
    */
    function hasAuthMech($method)
    {
        if (NET_SIEVE_STATE_DISCONNECTED === $this->_state) {
            $msg='Not currently connected';
            $code=7;
            return $this->_raiseError($msg,$code);
            //return PEAR::raiseError('Not currently connected');
        }

        if(is_array($this->_capability['sasl'] ) ){
            foreach( $this->_capability['sasl'] as $ext){
                if( trim( strtolower( $ext ) ) === trim( strtolower( $method ) ) )
                    return true;
            }
        }
        return false;
    }





}
?>
