<?
	require_once("class_app.php");
	$App = new App($_REQUEST["id"]);
	
	if (isset($_POST["init"])) {
		$a_index = $App->getIndex();
		echo json_encode($a_index);
	}
	
	if (isset($_POST["save"])) {
		$App->saveIndex($_POST["data"]);
	}
	
	if (isset($_POST["log"])) {
		$a_entry = $App->logEntry($_POST["entry"]);
		echo json_encode($a_entry);
	}	
	
	if (isset($_POST["report"])) {
		$a_report = $App->getReport();
		echo json_encode($a_report);
	}

	if (isset($_POST["reset"])) {
		$App->resetData($_POST["reset"]);
	}
	
/*	
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

		$lastTask = md5(time());
		$prevTime = 0;
		$i_size   = count($a_log);
		$a_totals = array();
		$i_totalTime = 0;
		for ($a=0; $a < $i_size; $a) {
			$a_entry  = $a_log[$a];
			$thisTask = $a_entry[0];
			$thisTime = $a_entry[4];

			$i_nextPos = ($a+1);

			if ($i_nextPos < $i_size) {
				$nextEntry = $a_log[$i_nextPos];
				$nextTask  = $nextEntry[0];
				$nextTime  = $nextEntry[4];

				$diff = ($nextTime - $thisTime);

				if ($thisTask == $nextTask) {

					$a += 2;
				} else {
					$a++;
				}

				$a_totals[$thisTask] += $diff;
				$i_totalTime += $diff;
			} else {
				$a++;
			}
		}

		// Check last entry to see if it was not closed, if not, use current time for duration
		$a_last = end($a_log);
		if ($a_last[1] != "off") {
			$diff = (time() - $a_last[4]);
			$a_totals[$a_last[0]] += $diff;
		}

		$s_chart = '<div id="chart">';
		   $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
    
    
		foreach ($a_totals as $task => $s) {
			$hrs = number_format(($s/60)/60,2);
			$per = floor(($s / $i_totalTime)*100);
			$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
			$color = "#666";

			if ($per > 10) { $color = "#060"; }
			if ($per > 25) { $color = "#009"; }
			if ($per > 50) { $color = "#ff0"; }
			if ($per > 75) { $color = "#f00"; }

			$s_chart .= '<div class="bar" style="background-color:'.$color.';width:'.$per.'%">'.$task.'</div>';

			$a_report[] = $task." for ".$hrs." hrs";
		}
		$s_chart .= "</div>";
		
		//
		echo $s_chart;
		print_r($a_report);
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
		
			$a_db = unserialize(file_get_contents($file));
			print_r($a_db);
			echo stripslashes(json_encode($a_db));
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
*/	
?>