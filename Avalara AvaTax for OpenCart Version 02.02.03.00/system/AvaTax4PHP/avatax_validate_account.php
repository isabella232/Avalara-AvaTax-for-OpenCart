<?php
//This validates the customer Account ID & their username and passwords

if($_REQUEST['environment'] == "Development")
{
	$url = 'https://sandbox.onboarding.api.avalara.com/v1/Accounts/'.$_REQUEST['accountid'];
	$username = "TEST/".$_REQUEST['username'];
}
else
{
	$url = 'https://onboarding.api.avalara.com/v1/Accounts/'.$_REQUEST['accountid'];
	$username = $_REQUEST['username'];
}

$password = $_REQUEST['password'];
$authentication = base64_encode($username.":".$password);

$ch = curl_init($url);
$options = array(
		CURLOPT_RETURNTRANSFER => true,         // return web page
		CURLOPT_HEADER         => false,        // don't return headers
		CURLOPT_FOLLOWLOCATION => false,         // follow redirects
	   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
		CURLOPT_AUTOREFERER    => true,         // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
		CURLOPT_TIMEOUT        => 20,          // timeout on response
		CURLOPT_POST            => 0,            // i am sending post data
		CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
		CURLOPT_SSL_VERIFYPEER => false,        //
		CURLOPT_VERBOSE        => 1,
		CURLOPT_HTTPHEADER     => array(
			"Authorization: Basic $authentication",
			"Content-Type: application/json"
		)
);

curl_setopt_array($ch,$options);
$data = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
//echo $curl_errno;
//echo $curl_error;
curl_close($ch);
//echo "<p>CURL Response</p>";
print_r($data);
?>