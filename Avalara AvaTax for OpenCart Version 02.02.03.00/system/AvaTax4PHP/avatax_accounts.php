<?php

function AccountValidation()
{
	require_once('AvaTax.php');
	
	$account = $_GET["acc"];
	$license = $_GET["license"];
	$environment = $_GET["environment"];
	$client = $_GET["client"];
	$log = $_GET["log"];

	if($environment == "Development")
		$serviceURL = "https://development.avalara.net";
	else
		$serviceURL = "https://avatax.avalara.net";

	new ATConfig($environment, array('url'=>$serviceURL, 'account'=>$account,'license'=>$license,'client'=>$client, 'trace'=> TRUE));

	$client = new AccountServiceSoap($environment);
	
	$return_message = "";
	
	try
	{
		$result = $client->CompanyFetch("");
        $response= array();

		if($result->getResultCode() != SeverityLevel::$Success)
		{
			$return_message .= "Error - AvaTax Account Service Message\n";
			
			foreach($result->getMessages() as $msg)
			{
				//$return_message .= $msg->getName().": ".$msg->getSummary()."<br/>";
				$return_message .= $msg->getSummary();
			}	
			$response["msg"]=$return_message;
			$response["address"]="";
		}
		else if($result->getResultCode() == SeverityLevel::$Success && $result->getValidCompanies() != "")
		{
			$arr=array();
			$validCompanies=array();
			$validCompanies=$result->getValidCompanies();
			foreach ($validCompanies as $obj) {
				$arr[$obj->CompanyCode] = $obj->CompanyName;
			}
			$return_message .= json_encode($arr);
		}
		echo($return_message);
		
		if($log == 1)
		{
			require_once('classes/SystemLogger.class.php');
			$timeStamp 			= 	new DateTime();						// Create Time Stamp
			$params				=   '[Input: ' . ']';		// Create Param List
			$u_name				=	'';							// Eventually will come from $_SESSION[] object

			// Creating the System Logger Object
			$application_log 	= 	new SystemLogger;

			$application_log->AddSystemLog($timeStamp->format('Y-m-d H:i:s'), __FUNCTION__, __CLASS__, __METHOD__, __FILE__, $u_name, $params, $client->__getLastRequest());		// Create System Log
			$application_log->WriteSystemLogToFile();			// Log info goes to log file

			$application_log->AddSystemLog($timeStamp->format('Y-m-d H:i:s'), __FUNCTION__, __CLASS__, __METHOD__, __FILE__, $u_name, $params, $client->__getLastResponse());		// Create System Log
			$application_log->WriteSystemLogToFile();			// Log info goes to log file
		}

		//return json_encode($response);
	}
	catch(SoapFault $exception)
	{
		$return_message .= "Exception: ";
		if($exception)
			$return_message .= $exception->faultstring;
			$return_message .= $client->__getLastRequest() . "<br/>";
			$return_message .= $client->__getLastResponse() . "<br/>";
			
		//return $return_message;
		print_r($return_message);
	} 
}  

if(isset($_GET["from"]) && $_GET["from"]=="AvaTaxFetchCompanies")
{
	AccountValidation(); 
}
?>