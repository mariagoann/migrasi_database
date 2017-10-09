<!-- migrasi_database.php
	 digunakan untuk mapping table
	 digunakan untuk migrasi dari old database , dengan melakukan generate file sql 
	 yg akan dieksekusi.

	 digunakan untuk migrasi dari database lama ke database baru dengan struktur yang telah berbeda / RELASI
	 -->
<?php
	//konek database old_db dan new_db
	$servername = "localhost"; // write host
	$username = "root"; //write username
	$password = ""; //write password
	$dbname1 = "db1"; // select db want to migrate
	
	// Create connection
	$conn1 = new mysqli($servername, $username, $password, $dbname1);

	// Check connection
	if ($conn1->connect_error) {
		die("Connection failed: " . $conn1->connect_error);
	}

	//list of all tables want to migrate
	//example taking 2 tables
	$tables=array(
		"asal_sekolah" => array(
				"name" =>array(
					"mref_r_asal_sekolah" => "mref_r_asal_sekolah"
				),
				"fields"=>array(
					"nama"=>"nama",
					"desc"=>"desc"
				),
				"pk" =>"asal_sekolah_id",
				"precondition"=>array(),
				"fk" =>array()
		,
		"nilai_uas"=>array(
			"name"=>array(
				"nlai_nilai_uas"=>"nilai_uas"
			),
			"fields"=>array(
				"id_kur"=>"ID_KUR",
				"kode_mk"=>"KODE_MK",
				"ta"=>"TA",
				"sem_ta"=>"SEM_TA",
				"nim"=>"NIM",
				"komponen"=>"KOMPONEN",
				"dosen_approval"=>"DOSEN_APPROVAL",
				"komponen_ke"=>"KOMPONEN_KE",
				"nilai"=>"NILAI",
				"dim_id"=>"FK_1",	
				"kurikulum_syllabus_id"=>"FK_2"
			),
			"pk"=>"nilai_uas_id",
			"precondition"=>array(),
			"fk"=>array(
				"FK_1"=>array("dim"=>array(array("nim"=>"NIM"))),
				"FK_2"=>array("kurikulum_syllabus"=>array(array("id_kur"=>"ID_KUR")
												,array("kode_mk"=>"KODE_MK")
												,array("ta"=>"TA")))
			)
		)
		,
	);
	

	foreach($tables as $tables_key=>$tables_val){
		$table_name_new=key($tables_val["name"]);
		$table_name_old=$tables_val["name"][$table_name_new];

		echo "Eksekusi table ".$table_name_new."\n \n";	
		$fields_new="";
		
		$table_fields=$tables_val["fields"];
		foreach($table_fields as $table_fields_key=>$table_fields_val)
		{	
			$fields_new.= $table_fields_key.",";
		}	
		$fields_new=rtrim($fields_new,",");
		
		//cek preCondition for selecting data
		$table_precondtion = $tables_val["precondition"];
		if($table_precondtion != NULL)
		{
			foreach ($table_precondtion as $key => $value) {
				$retreave_old = "select * from ".$table_name_old. " where ".$key. " = "."'".$value."'";
			}
		}
		else
		{
			$retreave_old = "select * from ".$table_name_old;
		}
		$result_old = $conn1->query($retreave_old);


		if(file_exists($table_name_new."_progress.txt")){
			unlink ($table_name_new."_progress.txt");
		}

		if(file_exists($table_name_new.".txt")==false){
			$file =$table_name_new.'_progress.txt';
			// Open the file to get existing content
			$f = fopen($file,"a");
			while ($row = mysqli_fetch_assoc($result_old))
			{
					$values_old="";
					$condition1 ="";
					$condition2 = "";
					$insert_new="";
					
					foreach($table_fields as $table_fields_key=>$table_fields_val)
					{
						if (strpos ($table_fields_val, "FK_") !== false)
						{
							foreach ($tables_val["fk"][$table_fields_val] as $tables_val_key => $tables_val_val)
							{
								$condition1 = "";
								for ($i=0; $i<count($tables_val_val); $i++)
								{
									foreach ($tables_val_val[$i] as $key => $value) {
											$condition1 .= $key." = ". (is_numeric($row[$value])?$row[$value]
																		:"'".addslashes($row[$value])."'")." and ";
									}
								}
							}
							$condition1 = rtrim ($condition1, "and ");						
							$nama_tabel_relasi = key($tables[$tables_val_key]["name"]); //new_kur
							$nama_pk_relasi = $tables[$tables_val_key]["pk"]; //kur_id	
							$values_old .= " (select ".$nama_pk_relasi." from ".$nama_tabel_relasi." where ".$condition1." ), ";
						}
						else
						{
							$values_old .= (is_numeric($row[$table_fields_val])?$row[$table_fields_val]
												:"'".addslashes($row[$table_fields_val])."'").",";
						}
					}
						$values_old=rtrim ($values_old, ", ");
						
					//insert statement
					$insert_new = "insert into ".$table_name_new." (".$fields_new.")    values (".$values_old.");\r\n";
	
					fwrite($f, $insert_new);
			}
			fclose($f);	
			rename($file,$table_name_new.".txt"); 
			
		}
	}
?>