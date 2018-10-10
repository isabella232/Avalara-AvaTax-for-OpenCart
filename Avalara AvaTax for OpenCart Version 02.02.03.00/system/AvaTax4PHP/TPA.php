<?php
session_start();

if(!isset($_SESSION['token']) || ($_SESSION['token']==""))
	die('Access denied'); 

require_once("../../config.php");	//Fetch connection details from config file

$con=mysqli_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
// Check connection
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	die();
}

if($_REQUEST['environment']=="Development")
	$serviceurl = "https://development.avalara.net";
else
	$serviceurl = "https://avatax.avalara.net";

//Started creating XML file
//header("Content-type: application/xml");
$xml= "<?xml version=\"1.0\"?>";
$xml.= "<TPA>
  <AccountCredentials>
		<AccountNumber>".$_REQUEST['account']."</AccountNumber>
		<LicenseKey>".$_REQUEST['license']."</LicenseKey>
		<UserName>".$_REQUEST['username']."</UserName>
		<Password>".$_REQUEST['password']."</Password>
		<WebService>".$serviceurl."</WebService>
		<CompanyCode>".$_REQUEST['company']."</CompanyCode>
		<ERPName>OpenCart</ERPName>
	</AccountCredentials>";

//Fetch Company details
$companyQuery = "SELECT * FROM ".DB_PREFIX."setting WHERE `key` IN('config_name','config_address','config_address_line2','config_city','config_zone_id','config_country_id','config_postal_code','config_email','config_telephone','config_fax','config_owner')";

//echo "<br>companyQuery: ".$companyQuery;
$companyRes = mysqli_query($con,$companyQuery);
while($companyData = mysqli_fetch_array($companyRes,MYSQLI_ASSOC))
{
	//echo "<br>Key: ".$companyData['key'];
	if($companyData['key'] == "config_name")			$companyName = $companyData['value'];
	if($companyData['key'] == "config_address")			$companyAddress = $companyData['value'];
	if($companyData['key'] == "config_address_line2")	$companyAddressLine1 = $companyData['value'];	
	if($companyData['key'] == "config_city")			$companyCity=$companyData['value'];	
	if($companyData['key'] == "config_zone_id")			$companyState=$companyData['value'];
	if($companyData['key'] == "config_country_id")		$companyCountry=$companyData['value'];
	if($companyData['key'] == "config_postal_code")		$companyZip=$companyData['value'];
	if($companyData['key'] == "config_email")			$companyEmail=$companyData['value'];
	if($companyData['key'] == "config_telephone")		$companyPhone=$companyData['value'];
	if($companyData['key'] == "config_fax")				$companyFax = $companyData['value'];
	if($companyData['key'] == "config_owner")			$ownerName = $companyData['value'];
}

//Fetch Country Code
$countryQuery="SELECT * FROM ".DB_PREFIX."country WHERE country_id=".$companyCountry;
$countryRes=mysqli_query($con,$countryQuery);
$countryData=mysqli_fetch_array($countryRes,MYSQLI_ASSOC);
$countryCode = $countryData['iso_code_3'];

//Fetch State Code
$stateQuery="SELECT * FROM ".DB_PREFIX."zone WHERE zone_id=".$companyState;
$stateRes=mysqli_query($con,$stateQuery);
$stateData=mysqli_fetch_array($stateRes,MYSQLI_ASSOC);
$stateCode = $stateData['code'];

$xml.="
<Company>
	<CompanyName>".$companyName."</CompanyName>
	<TIN></TIN>
	<BIN></BIN>
	<Address>
		<Line1>".$companyAddress."</Line1>
		<Line2>".$companyAddressLine1."</Line2>
		<Line3></Line3>
		<City>".$companyCity."</City>
		<StateProvince>".$stateCode."</StateProvince>
		<Country>".$countryCode."</Country>
		<ZipPostalCode>".$companyZip."</ZipPostalCode>
	</Address>
	<PrimaryContact>
		<FirstName>".$ownerName."</FirstName>
		<LastName></LastName>
		<Email>".$companyEmail."</Email>
		<PhoneNumber>".$companyPhone."</PhoneNumber>
		<Title></Title>
		<MobileNumber>".$companyPhone."</MobileNumber>
		<Fax>".$companyFax."</Fax>
	</PrimaryContact>
	</Company>
	<Nexus>
		<CompanyLocations>
		  <CompanyLocation>
			<Country>".$countryCode."</Country>
			<States>".$stateCode."</States>
		  </CompanyLocation>
		</CompanyLocations>
		<WareHouseLocations>
			<WareHouseLocation>
				<Country>".$countryCode."</Country>
				<States>".$stateCode."</States>
			</WareHouseLocation>
		</WareHouseLocations>
		<PreviousCustomerLocations>";

//Fetch last 1000 orders or 1 year data for unique addresses where products have been delivered.
$today=date("Y-m-d 23:59:00");
$oneYearBack=date("Y-m-d",strtotime("$today -1 year"));
$addressQuery = "SELECT xyz.iso_code_3,xyz.code,xyz.shipping_country_id,xyz.shipping_zone_id,COUNT(*) AS 'Count'
	FROM (SELECT ".DB_PREFIX."country.iso_code_3,".DB_PREFIX."zone.code,".DB_PREFIX."order.shipping_country_id,".DB_PREFIX."order.shipping_zone_id
	FROM ".DB_PREFIX."order JOIN ".DB_PREFIX."country ON (".DB_PREFIX."order.shipping_country_id = ".DB_PREFIX."country.country_id) JOIN ".DB_PREFIX."zone ON (".DB_PREFIX."order.shipping_zone_id = ".DB_PREFIX."zone.zone_id)
	WHERE date_added BETWEEN '".$oneYearBack."' and '".$today."'
	ORDER BY order_id DESC
	LIMIT 0,1000) xyz
	GROUP BY xyz.iso_code_3,xyz.code";

$addressRes=mysqli_query($con,$addressQuery);
while($addressData=mysqli_fetch_array($addressRes,MYSQLI_ASSOC))
{
	//To check tax has been calculated for how may invoices for selected state and country
	$invoicesChargedQry = "SELECT COUNT(*) AS 'Count' FROM ".DB_PREFIX."order JOIN ".DB_PREFIX."order_total ON(".DB_PREFIX."order.order_id = ".DB_PREFIX."order_total.order_id AND `code`='tax' AND `value`<>0) 
	WHERE shipping_country_id = ".$addressData['shipping_country_id']." and shipping_zone_id = ".$addressData['shipping_zone_id']." and date_added BETWEEN '".$oneYearBack."' and '".$today."'
	LIMIT 0,1000";
	
	//echo "<br>invoicesChargedQry: ".$invoicesChargedQry;
	$invoicesChargedRes = mysqli_query($con,$invoicesChargedQry);
	$invoicesChargedData = mysqli_fetch_array($invoicesChargedRes,MYSQLI_ASSOC);
	$invoicesChargedCount = $invoicesChargedData['Count'];

	$totalInvoicesQry = "SELECT count(*) as 'Count' 
	FROM " .DB_PREFIX."order 
	WHERE shipping_country_id = ".$addressData['shipping_country_id']." and shipping_zone_id = ".$addressData['shipping_zone_id']." and date_added BETWEEN '".$oneYearBack."' and '".$today."'
	LIMIT 0,1000";

	$totalInvoicesRes = mysqli_query($con,$totalInvoicesQry);
	$totalInvoicesData = mysqli_fetch_array($totalInvoicesRes,MYSQLI_ASSOC);
	$totalInvoicesCount = $totalInvoicesData['Count'];

	//To calculate avergae of tax calculated invoices
	$avgCount = ($invoicesChargedCount * 100) / $totalInvoicesCount;
    $xml.="<PreviousCustomerLocation>
			<Country>".trim($addressData['iso_code_3'])."</Country>
			<States>".trim($addressData['code'])."</States>
			<InvoicesCharged>".round($avgCount,0)."</InvoicesCharged>
			<TotalInvoices>".$totalInvoicesCount."</TotalInvoices>
		</PreviousCustomerLocation>";
}
$xml.="</PreviousCustomerLocations></Nexus>";

//Check Tax exempt user count
$custQry="SELECT COUNT(*) as 'Count' FROM ".DB_PREFIX."customer";
$custRes=mysqli_query($con,$custQry);
$custData=mysqli_fetch_array($custRes,MYSQLI_ASSOC);
$totalCustCnt=$custData['Count'];

//Find total Products count and their tax classes 
$productQry="SELECT model,title FROM ".DB_PREFIX."product join ".DB_PREFIX."tax_class";
$productRes=mysqli_query($con,$productQry);
$totalProductCnt=mysqli_num_rows($productRes);

$xml.="<AvaERPSettings>
	<TaxSchedule>
		<IsTaxScheduleMapped>true</IsTaxScheduleMapped>
		<TaxScheduleID>AVATAX</TaxScheduleID>
</TaxSchedule>
<MapItemCodes>
	<MappedItemsCount>".$totalProductCnt."</MappedItemsCount>
	<MappedItems>";
 
$nonTaxableProducts = 0;
$nonTaxablexml = "";
while($productData=mysqli_fetch_array($productRes,MYSQLI_ASSOC))
{
	if($productData['title'] == "Non Taxable")
	{
		$productCode = "NT";
		$nonTaxableProducts++;

		$nonTaxablexml.="
			<EntityNameCode>
				<Name>".$productData['model']."</Name>
				<Code>".$productCode."</Code>
		   </EntityNameCode>";
	}
	else
	{
		$productCode = $productData['title'];
	}

	$productCode = 
	$xml.="
			<EntityNameCode>
				<Name>".$productData['model']."</Name>
				<Code>".$productCode."</Code>
			</EntityNameCode>";
}

$xml.="</MappedItems>
		<NonTaxableItems>
			<Total>".$totalProductCnt."</Total>
			<NonTaxable>".$nonTaxableProducts."</NonTaxable>
			<Items>
				".$nonTaxablexml."
			</Items>
		</NonTaxableItems>
		</MapItemCodes>
		<AddressValidation>
			<IsAddressValidationEnabled>true</IsAddressValidationEnabled>
			<CountryNamesMapped>true</CountryNamesMapped>
			  <MappedCountries>
				<MappedCountry>
				  <ERPCountry>Canada</ERPCountry>
				  <ERPCountryCode>CAN</ERPCountryCode>
				  <AvaCountry>CAN</AvaCountry>
				</MappedCountry>
				<MappedCountry>
				  <ERPCountry>USA</ERPCountry>
				  <ERPCountryCode>USA</ERPCountryCode>
				  <AvaCountry>USA</AvaCountry>
				</MappedCountry>
			  </MappedCountries>
		</AddressValidation>
		<Customers>";

//Check Tax exempt user count
$custNameQry="SELECT DISTINCT firstname,lastname FROM ".DB_PREFIX."order WHERE avatax_paytax_error_message = 'Success' and date_added BETWEEN '".$oneYearBack."' and '".$today."' ORDER BY order_id DESC LIMIT 0,1000";

$custNameRes=mysqli_query($con,$custNameQry);
while($custNameData=mysqli_fetch_array($custNameRes,MYSQLI_ASSOC))
{
	$xml.="<AvaCustomer>
			<Name>".$custNameData['firstname']." ".$custNameData['lastname']."</Name>
			<TaxSchedule>AVATAX</TaxSchedule>
			<ExemptCode />
		</AvaCustomer>";
}

$xml.="</Customers>
  </AvaERPSettings>
  <HelpLink>
    <Links>
      <Link>
        <LinkType>0</LinkType>
        <URL>https://help.avalara.com/004_AvaTax_Integrations/OpenCart/020_Install_Configure_and_Test_AvaTax_for_OpenCart</URL>
      </Link>
      <Link>
        <LinkType>1</LinkType> 
		<URL>https://help.avalara.com/004_AvaTax_Integrations/OpenCart/Learn_How_to_Use_Open_Cart</URL>
      </Link>
    </Links>
  </HelpLink>
</TPA>";

//echo $xml;
//exit;

//Pass XML data to API through file method

$url = 'https://avataxprofileassistant.com/TaxProfileAssistant/Post';

//$url = 'https://avataxprofileassistant.connectorsqa.avatax.com/Post';

$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_MUTE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);

//print_r($output);

if(curl_errno($ch))
{
	//echo "In error";
    print curl_error($ch);
	curl_close($ch);
}
elseif(!strpos($output,"avataxprofileassistant"))
{
	print_r($output);
}
else
{
	curl_close($ch);
	echo "<script>window.location='$output';</script>";
	//echo "In success";
}
?>