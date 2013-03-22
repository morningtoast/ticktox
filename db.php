<?
	if ($_POST["log"]) {
		$id = $_POST["log"];
		$file = "./data/".$id.".db";
		
		$a_db = unserialize(file_get_contents($file));
		
		$a_db["log"][] = array("task"=>$_POST["task"], "state"=>$_POST["state"], "date"=>time());
		
		$s_db = serialize($a_db);
			
			$fr = fopen($file,"w");
			fwrite($fr, $s_db);
			fclose($fr);		
	}
	
	if ($_POST["report"]) {
		$id = $_POST["report"];
		$file = "./data/".$id.".db";
		
		$a_db = unserialize(file_get_contents($file));
		$a_log = array();
		foreach ($a_db["log"] as $a_entry) {
			$a_log[$a_entry["task"]][] = $a_entry["date"];
		}
		
		foreach ($a_log as $task => $a_all) {
			echo $task."<br>";
			$a_sets = array_chunk($a_all, 2);
			foreach ($a_sets as $a_pair) {
				echo ($a_pair[1] - $a_pair[0])."<br>";
			}
		}
		
		//print_r($a_log);
	}

	if ($_POST["init"]) {
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
	
	
	if ($_POST["save"]) {
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