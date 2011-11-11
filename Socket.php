<?php

/**
 * Socket.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 * @package 	SocketServer
 */
class Socket extends Assembly
  {
    /**
     * Constructor
     * 
     * @param     $authWindow  	(int)		Authentication window, in seconds.
     * @param     $sessWindow	(int) 		Session window, in seconds
     * @param     $errLg   		(string) 	Location of the error log
     */
	function __construct(	$authWindow=300,
	                		$sessWindow=3600,
                    		$genPath="/usr/local/apache/htdocs/sandro/sockets/generatedFiles/",
							$methodsDir="/usr/local/apache/htdocs/sandro/sockets/rpcMethods/"
				   		)
	  {
		// lifespan settings
		$this->authenticateWindow = $authWindow; // five minutes to authenticate
		$this->sessionWindow = $sessWindow; // one hour window created on each action
		$this->generatedFilepath = $genPath;
		$this->methodsDir = $methodsDir;
		
		/*
		 * list of valid access user/pass.  only these
		 * logins will be allowed to call methods
		 */
		$this->serverAccessCredentials = array
		  (
		    "admin" => "wh1t3m0f0"
		  );
	  
		// stores references to all dynamically generated methods (loadRPCMethods())
		$this->rpcMethods = array();
		
		$this->nullChar = chr(0);

		// the main client array; all connections(slots) are tracked here
		$this->clients = Array();
	  }

    /**
     * Attempts to create, bind, and begin listening on the socket
     * 
     * @param     $domain  		(string)	IP of this server
     * @param     $port			(int) 		Port to attempt connection to
     * @param     $maxClients	(int) 		Maximum number of simultaneous clients allowed
     * @param     $lifespan		(int) 		Script lifespan
     */
	function create($domain="localhost", $port=9000, $maxClients=10000, $lifespan=0)
	  {
		$this->domain =	$domain;
		$this->port	= $port;
		$this->maxClients = $maxClients;
		$this->lifespan = $lifespan;
		
		set_time_limit($this->lifespan);
		
		// attempt to create socket
		$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!$this->socket)
		  {
		    $this->shutdown("Unable to create socket > domain:".$this->domain." port:".$this->port);
		  }
		
		// allow reuse
		@socket_setopt($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		
		// attempt to bind to socket
		$this->bound = @socket_bind($this->socket, $this->domain, $this->port);
		if(!$this->bound)
		  {
		    $this->shutdown("Unable to bind to socket > domain:".$this->domain." port:".$this->port." >> ".$this->getLastSocketError()." <<");
		}
		
		// start listening
		$this->listening = @socket_listen($this->socket,$this->maxClients);
		if(!$this->listening)
		  {
		    $this->shutdown("Unable to listen >> ".$this->getLastSocketError()." <<");
		  }	  
		  
		// log start time
		self::System()->log("SI|".self::System()->DateTime->getStamp());
	  }

    /**
     * Will accept a proper rpc method library as xml file.
	 *
	 * Takes all defined methods and translates them into functions,
	 * loading them into the $this->rpcMethods[$fName] collection.
     * 
     * @param     $library 		(string)	Path to methods file
     */
	function loadRPCMethods($library="privateMethods.xml",$generate="true")
	  {
		$xml = $this->methodsDir.$library;

		if(self::System()->setXPathOnFile($xml)) 
		  { 
		    /* begin output buffering, storing commented functional .php versions of the
			 * code being loading through $xml.  Buffer ignored if $generate is false.
			 * This means that all print statements will be writing to this buffer.  These
			 * print statements will mainly create proper comments for the code. 
		     * If $gemerate==true, buffer will be written to generatedFiles/RPCMethods.php
		     * once all local functions have been created.
			 */
		    ob_start();
			
			/* Want to have a description of each of the RPC methodCalls available
			 * to developers.  $methodCalls will store those descriptions, as a 
			 * serialization of $methodData (see below)
			 */
			$methodCalls = array();
			
		    // get all functions
			$func = self::System()->xPath->match("/child::functions/child::*");
			
			// create php bracket
			print "<?php\n";
			
			$fCount = 0;
			
			// dynamically add functions to this object
			foreach($func as $node)
			  {
			    /* $methodCalls (see above) will receive a serialized version of the 
				 * data contained in $methodData at the termination of each iteration
				 * of this containing loop.  $methodData is simply an array 
				 * structure describing the actual structure of the RPC call
				 * this method would require, written to as fields are pulled from the 
				 * RPC library we are currently reading
				 */
			    $methodData = array();
			    $methodData['methodCall'] = array();
			    $methodData['methodCall']['methodName'] = null;
			    $methodData['methodCall']['params'] = array();
			    $methodData['methodCall']['params']['param'] = array();
			  
			    // begin comment block for this function.  Note that we are 
				// using http://sourceforge.net/projects/phpdocu/ formatting
			    print "/**\n"; 
			  
			    // function name
			    $fName = self::System()->xPath->getAttributes($node,"NAME");
				
				// add function name to comment block (as short description)
				print "* ".$fName.".\n*\n"; 
                
				// add method name to rpc methodCall
                $methodData['methodCall']['methodName'] = $fName;

				// get parameters for this methodCall
				$params = self::System()->xPath->match("child::parameters/child::*",$node);
				
				// the actual php code executed on methodCall
				$code = self::System()->xPath->getData($node."/child::code");
				// remove <![CDATA[ ... ]]>
				$code = preg_replace('/<\!\[CDATA\[/', '', $code);
				$code = preg_replace('/\]\]>/', '', $code);
				
				// give each method a reference to System object
				$fCode = 'global $System;';
				
				/*
				 * $_argv made available to function, array containing all passed arguments.
				 */
				$fCode .= '$_argv = func_get_arg(0);';
				
				/*
				 * $_slot is pushed onto argument list by calling method; shift argv and 
				 * leave argv with client-requested list of parameters.
				 */
				$fCode .= '$_slot = array_shift($_argv);';
				
				// tracks which parameter is currently being processed
                $pCount = 0;
					
				foreach($params as $par)
				  {
				    // get the parameter key
					$pKey = self::System()->xPath->getAttributes($par,"KEY");
					
					// bad parameter definition; die
					if($pKey == null)
					  {
					    $err = "Bad parameter definition for :: ".$xml."->".$fName." :: Missing parameter KEY -> ".$par;
					    $this->shutdown($err);
					  }
					  
					// get parameter type (note: TYPE is optional, may be empty)
                    $pType = self::System()->xPath->getAttributes($par,"TYPE");
					
					// set default type to string if no type set
					if($pType == null) { $pType = "string"; }
					
					// keep $pType lowercase as switch{} below is case sensitive
					$pType = strtolower($pType);
					
					// get the parameter default, if any
					$pDefault = self::System()->xPath->getAttributes($par,"DEFAULT");
					
					/* get proper default type and comparison eval for TYPE attribute;
					 * also get a proper datatype $paramValue so that XMLRPC_prepare 
					 * can send back a valid rpc representation for this method;
					 */
					switch($pType)
					  {
					    case "int":
						  $pCheck = 'is_int($'.$pKey.')';
						  $pDef = ($pDefault) ? (int)$pDefault : '(int)""';
						  $paramValue = (int)"";
						break;
						
					    case "double":
						  $pCheck = 'is_float($'.$pKey.')';
						  $pDef = ($pDefault) ? (float)$pDefault : '(float)""';
						  $paramValue = (float)"";
						break;
						
						case "bool":
						  $pCheck = 'is_bool($'.$pKey.')';
						  $pDef = ($pDefault) ? (bool)$pDefault : '(bool)""';
						  $paramValue = (bool)"";
						break; 
						
						case "array":
						case "struct":
						  $pCheck = 'is_array($'.$pKey.')';
						  $pDef = ($pDefault) ? (array)$pDefault : '(array)""';
						  $paramValue = (array)"";
						break; 

						case "datetime.iso8601":
						  $tail = '?/';
						  $pregT = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]*)'.$tail; 
						  $pregUTC = $pregT.'?(Z|[+\-][0-9]{2}:?[0-9]{2})'.$tail;
						  $pCheck = '(preg_match("'.$pregT.'",$'.$pKey.') || preg_match("'.$pregUTC.'",$'.$pKey.'))';
						  $pDef = ($pDefault) ? "'".$pDefault."'" : '(string)""';
						  $paramValue = (string)"";
						break;						
						
						case "base64": 
						  $pCheck = 'is_string($'.$pKey.')';
						  $pDef = ($pDefault) ? "'".$pDefault."'" : '(string)""';
						  $paramValue = (string)"";
						break;
						
						default: // default is 'string'
						  $pCheck = 'is_string($'.$pKey.')';
						  $pDef = ($pDefault) ? "'".$pDefault."'" : '(string)""';
						  $paramValue = (string)"";
						break;
					  }
					  
					// add parameter definition to comment block
                    print "* @param     $".$pKey." 		(".$pType.")	Default:".$pDef."\n";		  
					  
					// store the parameter data for this method call  
			        $methodData['methodCall']['params']['param'][$pCount] = array();
			        $methodData['methodCall']['params']['param'][$pCount]['value'] = array(XMLRPC_prepare($paramValue));
					  
					/*
					 * Add variable definition for this parameter to the function.
					 */
					$fCode .= '$'.$pKey.'= (isset($_argv['.$pCount.'])) ? $_argv['.$pCount.'] : '.$pDef.';';

					// add parameter check function
					$fCode .= 'if('.$pCheck.'==false) { return(false); }';

                    ++$pCount;
				  }

				// no parameters, nullify <params> block
				if($pCount == 0)
				  {
			        $methodData['methodCall']['params'] = null;
				  }
				  
				// add parameter definition to comment block
                print "* @example ".$this->generatedFilepath.basename($xml).".rpcExamples.html\n";						  
				  
				// close comment block
				print "*/\n";
				
				// add the actual function code
				$fCode .= $code;
				
				// add function code to generated function file
				print "function ".$fName."() {\n";
				print "    ".$fCode."\n  }\n\n";
				
				// lose any &amp; type entities
				$fCode = html_entity_decode($fCode); 
				
				// create the function for Socket->rpcMethods
				$this->rpcMethods[$fName] = create_function('',$fCode);
				
				// store the method call example as proper xml
				$methodCalls[$fCount]['name'] = $fName;
				$methodCalls[$fCount]['example'] = XML_serialize($methodData);
				++$fCount;
			  }
			
			// close php bracket
			print "?>\n";
			
			// read then empty buffer
			$data = ob_get_contents();
            ob_clean();	
			
			// now write the output files
			if($generate == "true")
			  {  
			    // write the php functional representation of this method
				$fPath = $this->generatedFilepath.basename($xml).".php";
                $fp = fopen($fPath,"w");
                fwrite($fp,$data);
                fclose($fp);

				// write the rpc description files
			    for($x=0; $x<count($methodCalls); $x++)
				  {
				    $fPath = $this->generatedFilepath.basename($xml).".".$methodCalls[$x]['name'].".example.html";
                    $fp = fopen($fPath,"w");
					
			        if(self::System()->setXPathOnString($methodCalls[$x]['example']))
				      {
				        $data = self::System()->xPath->exportAsHtml('');
					    fwrite($fp,$data);
					  }
				    fclose($fp);
                  }
			  }
		  } 
		else
		  {
		    $this->shutdown("Unable to load Methods");
		  }
		self::System()->log("LM|".$xml);
	  }
	
    /**
     * Starts the socket server.
     */
	function start()
	  {
	  	self::System()->log("SS|".self::System()->DateTime->getStamp());
		
        // Loop continuously
        while(true) 
		  {
		    // without this, cpu usage goes up to 99.9%
			// gotchas? mailto:sandropasquali@yahoo.com
			usleep(1);
			
            // Setup clients listen socket for reading
            $read = Array(0 => $this->socket);
			$c = count($this->clients);
            for($slot = 0; $slot < $c; $slot++)
              {
                if($this->clients[$slot]['sock'] != null)
				  {
				    // check if client has timed out
				    if(!$this->isAlive($slot))
					  {
					    $this->removeClient($slot);
					  }
					else
					  { 
                        $read[$slot + 1] = $this->clients[$slot]['sock'];
					  }
				  }
              }

            // Set up a blocking call to socket_select()
            $ready = @socket_select($read, $write = NULL, $except = NULL, 0);

			if($ready === false)
			  {
			    $this->shutdown("unable to socket_select");
			  }

            /* if a new connection is being made add it to the client array */
            if(in_array($this->socket, $read)) 
		      {
			    $slot = 0;
				while($slot <= $this->maxClients)
                  {
                    if(!isset($this->clients[$slot])) 
				      {
					    // once open slot is found, bind client
                        $this->createConnection($slot);
						
                        break;
                      }
					++$slot;
                  }
                
				if($slot == $this->maxClients)
				  {
				    /* add a notification here */
					
					
				    $this->removeClient($slot);
				  }
				
                if (--$ready <= 0)
                  {
                    continue;
                  }
              } 
		   
            // If a client is trying to write - handle it now
            for ($slot = 0; $slot < count($this->clients); $slot++) 
              {
                if(in_array($this->clients[$slot]['sock'] , $read))
                  {
                    $input = trim(@socket_read($this->clients[$slot]['sock'] , 8192));
					
					switch($input)
					  {
                        case null: 
				          // the client has disconnected.
                          $this->removeClient($slot);
						break;
						
						default:
				          /* 
						   * Do authentication if necessary (level 0 == not authenticated)
					       * ALL attempts go through authenticate until client authenticated.
					       * Towards saftety: bogus method names are sent by client to 
					       * hide real authentication method names
					       */
				          if($this->clients[$slot]['level']==0)
					        {
					          // parse the methodCall
						
		                      $xmlrpc_request = XMLRPC_parse($input);
                              $method = XMLRPC_getMethodName($xmlrpc_request);
                              $params = XMLRPC_getParams($xmlrpc_request);
					   
					          /*
							   * check if this is an admin request; if an 
							   * admin is calling, authentication/commands will
							   * be handled separately.
							   */
                              if($method=="admin")
							    {
                                  $this->adminLogin($slot,$params);
								}
						      else 
							    {
								  /*
		                           * handle standard client authentication
		                           */
                                  $this->authenticate($method,$slot,$params);
								}
					        }
						  else
						    {		self::System()->log($input);
							  /*
							   * if already authenticated, assume that a 
							   * proper method call has been sent.
							   */
                              $this->executeMethod($slot,$input);
							}
				        break;
                      }
                  }
              }
          } 
		$this->shutdown("loop terminated");
	  }
	  
	function adminLogin($slot,$params)
	  {
		// get username and password
		$aUser = array_shift($params);
		$aPass = array_shift($params);
			
		/* 
		 * if bad password, send fault response.
		 * if ok, log in as admin, build out the rpc request
		 * which was sent, execute, return result
		 */
        try
	      {
            if($this->serverAccessCredentials[$aUser] == $aPass)
	          {
			    $this->loginUser($slot,99);
				$methodCall = array_shift($params);
				$methInf = array($slot);
				for($m=0; $m<count($params); $m++)
				  {
					array_push($methInf, $params[$m]);
				  }
				$package = $this->methodCall($methodCall,$methInf);
				$this->sendData($slot,$package);
	          }
			else
			  {
		        throw new Exception("");
			  }
          } 
	    catch(Exception $exception) 
		  {
			$this->sendData($slot,XMLRPC_faultResponse('5002','You are not authorized to access this method.'));
		  }	  
	  }
	  
    function authenticate($method,$slot,$params)
	  {
        $methInf = array($slot,$params[0]);
						
		// call relevant auth method; if method is neither, ignore
		if(($method == "authenticate1") || ($method == "getUserKey"))
		  {
			$this->methodCall("getUserKey",$methInf);
		  }
		else if($method == "authenticate2")
		  { 
			$this->methodCall("checkPassword",$methInf);
		  }	  
	  }

    /**
     * All client rpcMethod requests caught by the server are sent
	 * here to be processed.  
	 *
	 * EXCLUSION: authentication method calls are handled independently;
	 * parsed and modified and sent to .methodCall() directly.
	 *
	 * The $package is parsed and the methodName and parameters are
	 * extracted and passed to methodCall().
     * 
     * @param  	$slot		(int)		Client slot index
	 * @param	$package 	(string)	An RPC package
	 *
	 * @see		start()		
	 * @see 	methodCall()	
     */
	function executeMethod($slot,$package)
	  {
	    // xpath reset to check if valid xml, and to keep as a document object
		if(self::System()->setXPathOnString($package))
		  {
		    // parse the methodCall
		    $xmlrpc_request = XMLRPC_parse($package);
            $method = XMLRPC_getMethodName($xmlrpc_request);
            $params = XMLRPC_getParams($xmlrpc_request);
			
			// prepend slot; unshift did not work for me.  will revisit.  confused.
			for($a=0; $a<count($params); $a++)
			  {
			    $aParams[$a+1] = $params[$a];
			  }
			$aParams[0] = $slot;
			
			// get xmlrpc response package to requested method
			$package = $this->methodCall($method, $aParams);

			// send it back
            $this->sendData($slot,$package);
		  }
		else // setXPathOnString fails
		  {
		    $this->sendData($slot,XMLRPC_faultResponse('1001','unable to parse rpc methodCall: '.print_r($package,true)));
		  }
	  }
	  
    /**
     * Makes the actual RPC method call.
	 *
	 * The $package is parsed and the methodName and parameters are
	 * extracted and passed to methodCall().
     * 
	 * EXCLUSION: authentication method calls are handled independently;
	 * parsed and modified and sent to .methodCall() directly.
	 *
     * @param  	$method		(string)	The method to be called
	 * @param	$args 		(array)		An array of all arguments to method
	 *
	 * @see		executeMethod()		
	 * @see 	start()
	 *
     * @return	(array)		An array containing the returned values from method.
     */	  
	function methodCall($method, $args=array())
	  {
	    // order is important
	    ksort($args);
		
	    // make sure the requested method exists
	    if(isset($this->rpcMethods[$method]))
	      {
		    // log method call name, and # of arguments sent
		    self::System()->log("MC|".$this->clients[$args[0]]['username']."|".$method."|".print_r($args,true));
		  
		    // call, get return values from requested method
            $params = $this->rpcMethods[$method]($args);
			
			/* 
			 * make sure $params is well formed.  If it is not
			 * set (critical error) or is not an array (probably
			 * returning false), send back a faultResponse
			 */
			if(isset($params) && is_array($params))
			  {
			    // wrap response in a standard packet array, and
			    // prepend methodCall string (to identify packet)
				$params = array( 0 => $method, 1 => $params );
			
			    // return methodResponse packet
			    return(XMLRPC_methodResponse($params));
			  }
			else 
			  {
			    if($params == false) // the method was called and returned false
				  {
				    array_shift($args); // don't send back slot id
			        return(XMLRPC_faultResponse('1002','method --'.$method.'-- returned false with arguments ( '.print_r($args,true).')'));
                  }
				else // a malformed response or some other critical error
				  {
				    array_shift($args); // don't send back slot id
			        return(XMLRPC_faultResponse('1003','method --'.$method.'-- returned unrecognized response with arguments ( '.print_r($args,true).')'));
				  }
			  }
		  }
		else // return fault
		  {
	        return(XMLRPC_faultResponse('666','unknown method :: '.$method));
		  }
	  }

    /**
     * Handle any clients attempting to connect.
	 *
     * @param  	$slot	(int)	Client slot index
	 *
	 * @see		start()		
     */	 
	function createConnection($slot)
	  {
	  	$client = &$this->clients[$slot];
		
        $client['sock'] = @socket_accept($this->socket);

		@socket_setopt($client['sock'], SOL_SOCKET, SO_REUSEADDR, 1);

		@socket_getpeername($client['sock'], $host="", $port="");
						
		$client['username'] = "";				
		$client['host'] = $host;
		$client['port'] = $port;	
		$client['connectTime'] = self::System()->DateTime->getStamp();
		$client['lastAction'] = $client['connectTime'];
		$client['uniqueId'] = md5(uniqid(mt_rand(), true));
						
		$this->setUserLevel($slot,0);
		$this->setLifespan($slot,"authenticate");
				
		$this->recordLastClientAction($slot,"created");
		
	    // log client connection			
        $cc = "CC|".$client['host']."|".$client['port']."|".$client['connectTime'];
		self::System()->log($cc);	
	  }
	
    /**
     * Clear any lost client connections, or explicit client disconnections.
	 *
	 * Whenever a client connection is lost or a client is terminated, $this->clients needs 
	 * to be reset, and the socket needs to be closed cleanly.  This is done, and logged.
	 *
     * @param  	$slot	(int)	Client slot index
	 *
	 * @see		start()		
     */	 	
	function removeClient($slot)
	  {
	    // store client info for logging later
	  	$client = $this->clients[$slot];
		
	    // close the socket, killing client
	    @socket_close($this->clients[$slot]['sock']);
		
		// free slot for new connection
	    $this->clients[$slot] = null;
		
	    // log client disconnection			
        $cd = "CD|".$client['host']."|".$client['port']."|".self::System()->DateTime->getStamp();
		self::System()->log($cd);	
	  }
	  
    /**
     * Checks if a given client is connected.
	 *
     * @param  	$slot	(int)	Client slot index
	 *
     * @return	(bool)	Does client exist?
     */	 		  
	function isAlive($slot)
	  {
	    if($this->clientExists($slot))
		  {
	        return($this->clients[$slot]['lifespan'] > self::System()->DateTime->getStamp());
		  }
		else { return(false); }
	  }
	
    /**
     * set user attributes upon successful login
	 *
     * @param  	$slot		(int)		Client slot index.
	 * @param 	$level		(int)		Level index.
	 * @param 	$lifespan	(string)	Lifespan of this session.
	 * @param 	$level		(string)	Display skin for user session.
	 *
     * @see 	createConnection()
     * @see		setUserLevel()
     * @see		setLifespan()
     * @see		setUserSkin()
     */	
    function loginUser($slot,$level=1,$lifespan="session",$skin="default")
	  {
        $this->setUserLevel($slot,$level);
        $this->setLifespan($slot,$lifespan);	
		$this->setUserSkin($slot,$skin);  
	  }

    /**
     * Sets client object's 'level' attribute.
	 *
	 * USER LEVELS:
     * 0 	not authenticated. 
     * 1 	base user, no priviledges. 
	 * 99	admin
	 *
     * @param  	$slot	(int)	Client slot index.
	 * @param 	$level	(int)	Level index.
	 *
     * @see 	createConnection()
     */	 
    function setUserLevel($slot,$level=0)
	  {
	    $this->clients[$slot]['level'] = $level;
	  }
	  
	function setUserSkin($slot,$skin="unknown")
	  {
	    $this->clients[$slot]['skin'] = $skin;
	  }
	  
	function setLifespan($slot,$condition="")
	  {
	    if($this->clientExists($slot))
		  {
	        $c = &$this->clients[$slot];
		    $s = self::System()->DateTime->getStamp();
		
		    switch($condition)
		      {
		        case "authenticate":
			      $c['lifespan'] = $s + $this->authenticateWindow;
			    break;
			
			    case "session":
			      $c['lifespan'] = $s + $this->sessionWindow;
			    break;
			
			    // reaper at the door; immediate death;
			    default:
			      $c['lifespan'] = $s; 
			    break;
			  }
		  }
	  }  
	  
	function getChallenge($slot)
	  {
	    if(isset($this->clients[$slot]))
		  {
	        $c = $this->clients[$slot];
	        return(sha1($c['host'].$c['port'].$c['connectTime'].$c['uniqueId'].$c['username'].$slot));
		  }
	  }
	  
	function recordLastClientAction($slot,$msg="")
	  {
	    if($this->clientExists($slot))
		  {
	        $this->clients[$slot]['lastAction'] = time();	
			self::System()->log($msg."|".$slot."|".$this->clients[$slot]['lastAction']);
		  }
	  }
	  
	function clientExists($id)
	  {
		if(!isset($this->clients[$id]['sock']))
		  {
			return(false);
		  }
		return(true);	
	  }


    function broadcastData($output="", $exclusions = Array())
	  {
	    if($output != "")
		  {
	        for($a = 0; $a < count($this->clients); $a++)
		      {
			    if(!in_array($this->clients[$a],$exclusions))
				  {
				    $this->sendData($a,$output);     
				  }
		      }
		  } 
	  }
	  
	function sendData($slot, $output)
	  {
	    if($this->clientExists($slot))
		  {
	        $output = trim($output).$this->nullChar;
            $sw = @socket_write($this->clients[$slot]['sock'], $output);
			
			if($sw)
			  {
				$this->recordLastClientAction($slot,"SD|".$this->clients[$slot]['username']."|".$sw);
			  }
			else // can't write; client gone or error; remove client
			  {
			    $this->removeClient($slot);
			  }
			  
			return($sw);
		  }
	  }
	  
	function getLastSocketError()
	  {
	    $error = socket_last_error($this->socket);
		return(socket_strerror($error)." :: ".$error);
	  }	  
	
	function shutdown($errMsg="shutdown called")
	  {
        @socket_close($this->socket);
        self::System()->log("SD| ".$errMsg);
		exit;
	  }
  }

?>
