<?
	class App {
		function App($id) {
			$this->userId    = $id;
			$this->dataPath  = "./data";
			$this->indexPath = $this->setIndexFile();
			$this->logPath   = $this->setLogFile();
		}
		
		function setIndexFile() {
			return($this->dataPath."/".$this->userId.".index");
		}
		
		function setLogFile() {
			return($this->dataPath."/".$this->userId.".log");
		}
		
		
		function getIndex() {
			if (!file_exists($this->indexPath)) {
				$a_index = array();
				for ($i=1; $i <= 25; $i++) {
					$a_index[] = array(
						"id"   => $i,
						"name" => "empty"
					);
					
					$s_index = serialize($a_index);
				}
				
				$fr = fopen($this->indexPath,"w");
				fwrite($fr, $s_index);
				fclose($fr);
			} else {
				$a_index = unserialize(file_get_contents($this->indexPath));
			}
			
			return($a_index);
		}
		
		function saveIndex($data) {
			$fr = fopen($this->indexPath,"w");
			fwrite($fr, serialize($data));
			fclose($fr);		
		}
		
		function getLog() {
			$a_raw = file($this->logPath);
			$a_log = array();
			
			foreach($a_raw as $row) {
				$a_row = explode(";",$row);
				$a_log[] = array(
					"task"  => $a_row[0],
					"state" => $a_row[1],
					"date"  => $a_row[2],
					"hours" => $a_row[3],
					"time"  => $a_row[4]
				);
			}
			
			return($a_log);
		}
		
		function logEntry($data) {
			$a_entry = array("task"=>$data["task"],"state"=>$data["state"],"date"=>date("Y-m-d"),"hours"=>date("G:i"),"time"=>time());
			$s_log   = implode(";",$a_entry)."\n";
		
			$fr = fopen($this->logPath,"a");
			fwrite($fr, $s_log);
			fclose($fr);
			
			return($a_entry);
		}
		
		function getReport() {
			$a_log       = $this->getLog();
			$i_size      = count($a_log);
			$a_totals    = array();
			
			for ($a=0; $a < $i_size; $a) {
				$thisEntry = $a_log[$a];
				$i_nextPos = ($a+1);

				if ($i_nextPos < $i_size) {
					$nextEntry = $a_log[$i_nextPos];

					$diff = ($nextEntry["time"] - $thisEntry["time"]);

					if (($thisEntry["task"] == $nextEntry["task"]) and ($thisEntry["state"] == "off")) {
						$a += 2;
					} else {
						$a++;
					}

					$a_totals[$thisEntry["task"]] += $diff;
				} else {
					$a++;
				}
			} // END log loop
			
			$a_last = end($a_log);
			if ($a_last["state"] != "off") {
				$diff = (time() - $a_last["time"]);
				$a_totals[$a_last["task"]] += $diff;
			}
		
			$a_report       = array();
			$i_totalTime    = array_sum($a_totals);
			$i_totalPercent = 0;
			
			asort($a_totals);
			foreach ($a_totals as $task => $time) {
				$hours   = number_format(($time/60)/60,2);
				$percent = floor(($time / $i_totalTime)*100);
				

				if ($percent > 0) {
					$i_totalPercent += $percent;
					
					$a_report[] = array(
						"task"    => $task,
						"time"    => $time,
						"hours"   => $hours,
						"percent" => $percent
					);
				}
			}
			
			if ($i_totalPercent < 99) {
				$i_pad  = (99 - $i_totalPercent);
				$a_last = array_pop($a_report);
				$a_last["percent"] = ($a_last["percent"] + $i_pad);
				array_push($a_report, $a_last);
			}

			return($a_report);
		}
		
	} // END class
?>