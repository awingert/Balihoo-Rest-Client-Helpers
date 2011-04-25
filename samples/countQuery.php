<?php

require_once("../BalihooRestClient.php");

// Use the authentication, and url provided below.
$testAuth = '';
$testURL = '';
$proxy = null; 

$balihooClient = new BalihooRestClient($testAuth, $testURL, $proxy);
$balihooClient->setInsecure();

// Count an Email List
$results = $balihooClient->count(null, BalihooRestClient::VIEW_EMAIL);
print("Email view count with no query :". print_r($results['count'],true)."\n\n");

$results = $balihooClient->count(null, null);
print("Count of all items :". print_r($results['count'],true)."\n\n"); 

$results = $balihooClient->query(null, BalihooRestClient::VIEW_EMAIL);
print("List of all email addresses :". print_r($results,true)."\n\n");

$results = $balihooClient->query(null, BalihooRestClient::VIEW_EMAIL, 5);
print("List of first 5 email addresses :". print_r($results,true)."\n\n");

$results = $balihooClient->query(null, null);
print("List of all addresses :". print_r($results,true)."\n\n"); 


$query = array("Zip"=>"83702");
$results = $balihooClient->query($query, BalihooRestClient::VIEW_EMAIL);
print("List of addresses with Zip Code = 83702 :". print_r($results,true)."\n\n");

// queries by Zip should use a regular expression as the dataload process may 
// turn a Zip into a Zip9 e.g. 83702-7133
$query = array("Zip"=>array("\$regex"=> "83702.*"));
$results = $balihooClient->query($query, BalihooRestClient::VIEW_EMAIL);
print("List of all addresses in Zip Code 83702 :". print_r($results,true)."\n\n"); 
