<?
	if (isset($_POST["log"])) {
		$id = $_POST["log"];
		$file = "./data/".$id.".log";
		
		if (file_exists($file)) {
			$s_log = file_get_contents($file);
		} else {
			$s_log = "";
		}
		
		$s_log .= implode(";",array($_POST["task"],$_POST["state"],date("Y-m-d"),date("G:i"),time()))."\n";
		
			
			$fr = fopen($file,"w");
			fwrite($fr, $s_log);
			fclose($fr);		
	}
	
	if (isset($_POST["report"])) {
		$id = $_POST["report"];
		$file = "./data/".$id.".log";
		
		$a_rawlog = file($file);
		$a_log = array();
		foreach ($a_rawlog as $line) {
			$a_log[] = explode(";", $line);
		}
		
		print_r($a_log);
	}

	if (isset($_POST["init"])) {
		$id = $_POST["init"];
		$file = "./data/".$id.".db";
		
		if (!file_exists($file)) {
			$a_db = array(
				"index" => array(),
				"log"   => array()
			);
			
			for ($i=1; $i <= 25; $i++) {
				$a_db["index"][] = array(
					"id" => $i,
					"name" => "empty"
				);
			}
			
			$s_db = serialize($a_db);
			
			$fr = fopen($file,"w");
			fwrite($fr, $s_db);
			fclose($fr);
			
			echo json_encode($a_db);
			
		} else {
		
			$s_db = json_encode(unserialize(file_get_contents($file)));
			echo $s_db;
		}
	}
	
	
	if (isset($_POST["save"])) {
		$id     = $_POST["save"];
		$file   = "data/".$id.".db";
		
		$a_new      = $_POST["data"];
		$a_existing = json_decode(file_get_contents($file), true);
		
		$a_existing["index"] = $a_new;
		
		$fr = fopen($file,"w");
		fwrite($fr, serialize($a_existing));
		fclose($fr);
	}
?>