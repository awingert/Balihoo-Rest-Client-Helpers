<?php
/*
 * Balihoo Rest Helper lib
 * 
 * Verion 0.1 Beta
 * 
 * Licensed under the Apache license.  See attached license file.
 */


class BalihooRestException extends Exception {}


class BalihooRestClient 
{
	const COMMAND_LOAD = 'load';
	const COMMAND_COUNT = 'count';
	const COMMAND_DISTINCT = 'distinct';
	const COMMAND_QUERY = 'query';
	const COMMAND_STATUS = 'status';
	const COMMAND_GET = 'get';
	const COMMAND_DELETE = 'delete';
	
	const VIEW_EMAIL = 'Email';
	const VIEW_DIRECTMAIL = 'DirectMail';
	const VIEW_MAINPHONE = 'MainPhone';
	const VIEW_MOBILE = 'MobilePhone';	
	
	var $authKey;
	var $proxy;
	var $url;
	var $insecure = false;
	var $raw_http_response;
	
	public function __construct($authKey, $url, $proxy= null) 
	{
		$this->authKey = $authKey;
		$this->proxy = $proxy;
		$this->url = $url;
	}
	
	// WARNING: only call this in sample or test environments with sample/test authKeys.  
	// do not send production authKeys over unencrypted channels.
	public function setInsecure()
	{
		$this->insecure = true;
	}
	
	public function load($data)
	{
		$command = BalihooRestClient::COMMAND_LOAD;
		
		$queryEncode = json_encode($data);		
		$query = array('command'=>$command, 'contacts'=>$queryEncode);
		
		$result = $this->runQuery($command, $query, $this->getFullUrl());
		return $result;
	}
	
	public function count($inQuery, $view)
	{
		$command = BalihooRestClient::COMMAND_COUNT;
		
		$queryEncode = json_encode($inQuery);
		$query = array('command'=>$command, 'query'=>$queryEncode, 'view'=>$view);
		
		$result = $this->runQuery($command, $query, $this->getFullUrl());
		return $result;
	}

	public function query($inQuery, $view, $limit = null)
 	{
 		$command = BalihooRestClient::COMMAND_QUERY;
 		$queryEncode = json_encode($inQuery);
		$query = array('command'=>$command, 'query'=>$queryEncode, 'view'=>$view, 'limit'=>$limit);
 		
		$result = $this->runQuery($command, $query,  $this->getFullUrl());
		return $result;
	}
	
	public function status($id)
	{
		$command = BalihooRestClient::COMMAND_STATUS;
		$overrideurl = $this->getFullUrl()."/".$command."/".$id;
		$result = $this->runQuery($command, null, $overrideurl);
		return $result;	
	}

	public function get($id)
	{
		$command = BalihooRestClient::COMMAND_STATUS;
		$overrideurl = $this->getFullUrl()."/".$id;
		$result = $this->runQuery($command, null, $overrideurl);
		return $result;	
	}
	
	public function delete($id)
	{
		$command = BalihooRestClient::COMMAND_DELETE;
		$overrideurl = $this->getFullUrl()."/".$id;
		$result = $this->runQuery($command, null, $overrideurl);
		return $result;	
	}

	public function distinct($keys)
	{
		$command = self::COMMAND_DISTINCT;
		$keysEncode = json_encode($keys);
		$data = array('command'=>$command, 'keys'=>$keysEncode);
		
		$result = $this->runQuery($command, $data, $this->getFullUrl());
		return $result;
	}

	
	
	private function runQuery($command, $data, $url) 
	{
		$command = strtolower($command);
		
		$header = array("X-Auth-Token:".$this->authKey);
		$method = $this->getMethod($command);
		$s = curl_init();
		
		curl_setopt($s,CURLOPT_URL,$url);
		
		curl_setopt($s,CURLOPT_HTTPHEADER,$header);
		
		if (isset($this->proxy))
			curl_setopt($s, CURLOPT_PROXY, $this->proxy); 
			
	    curl_setopt($s,CURLOPT_HEADER, true); 
	    curl_setopt($s,CURLOPT_TIMEOUT,4);
	   
		switch(strtoupper($method)) {
			case "GET":
				curl_setopt($s, CURLOPT_HTTPGET, TRUE);
				break;
			case "POST":
				curl_setopt($s, CURLOPT_POST, 1);
				curl_setopt($s, CURLOPT_POSTFIELDS, $data);
				break;
			case "DELETE":
				curl_setopt($s, CURLOPT_CUSTOMREQUEST, $method);
				break;
			default:
				// PUT/DELETE/etc need implementation
				throw new BalihooRestException("unimplemented method");
		}
	    curl_setopt($s,CURLOPT_MAXREDIRS,4);
	    curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
		
	    
	    $response = curl_exec($s);
		$error = curl_error($s);

		$result = array();        
        $header_size = curl_getinfo($s,CURLINFO_HEADER_SIZE);
        $result['header'] = substr($response, 0, $header_size);
        $result['body'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo($s,CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($s,CURLINFO_EFFECTIVE_URL);
        
        $this->checkError($error, $result);
        
	    $resultsDecode = json_decode($result['body'], true);
	    
	    if (!$resultsDecode || !is_array($resultsDecode)) {
	    	// make sure the results look like a json decoded object
	    	$resultsDecode = array();
	    	$resultsDecode['result'] = $result['body'];
	    }
	    
	    $this->raw_http_response = $result;
	    curl_close($s);
	    
	    return $resultsDecode;
	}
	
	private function checkError($error, $result)
	{
	    if ( $error != "" )
        {
            throw new BalihooRestException("Curl error -- ".print_r($error,true));
        }

        // result code starting in 4
        if (substr((string)$result['http_code'],0,1) == "4") {
            throw new BalihooRestException("Error using Balihoo Rest Client :".$result['body']);
		}
	}
	
	private function getFullUrl() 
	{
		if (strstr($this->url, 'http') === false) {
			if ($this->insecure)
				$fullUrl = "http://".$this->url;
			else
				$fullUrl = "https://".$this->url;
		} else {
			$fullUrl = $this->url;
		}
			
		return $fullUrl;
	}
	
	private function getMethod($command)
	{
		switch(strtolower($command)) {
			case BalihooRestClient::COMMAND_LOAD: 
			case BalihooRestClient::COMMAND_DISTINCT: 
			case BalihooRestClient::COMMAND_QUERY: 
			case BalihooRestClient::COMMAND_COUNT: 
				$method = 'POST';
				break;
			case BalihooRestClient::COMMAND_STATUS: 
			case BalihooRestClient::COMMAND_GET: 
				$method = 'GET'; 
				break;
			case BalihooRestClient::COMMAND_DELETE: 
				$method = 'DELETE'; 
				break;
			default: 
				throw new BalihooRestException("No method associatted with Command: ".$command);
		}
		
		return $method;	
	}
	
}

