<?php
function PostTax($order_data)
{
	require_once('AvaTax.php');

	//new ATConfig($order_data["environment"], array('url'=>$order_data["service_url"], 'account'=>$order_data["account"],'license'=>$order_data["license"], 'trace'=> TRUE));
	new ATConfig($order_data["environment"], array('url'=>$order_data["service_url"], 'account'=>$order_data["account"],'license'=>$order_data["license"],'client'=>$order_data["client"], 'trace'=> TRUE));
	
	$client = new TaxServiceSoap($order_data["environment"]);
	$request = new PostTaxRequest();
	
    $request->setCompanyCode($order_data["CompanyCode"]);
    $request->setDocType($order_data["DocType"]);
    $request->setDocCode($order_data["DocCode"]);
    $request->setNewDocCode($order_data["DocCode"]);
    $request->setDocDate($order_data["DocDate"]);
    $request->setTotalAmount($order_data["TotalAmount"]); 
    $request->setTotalTax($order_data["TotalTax"]);
	//$request->setCommit(TRUE); 
	if($order_data["Commit"]==1) $request->setCommit(TRUE);
	else $request->setCommit(FALSE); 
	

	$returnMessage = "";
	$PostTaxReturnValue = array();
	
	// PostTax and Results
	try 
	{
		$result = $client->postTax($request);
		
		// Success - Display GetTaxResults to console
		if ($result->getResultCode() != SeverityLevel::$Success) {
			foreach ($result->getMessages() as $msg) {
				$returnMessage .= $msg->getName() . ": " . $msg->getSummary() . "\n";
				//echo $msg->getName() . ": " . $msg->getSummary() . "\n";
			}
			return "Error :".$returnMessage;
		}
		else
		{
			$PostTaxReturnValue["ResultCode"] = $result->getResultCode();
			$PostTaxReturnValue["TransactionId"] = $result->getTransactionId();			
			$PostTaxReturnValue["DocId"] = $result->DocId;
			
			return $PostTaxReturnValue;
		}
		// If NOT success - display error or warning messages to console
		// it is important to itterate through the entire message class   
	} 
	catch (SoapFault $exception) 
	{
		$msg = "Exception: ";
		if ($exception)
			$msg .= $exception->faultstring;

		// If you desire to retrieve SOAP IN / OUT XML
		//  - Follow directions below
		//  - if not, leave as is
		//    }   //UN-comment this line to return SOAP XML
		$returnMessage .= $msg . "\n";
		$returnMessage .= $client->__getLastRequest() . "\n";
		$returnMessage .= $client->__getLastResponse() . "\n";
		return $returnMessage;
		
		//echo $client->__getLastRequest() . "\n";			
		//echo $client->__getLastResponse() . "\n";		
	}   //Comment this line to return SOAP XML
}
?>