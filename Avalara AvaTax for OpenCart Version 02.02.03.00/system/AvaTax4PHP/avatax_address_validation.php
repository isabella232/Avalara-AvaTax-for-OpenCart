<?php
//echo file_exists('../../config.php');

	if(isset($_REQUEST["service_url"]) && $_REQUEST["service_url"]!="")
	{		
		$environment="Production";
		if(preg_match("/development/", $_REQUEST["service_url"])) $environment = 'Development';;
		require_once('address_validation.php');
               
                
		$addressData=$_REQUEST;
		$addressData['environment']=$environment;
		$result=AddressValidation($addressData);
		echo $result;
		
	}
        
        
?>