<?php
// Unzip cbrfeed.zip, import all csv files to database
$zip = zip_open(dirname(__FILE__) . "/cbrfeed.zip");
$tmpdir = "/tmp/cbrfeed/";
mkdir($tmpdir);
/*if (is_resource($zip)) {
	while ($zip_entry = zip_read($zip)) {
		$fp = fopen($tmpdir . zip_entry_name($zip_entry) , "w");
		if (zip_entry_open($zip, $zip_entry, "r")) {
			echo "Extracting " . zip_entry_name($zip_entry) . "\n";
			$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			fwrite($fp, "$buf");
			zip_entry_close($zip_entry);
			fclose($fp);
		}
	}
	zip_close($zip);
}*/
$conn = pg_connect("dbname=transitdata user=postgres password=snmc");
  if (!$conn) {
      echo "An error occured.\n";
      exit;
  }
                        
foreach (scandir($tmpdir) as $file) {
	if (!strpos($file, ".txt") === false) {
		$fieldseparator = ",";
		$lineseparator = "\n";
		$tablename = str_replace(".txt", "", $file);
		echo "Opening $file \n";
		$line = 0;
		$handle = fopen($tmpdir . $file, "r");
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if ($line > 0) {
				$query = "insert into $tablename values(";
                                $valueCount = 0;
                                foreach ($data as $value) {
                                $query.=($valueCount >0 ? "','" :"'").pg_escape_string($value);
                                $valueCount++;
                                }
				if ($tablename == "stops") {
                                $query.= "', ST_GeographyFromText('SRID=4326;POINT({$data[2]} {$data[0]})'));";
				} else {
                                  $query.= "');";
                                }
                                if ($tablename =="stop_times" && $data[1] == "") {
                                  $query = "insert into $tablename (trip_id,stop_id,stop_sequence) values('{$data[0]}','{$data[3]}','{$data[4]}');";
                                }
                                 
			}
                        $result = pg_query($conn, $query);
			$line++;
                        if ($line % 10000 == 0) echo "$line records... \n";
		}
		fclose($handle);
		echo "Found a total of $line records in $file.\n";

	}
}
?>
