<?php
if(isset($_GET["from"]) && $_GET["from"]=="AvaTaxConnectionTest")
{
	/****************************************************************************************************
	*   Last Updated On	:	10/23/2015			                            							*
	*   Description     :   Checking SOAP service is enabled or customer's PHP environment.				*
	* 	If SOAP service is installed, returning an error message to install SOAP service  				*
	******************************************************************************************************/

	if (!extension_loaded('SOAP')) {
		$errorMsg = "";
		$errorMsg.= "<div style='text-align:center;padding-top:10px;'>Please enable SOAP service. It is mandatory to use AvaTax.</div>";
		$errorMsg.= "<p style='text-align:center;padding-top:10px;'><input type='button'  onClick='closeTestConnection()' value='OK' ></p>";
		echo $errorMsg;
		exit;
	}

	require_once('AvaTax.php');
	
	$successMsg = "";
	
	$account = $_GET["acc"];
	$license = $_GET["license"];
	$environment = $_GET["environment"];
	$client = $_GET["client"];
	$connectorVersion = $_GET["client"];

	if($environment == "Development")
		$serviceURL = "https://development.avalara.net";
	else
		$serviceURL = "https://avatax.avalara.net";


	/****************************************************************************************************
	*   Last Updated On	:	07/28/2015			                            							*
	*   Description     :   Enter AvaTax admin console company code here.
	* 	Removed URL from query string. Now defining URL in test_connection.php page as per environment  	*
	******************************************************************************************************/

	new ATConfig($environment, array('url'=>$serviceURL, 'account'=>$account,'license'=>$license,'client'=>$client, 'trace'=> TRUE));

	//Added below code for Instrumentation
	require_once('classes/SystemLogger.class.php');
	// Creating the System Logger Object
	$application_log 	= 	new SystemLogger;

	$client = new TaxServiceSoap($environment);
	$phpVersionArray = explode("||",$connectorVersion);
	$phpVersion = trim(str_replace("OpenCart","",$phpVersionArray[0]));

	unset($test_connection_metrics);
	$test_connection_metrics[] = array("CallerTimeStamp","MessageString","CallerAcctNum","Operation","ServiceURL","LogType","LogLevel","ERPName","ERPVersion","ConnectorVersion");
	$debug_date = new DateTime();
	try
	{
		$result = $client->isAuthorized("");
		/*************************************************************************************************
		*   Last Updated On	:	07/07/2015			                            							*
		*   Description        :   Added Ok button to test connection window	  	*
		**************************************************************************************************/

		if($result->getResultCode() != SeverityLevel::$Success)	// call failed
		{
			/********************************************************************************************
			*   Last Updated On		:	12/09/2015			                            					*
			*   Description        	:   While creating free trial account, if account doesn't get activated instantly, we dont get return message properly in $result->Messages(). So changed below parameters to display data properly. 
			Renamed $result->Messages() to $result->getMessages(), $msg->Name() to $msg->getName() & $msg->Summary() to $msg->getSummary() 	*
			**********************************************************************************************/
			foreach($result->getMessages() as $msg)
			{
				$successMsg .= $msg->getName().": ".$msg->getSummary()."<br/>\n";
			}

			$debug_time = $debug_date->format('Y-m-d\TH:i:s').".".substr((string)microtime(), 2, 3)." ".$debug_date->format("P");
			$test_connection_metrics[] = array($debug_time,"\"Test Connection Fail Response - Account - ".$account." License Key ".$license." Detail - ".var_export(str_replace("'","",$result), true)."\"",$account,"Test Connection",$serviceURL,"Performance","Informational","OpenCart",$phpVersion,$connectorVersion);
			$returnServiceLog = $application_log->serviceLog($test_connection_metrics);
		} 
		else // successful calll
		{
			$dateTime = new DateTime();
			$dateTime = strtotime($result->getExpires());
			$dateTime = date ("Y-m-d", $dateTime);
			$type = gettype ($dateTime);
			$successMsg .= "Welcome to the Ava Tax Service.<br/>";
			$successMsg .= "Connection Test Status: <span style='color:green;'>".$result->getResultCode()."</span><br/>";
			$successMsg .= "Expiry Date : <span style='color:green;'>".$dateTime."</span><br/>";
			$successMsg .= "<p style='text-align:center;padding-top:10px;'><input type='button'  onClick='closeTestConnection()' value='OK' ></p>";

			$debug_time = $debug_date->format('Y-m-d\TH:i:s').".".substr((string)microtime(), 2, 3)." ".$debug_date->format("P");
			$test_connection_metrics[] = array($debug_time,"\"Test Connection Success Response - Account - ".$account." License Key ".$license." Detail - ".str_replace("'","",var_export($result, true))."\"",$account,"Test Connection",$serviceURL,"Performance","Informational","OpenCart",$phpVersion,$connectorVersion);
			$returnServiceLog = $application_log->serviceLog($test_connection_metrics);
		}
		echo "<div style='text-align:center;padding-top:10px;'>".$successMsg."</div>"; 
		//echo $successMsg; 
	}
	catch(SoapFault $exception)
	{
		$msg = "Reason: ";
		if($exception)
			$msg .= $exception->faultstring;

		$successMsg .= "Welcome to the Ava Tax Service.<br/>";
		$successMsg .= "Connection Test Status: <span style='color:red;'>Failed</span><br/>";
		$successMsg .= $msg."<br/>";
		$successMsg .= "<p style='text-align:center;padding-top:20px;'><input type='button'  onClick='closeTestConnection()' value='OK' ></p>";
		echo "<div style='text-align:center;padding-top:10px;'>".$successMsg."</div>"; 
		
		$debug_time = $debug_date->format('Y-m-d\TH:i:s').".".substr((string)microtime(), 2, 3)." ".$debug_date->format("P");
		$test_connection_metrics[] = array($debug_time,"\"Test Connection Fail Response in Catch - Account ".$account." License Key ".$license." Detail - ".str_replace("'","",var_export($exception, true))."\"",$account,"Test Connection",$serviceURL,"Performance","Informational","OpenCart",$phpVersion,$connectorVersion);
		$returnServiceLog = $application_log->serviceLog($test_connection_metrics);
	}

	//if($address_data["log"] == 1)
	//{
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
	//}	
}
?>