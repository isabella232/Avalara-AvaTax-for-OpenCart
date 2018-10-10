<?php
$con=mysqli_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
// Check connection
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	die();
}

//Add three new fields to Open Cart "customer" table for exemption and entity use codes
$checkColumns = "SHOW COLUMNS FROM `" . DB_PREFIX . "customer` LIKE 'avatax_exemption_status'";
$columnRes = mysqli_query($con,$checkColumns);
if(mysqli_num_rows($columnRes) > 0)
{}
else
{
	$alterQuery = "ALTER TABLE `" . DB_PREFIX . "customer` ADD `avatax_exemption_status` bool,
	ADD `avatax_exemption_number` VARCHAR( 50 ),
	ADD `avatax_entity_usecode` VARCHAR( 50 )";
	
	$columnRes = mysqli_query($con,$alterQuery);
}
mysqli_close($con);