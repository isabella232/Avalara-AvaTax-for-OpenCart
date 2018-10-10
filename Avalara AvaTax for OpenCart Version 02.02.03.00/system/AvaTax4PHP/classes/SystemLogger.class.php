<?PHP
	/**
	 * Description	:	Class Implementation - This source file implements the System Logger Class
	 *
	 * 
	 * PHP version 5.5.9
	 *
	 * LICENSE: This source file is subject to version 3.01 of the PHP license
	 * that is available through the world-wide-web at the following URI:
	 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
	 * the PHP License and are unable to obtain it through the web, please
	 * send a note to license@php.net so we can mail you a copy immediately.
	 *
	 * @category   --NA--
	 * @package    --NA--
	 * @copyright  Avalara Technologies India [P] Ltd.
	 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
	 * @version    1.0
	 * @link       --TBD Later --
	 * @see        
	 * @since      File available since Release 1.0
	 * @deprecated --NA--
 */
?>
<?PHP
class SystemLogger
{
	var $when;
	var $func_name;
	var	$class_name;
	var $method_name;
	var $file_name;
	var $invoked_by;
	var $passed_param;
	var $result_string;
			
	function SystemLogger()
	{
		// constructor definition
		$this->when = '';
		$this->func_name = '';
		$this->class_name = '';
		$this->method_name = '';
		$this->file_name = '';
		$this->invoked_by = '';
		$this->passed_param = '';
		$this->result_string = '';				
	}
	
	function AddSystemLog($now, $fun_nm, $clas_nm, $method_nm, $fil_nm, $invoker, $param_list, $res_string)
	{
		// builds the object on call
		$this->when				=	$now;
		$this->func_name		=	$fun_nm;
		$this->class_name		=	$clas_nm;
		$this->method_name		=	$method_nm;
		$this->file_name		=	$fil_nm;
		$this->invoked_by		=	$invoker;
		$this->passed_param		=	$param_list;
		$this->result_string	=	$res_string;			
	}
	
	
	function ShowSystemLog()
	{
		// echos the currently captured system log
		echo "AT : " . $this->when;
		echo "<BR>";
		echo "Function() : " . $this->func_name;
		echo "<BR>";
		echo "Class : " . $this->class_name;
		echo "<BR>";
		echo "Class::Method() : " . $this->method_name;
		echo "<BR>";
		echo "File : " . $this->file_name;
		echo "<BR>";
		echo "Invoker : " . $this->invoked_by;
		echo "<BR>";
		echo "Param Passed : " . $this->passed_param;
		echo "<BR>";
		echo "Ret Val : " . $this->result_string;			
	}

	function metric($type,$linecount,$docno,$connectortime,$latency)
	{
		$logging_folder		=	$this->createLogPath();
		$log_file_name		=	$logging_folder . "/Ava-Connector-Metric-Log-" . date('d-m-Y') . ".txt";
		$log_file 			= 	fopen($log_file_name, "a") or die("Unable to open $log_file_name ");
		$today =  date("Y-m-d h:i:s a");
		fprintf($log_file, "%s%s",$today," - ");
		fprintf($log_file, "%s", "INFO --> CONNECTOR METRICS ");
		fprintf($log_file, "%s%s", " Type - ", $type);
		fprintf($log_file, "%s%s", "    Line Count - ", $linecount);
		fprintf($log_file, "%s%s", "   DOCNO - ", $docno);
		fprintf($log_file, "%s%s\r\n", "   Connector Latency - ", $latency);
		fprintf($log_file, "%s%s",$today," - ");
		fprintf($log_file, "%s", "INFO --> CONNECTOR METRICS ");
		fprintf($log_file, "%s%s", " Type - ", $type);
		fprintf($log_file, "%s%s", "    Line Count - ", $linecount);
		fprintf($log_file, "%s%s", "   DOCNO - ", $docno);
		fprintf($log_file, "%s%s\r\n", "   Connector - ", $connectortime);
		
		//fprintf($log_file, "%s%s\r\n", "------------", "------------");

		fclose($log_file);
	}
	
	//This function will store logs in AWS server. This covers Instrumentation story.
	function serviceLog($performance_metrics)
	{		
		array_push($performance_metrics[0], "Source");
		
		$resp = array();
		foreach ($performance_metrics as $entry) {
			$row = array();
			foreach ($entry as $key => $value) {
				array_push($row, $value);
			}
			if(!in_array("CallerTimeStamp",$row))
			{
				array_push($row, phpversion());
			}
			array_push($resp, implode(',', $row));
		}
		$csv_data = implode("\r\n", $resp);
		
		/*if(in_array("https://development.avalara.net",$performance_metrics[1]))
		{
			$url = 'https://qa.cphforavatax.com/Ava_Post_C_Log';
		}
		else
		{
			$url = 'https://qa.cphforavatax.com/Ava_Post_C_Log';
		}

		$ch = curl_init($url);
		$options = array(
				CURLOPT_RETURNTRANSFER => true,         // return web page
				CURLOPT_HEADER         => false,        // don't return headers
				CURLOPT_FOLLOWLOCATION => false,         // follow redirects
			   // CURLOPT_ENCODING       => "utf-8",           // handle all encodings
				CURLOPT_AUTOREFERER    => true,         // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
				CURLOPT_TIMEOUT        => 20,          // timeout on response
				CURLOPT_POST            => 1,            // i am sending post data
				CURLOPT_POSTFIELDS     => $csv_data,    // this are my post vars
				CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
				CURLOPT_SSL_VERIFYPEER => false,        //
				CURLOPT_VERBOSE        => 1
		);

		curl_setopt_array($ch,$options);
		$data = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);*/
		//echo "<p>CURL Response</p>";
		//print_r($data);
	}

	function arraytocsv($array) {
		$csv = array();
		foreach ($array as $item) {
			if (is_array($item)) {
				$csv[] = $this->arraytocsv($item);
			} else {
				$csv[] = $item;
			}
		}
		return implode(',', $csv);
	}

	function createLogPath()
	{
		// If does not exist, create a folder named ava-sys-logs - under the given path
	//	$curr_dir	=	getcwd();
		
		// Check if a folder named ava-logs exist below CWD
		
		//$dir_name	=	'ava-logs';
		
		$dir_name	=	dirname ( __FILE__ ).'/ava-logs';		// For now this is hard-coded
		if (is_dir($dir_name)) 	// Checking if the sub-folder ava-logs exists under current folder
		{

			if ($dh = opendir($dir_name)) 
			{
				while (($file = readdir($dh)) !== false) 
				{
					;	// the desired folder exists, do nothing
				}
				closedir($dh);
			}
			else	// sub-folder ava-logs does NOT exist under current folder
			{
				// desired folder - needs to be created
				mkdir($dir_name, 0777, true);
			}			
			
		}
		else
		{
			mkdir($dir_name, 0777, true);
		}
		
		$log_folder = $dir_name;
		
		return $log_folder;
		
	}
	/* Writes the current system log to designated log file */
	function WriteSystemLogToFile()
	{
		$logging_folder		=	$this->createLogPath();			
		$log_file_name		=	$logging_folder . "/Ava-Connect-Log-" . date('d-m-Y') . ".txt";
		$log_file 			= 	fopen($log_file_name, "a") or die("Unable to open $log_file_name ");

		fprintf($log_file, "%s%s\r\n", "AT : ", $this->when);
		fprintf($log_file, "%s%s\r\n", "Function() : ", $this->func_name);
		fprintf($log_file, "%s%s\r\n", "Class : ", $this->class_name);
		fprintf($log_file, "%s%s\r\n", "Class::Method() : ", $this->method_name);
		fprintf($log_file, "%s%s\r\n", "File : ", $this->file_name);
		fprintf($log_file, "%s%s\r\n", "Invoker : ", $this->invoked_by);
		fprintf($log_file, "%s%s\r\n", "Param Passed : ", $this->passed_param);
		fprintf($log_file, "%s%s\r\n", "Ret Val : ", $this->result_string);
		fprintf($log_file, "%s%s\r\n", "------------", "------------");
		
		fclose($log_file);				
	
	}
	
	function WriteSystemLogToDB()
	{
		/////// DB Connection /////////////
		$dbhost_name 	= "localhost"; 					// host name 
		$database 		= "ava-connector-logger";      	// database name
		$username 		= "root";            			
		$password 		= "";            				
		
		$con = mysqli_connect($dbhost_name, $username, $password, $database);

		// Check connection
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		else
		{
			;	// connection successful
		}
		
		mysqli_query($con,"SET time_zone = '+05:30' ");
		mysqli_query($con, "SET names utf8");	
		
		$file_name	=	addslashes($this->file_name);
		
		// Defining the query
		$query	=	" INSERT INTO 
					`ava-connect-logger`
					(
						`when_timestamp`,
						`func_name`, 
						`class_name`, 
						`method_name`, 
						`file_name`, 
						`invoked_by`, 
						`passed_param`, 
						`result_string`
					)
				VALUES 
					(
						'$this->when',
						'$this->func_name', 
						'$this->class_name', 
						'$this->method_name',
						'$file_name', 
						'$this->invoked_by', 
						'$this->passed_param', 
						'$this->result_string'
					) ";
			
		mysqli_query($con, $query);
		
		// fetch the record no of last inserted record			
		$rec_no	=	mysqli_insert_id($con);
		
		// close the database			
		mysqli_close($con);				
	}
}
?>