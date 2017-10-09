<?php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname2 = "db2";// new DB
	
	// Create connection
	$conn2 = new mysqli($servername, $username, $password, $dbname2);

	// Check connection
	if ($conn2->connect_error) {
		die("Connection failed: " . $conn2->connect_error);
	}

	//list all tables want to insert
	$tables=array(	
		"agenda"=>array(
			"name"=>array(
				"tmbh_agenda"=>"agenda",
			),
		)
		,
		"kbk"=>array(
			"name"=>array(
				"krkm_r_kbk"=>"ref_kbk"
			),
		)
	);
//$counter = 0;
	foreach($tables as $tables_key=>$tables_val){
		//$counter = $counter+1;
		$table_name_new=key($tables_val["name"]);
		$report_query="";
		echo "Eksekusi table ".$table_name_new."\n \n";

		if(file_exists("report_".$table_name_new.".txt")==false)
		{

			if(file_exists("report_".$table_name_new."_progress.txt")){
				unlink ("report_".$table_name_new."_progress.txt");
			}

			if (file_exists("report_".$table_name_new."_progress.txt")==false)
			{
				//echo"sukses create progress"."\n";
				$file = "report_".$table_name_new."_progress.txt";
				$f = fopen($file,"a");

				if(file_exists($table_name_new.".txt")==true){
					//echo "nemu file query txt"."\n";
					
					$myfile = fopen($table_name_new.".txt", "r") or die("Unable to open file!");
					if(filesize($table_name_new.".txt")!=0){
						
						$query = fread($myfile,filesize($table_name_new.".txt"));
						

						if ($conn2->multi_query($query)) {
							do {
						        if ($result = $conn2->store_result()) {
						            while ($row = $result->fetch_row()) {
						                printf("%s\n", $row[0]);
						            }
						            $result->free();
						        }
						    } while ($conn2->next_result());
							$report_query = "Success on table ".$table_name_new."\r\n";
							echo $report_query;
							fwrite($f, $report_query);						    
						}
						elseif (!$conn2->multi_query($query)) {
							$report_query = "Multi query failed: (" . $mysqli->errno . ") " . $mysqli->error."\r\n";
							echo $report_query;
							fwrite($f, $report_query);
						}
					}
					fclose($myfile);
				}
				fclose($f);
				rename($file, "report_".$table_name_new.".txt");
			}
		}			
	}
?>