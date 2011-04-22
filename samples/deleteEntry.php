<?php

require_once("../BalihooRestClient.php");

// Use the authentication, and url provided below.
$testAuth = '';
$testURL = '';
$proxy = null;

$balihooClient = new BalihooRestClient($testAuth, $testURL, $proxy);
$balihooClient->setInsecure();

$results = $balihooClient->count(null, null);
if ($results['count'] == 0) {
	echo("Exiting: no items to delete\n");
	exit;
}

print("Count of all items before delete :". print_r($results['count'],true)."\n\n"); 

$currentContacts = $balihooClient->query(null, null);
$results = $balihooClient->get($currentContacts[0]['id']);
print("deleting contact id: ".$currentContacts[0]['id']."\n");

$results = $balihooClient->delete($currentContacts[0]['id']);

// see what happens if we query this item again.
$results = $balihooClient->get($currentContacts[0]['id']);
if ($results['result'] != 'null')
	print("ERROR: There should be no results here: ". print_r($results,true)."\n");

$results = $balihooClient->count(null, null);
print("Count of all items after delete:". print_r($results['count'],true)."\n\n"); 
