<?php

require_once("../BalihooRestClient.php");

// Use the authentication, and url provided below.
$testAuth = '';
$testURL = '';
$filePath = 'testDataLoad1.csv';
$proxy = null;

$balihooClient = new BalihooRestClient($testAuth, $testURL, $proxy);
$balihooClient->setInsecure();

// get a document to load
$data = csvToArray($filePath);

$result = $balihooClient->load($data);
$jobid = $result['jobid'];
print_r("Job ID returned from load: ".$jobid."\nThis can be used to query the status of a load later.\n");

// on the off chance that the load finshes 
sleep(2);
$count = 0;
$results['status'] = "";
while (!stristr($results['status'],"complete")) {
	sleep($count);
	$results = $balihooClient->status($jobid);
	$count++;
	if ($count > 10) {
		print("Example server not processing quickly enough to show completeness in time allotted.\n");
		exit;
	}	
}
print("Status returned complete from jobid:".$jobid."\nThe data in will now be returned in queries.\n");

// utility function to turn a csv file into an array.
function csvToArray($filepath)
{
	// can we open this file
	if (($handle = fopen($filepath, "r")) == FALSE)
	{
		throw new Exception("unable to open file: $filepath");
	}
	$titles = fgetcsv($handle, 10000, ",");
	
	$mapping = array();
	while (($data = fgetcsv($handle, 10000, ",")) !== FALSE)
	{
		if (!is_array($data) || !$data[0])
			continue;
			
		$row = array();
		for ($i=0; $i < count($titles); $i++) {
			if (isset($data[$i])&& isset($titles[$i]))
				$row[$titles[$i]] = $data[$i];
		}
		$mapping[] = $row;
	}
    
	return $mapping;
}
