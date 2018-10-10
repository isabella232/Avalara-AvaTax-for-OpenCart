<?php
function GetTaxHistory($order_data)
{
	require_once('AvaTax.php');

	new ATConfig($order_data["environment"], array('url'=>$order_data["service_url"], 'account'=>$order_data["account"],'license'=>$order_data["license"],'client'=>$order_data["client"], 'trace'=> TRUE));
	
	$client = new TaxServiceSoap($order_data["environment"]);	
	$request = new GetTaxHistoryRequest();	
	
    $request->setCompanyCode($order_data["CompanyCode"]);
    $request->setDocType($order_data["DocType"]);
    $request->setDocCode($order_data["DocCode"]);    

	$returnMessage = "";
	$GetTaxHistoryReturnValue = array();
	
	// Get Tax History and Results
	try 
	{	
		$result = $client->getTaxHistory($request);
		
		// Success - Display GetTaxResults to console
		if ($result->getResultCode() != SeverityLevel::$Success) {
			foreach ($result->getMessages() as $msg) {
				$returnMessage .= $msg->getName() . ": " . $msg->getSummary() . "\n";
			}
			return "Error :".$returnMessage;
		}
		else
		{	
			$GetTaxHistoryReturnValue["DocDate"] = $result->getGetTaxResult()->getDocDate();
			$GetTaxHistoryReturnValue["TaxDate"] = $result->getGetTaxResult()->getTaxDate();
			$GetTaxHistoryReturnValue["DocCode"] = $result->getGetTaxResult()->getDocCode();
			return $GetTaxHistoryReturnValue;
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