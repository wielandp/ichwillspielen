<?php
if($_SERVER['HTTP_ORIGIN'] == "http://192.168.188.23:4200") {
  header("Access-Control-Allow-Origin: http://192.168.188.23:4200");		/* for angular local testserver */
} else {
  header("Access-Control-Allow-Origin: https://tklhalle.de");		/* normal server */
}
header("Access-Control-Allow-Credentials: true");
session_start();

/**************************************************/
/* cal.php
/*
/* Calendar backend 
/*
/**************************************************/
/* (c) 2016
/*
/* Author  : Andreas Nitsche, Wieland Pusch
/* Version : 1.0
/* Changes :
/*
/**************************************************/

require_once "common.php";

$setupData	= array();
$connection;

function getSetupData() {
	global $setupData, $connection;

	$retv = mysqli_query($connection, "SELECT skey, value FROM setup");
	if (!$retv) {
		die('Error: ' . $connection->error);
	}
	while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
		$setupData[$row['skey']] = $row['value'];
	}
	mysqli_free_result($retv);

    if (isLoggedIn() && $_SESSION['LID'] > 1000) {
		$query = "SELECT * FROM login WHERE id=".$_SESSION['LID'];
		$retv = mysqli_query($connection, $query);
		if (!$retv) {
			die('Error: ' . $connection->error);
		}
		while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
			$setupData["admin"] = $row['name'];
		}
		mysqli_free_result($retv);
	}

	$query = "SELECT * FROM preise ORDER BY tagstunde";
	$retv = mysqli_query($connection, $query);
	if (!$retv) {
		die('Error: ' . $connection->error);
	}
	while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
		$setupData["price".$row['tagstunde']] = $row['euro'];
	}
	mysqli_free_result($retv);

	$_SESSION["vorname"] = $_GET['vorname'];
	$_SESSION["nachname"] = $_GET['nachname'];

	return true;
}

function saveSetupData() {
	global $connection;
    foreach ($_REQUEST as $index => $val) {
        if ($index != "action") {
			if ($index == "admin") {
				$retv = mysqli_query($connection, "UPDATE login SET name='$val' WHERE id=".$_SESSION['LID']);
			} elseif ($index == "adminpwd") {
				$retv = mysqli_query($connection, "UPDATE login SET pwd=md5('$val') WHERE id=".$_SESSION['LID']);
			} else {
				$retv = mysqli_query($connection, "UPDATE setup SET value='$val' WHERE skey='$index'");
			}
            if (!$retv) {
                die('Error: ' . $connection->error);
            }
            mysqli_free_result($retv);
        }
    }

	return '1';
}

function JavaScript($script) {
	echo "<script language=\"JavaScript\" type=\"text/javascript\">$script</script>";
}

function redirect($where) {
	global $gVar;

	// for IE (extra bytes to flush webpage)
	for ($i=0; $i < 256; $i++)
		echo " ";

	ob_flush();
	flush();
	JavaScript("window.location.href='$where'");
}

function getRemoteIP() {
	$ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else
		$ip = "UNKNOWN";
	return $ip;
}

function saveTransaction($art, $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid) {
	global $mailUser, $connection;

	$datum = date('d.m.Y H:i:s', strtotime($start));
	$laenge = date('H:i:s', strtotime($end) - strtotime($start));
	$nachricht = "$art $title $uid $datum $laenge $id";
	//mail($mailUser, "SGE Halle $art $title $datum", $nachricht);
	$thisip = getRemoteIP();
	if (isLoggedIn()) {
	  $lid = $_SESSION['LID'];
	} else {
	  $lid = 0;
	}
	$query = "INSERT INTO savetransaction (action, actiondate, ipaddr, title, firstname, lastname, telnumber, start, end, typ, uid, lid)
			  VALUES ('$art', NOW(), '$thisip', '$title', '$firstname', '$lastname', '$telnumber', '$start', '$end', $typ, $uid, $lid)";
	if (!mysqli_query($connection, $query)) {
		die('Error: ' . $connection->error . "\n" . $query);
	}
}

function getPrice($wtag, $hour) {
	global $setupData;
	return $setupData["price".((($wtag + 1) % 7) * 100 + $hour)];
}

function check_code($code, $typ) {
    if ($typ == 0) {
      if (!isLoggedIn() && strlen($code) > 0) {
        if (strlen($code) < 6) {
	      die("Ein Code ist immer 6 Zeichen lang. Zu wenig Zeichen");
	    }
        if (strlen($code) > 6) {
	      die("Ein Code ist immer 6 Zeichen lang. Zu viele Zeichen");
	    }
        if (strstr($code, 'O') || strstr($code, 'I')) {
	      die("Ein Code hat nur Kleinbuchstaben und Ziffern.");
	    }
	  }
	  return strtolower($code);
	}
	return $code;
}

function saveEntry($id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid) {
	global $setupData, $connection;
	getSetupData();
	$telnumber = check_code($telnumber, $typ);
	$telnumber2 = $telnumber;
	$thisip = getRemoteIP();
	$bem = "";
	$maxdate2 = $setupData['maxdate2'];

	if (substr($start,0,10) > substr($setupData['maxdate'],0,10)
	||	substr($start,0,10) < substr($setupData['mindate'],0,10)
	) {
      die("Buchungen sind nur in der Saison möglich.");
	}
	if (substr($end,0,10) > substr($setupData['maxdate'],0,10)
	||	substr($end,0,10) < substr($setupData['mindate'],0,10)
	) {
      die("Buchungen sind nur in der Saison möglich (Ende).");
	}
	
//	die("Achtung DBG saveEntry id=$id.");
	
	if ($id == 0) {
		$query = "SELECT firstname, lastname FROM custom WHERE start <= '$start' and end >= '$end' and uid = $uid";
		$retv = mysqli_query($connection, $query);
		if (!$retv) {
			die('Error: ' . $connection->error);
		}
		if ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
			saveTransaction("Kollision1", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
			die("Buchung in diesem Zeitraum nicht möglich 1.");
		}
		$query = "SELECT firstname, lastname FROM weekly
			WHERE day=WEEKDAY('$start')+1 and start <= right('$start',8) and end >= TIMEDIFF('$end', concat(left('$start',10),' 00:00:00')) and uid = $uid
			  AND (typ = 2 or CAST('$start' as date) not in (select datum from holiday where datum = CAST('$start' as date)))
			  AND ADDDATE('$start', day-WEEKDAY('$start')-1) < '$maxdate2'";
		$retv = mysqli_query($connection, $query);
		if (!$retv) {
			die('Error: ' . $connection->error);
		}
		if ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
			saveTransaction("Kollision2", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
			die("Buchung in diesem Zeitraum nicht möglich 2.");
		}
        if ($typ == 1 && strlen($telnumber) != 6 && strtotime($start)-time()>(60*60*2)) {
            if (!isLoggedIn()) {
				saveTransaction("Jzufrüh", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
                die("Jugend-Buchungen können nur ab 2 Stunden vor Beginn eingetragen werden.");
			}
        }
        if ($typ == 1 && strlen($telnumber) == 6 && strtotime($start)-time()>(60*60*3)) {
            if (!isLoggedIn()) {
				saveTransaction("J3zufrüh", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
                die("3h-Jugend-Buchungen können nur ab 3 Stunden vor Beginn eingetragen werden.");
			}
        }
        if ($typ == 1 && strlen($telnumber) != 6 && !isLoggedIn() && strtotime($start)-time()>(60*60*2)-30) {
			sleep(strtotime($start)-time()-(60*60*2)+30);
		}
        if ($typ == 0 && strtotime($start)-time()>(60*60*24*7*4)) {
            if (!isLoggedIn())
                die("Einzel-Buchungen können nur ab 4 Wochen vor Beginn eingetragen werden.");
        }
        if (($typ == 0 || ($typ == 1 && strlen($telnumber) == 6)) && (strlen($telnumber) == 6 || !isLoggedIn())) {
			$query = "SELECT *, WEEKDAY('$start') wtag, HOUR('$start') hour FROM marke WHERE code='$telnumber' and used=0";
			$retv = mysqli_query($connection, $query);
			if (!$retv) {
				die('Error: ' . $connection->error);
			}
			if ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
				$telnumber2 = $row['text']."-".sprintf("%02d",$row['preis'])."-".sprintf("%03d",$row['lfdnr'])."-$telnumber";
				$preis	= $row['preis'];
				$bem	= $row['bem'];
			} else {
				$query = "INSERT INTO accesslog (typ, fail, ipaddr, text) VALUES (2, 1, '$thisip', '$telnumber')";
				if (!mysqli_query($connection, $query)) {
					die('Error: ' . $connection->error . "\n" . $query);
				}
				saveTransaction("Ungültig", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
				die("Marke unbekannt.");
			}
			if ($typ != 1 && $row['text'] === "3") {
				if (!isLoggedIn()) {
					saveTransaction("J3notJ", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
					die("Mit 3h-Jugend-Codes können nur Freie-Jugend-Buchung gebucht werden.");
				}
			}
			if ($typ == 1 && $row['text'] !== "3") {
				if (!isLoggedIn()) {
					saveTransaction("JkeinJ3", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
					die("Code ist kein 3h-Jugend-Code. Bitte Buchung Erwachsene auswählen.");
				}
			}
			$oprice = getPrice($row['wtag'], $row['hour']);
#			$row['wtag'] = (($row['wtag'] + 1) % 7) * 100 + $row['hour'];
#			die("preis der marke=".$preis." start=$start wtag=".$row['wtag']." oprice=$oprice");
			if ($row['text'] !== "J" && $row['text'] !== "3" && $preis < $oprice) {
			  die("Wert der Marke ".$preis." EUR zu niedrig. Preis der Stunde $oprice EUR. Buchung nicht möglich!");
			}
			if ($preis > $oprice) {
			  echo "Achtung: Wert der Marke ".$preis." EUR Preis der Stunde $oprice EUR\n";
			}
			$query = "UPDATE marke SET used=used+1
					  WHERE code='$telnumber' and used=0";
			if (!$retv = mysqli_query($connection, $query)) {
				die('Error: ' . $connection->error);
			}
			$rows = $connection->affected_rows;
			if ($rows != 1) {
				die("Marke ungültig");
			}
		}
		$query = "INSERT INTO custom (title, firstname, lastname, telnumber, body, start, end, typ, uid, bem) 
				  VALUES ('$title', '$firstname', '$lastname', '$telnumber2', '$body', '$start', '$end', $typ, $uid, '$bem')";
	} else {
        if (strtotime($start)-time()<(60*60*24)) {
            if (!isLoggedIn())
                die("Buchungen können nur bis 24 Stunden vor Beginn geändert werden.");
        }
		$query = "UPDATE custom 
				  SET title='$title', firstname='$firstname', lastname='$lastname', body='$body', start='$start', end='$end', typ=$typ, uid=$uid 
				  WHERE id=$id";
	}
	if (!$retv = mysqli_query($connection, $query)) {
		$connerr = $connection->error;
		if ($id == 0) {
			saveTransaction("BuchFail", $id, $title, $firstname, $lastname, $telnumber2, $connerror, $start, $end, $typ, $uid);
			if ($typ == 0 && (strlen($telnumber) == 6 || !isLoggedIn())) {
				$query = "UPDATE marke SET used=used-1
						  WHERE code='$telnumber' and used=1";
				if (!$retv = mysqli_query($connection, $query)) {
					die('Error: ' . $connection->error);
				}
			}
		}
		die('Error2: ' . $connerr);
	}
	if ($id == 0) {
		$id = $connection->insert_id;
		saveTransaction("Buchung", $id, $title, $firstname, $lastname, $telnumber2, $body, $start, $end, $typ, $uid);

        if ($typ == 0 || ($typ == 1 && strlen($telnumber) == 6)/*&& !isLoggedIn()*/) {
			$query = "UPDATE marke SET custom_id=$id WHERE code='$telnumber' and used=1";
			if (!$retv = mysqli_query($connection, $query) || $retv->affected_rows != 1) {
				die('Error: ' . $connection->error);
			}
		}
		return $id;
	} else {
		saveTransaction("Änderung", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
		return $id;
	}
}

function saveEntryFree($id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid) {
	global $setupData, $connection;
	getSetupData();
	$telnumber2 = $telnumber;
	$thisip = getRemoteIP();

	if (substr($start,0,10) > substr($setupData['maxdate'],0,10)
	||	substr($start,0,10) < substr($setupData['mindate'],0,10)
	) {
      die("Buchungen sind nur in der Saison möglich.");
	}
	if (substr($end,0,10) > substr($setupData['maxdate'],0,10)
	||	substr($end,0,10) < substr($setupData['mindate'],0,10)
	) {
      die("Buchungen sind nur in der Saison möglich (Ende).");
	}
	if ($id == 0) {
		$query = "INSERT INTO ausfall (title, firstname, lastname, telnumber, body, start, end, typ, uid) 
				  VALUES ('$title', '$firstname', '$lastname', '$telnumber2', '$body', '$start', '$end', $typ, $uid)";
	} else {
        if (strtotime($start)-time()<(60*60*24)) {
            if (!isLoggedIn())
                die("Buchungen können nur bis 24 Stunden vor Beginn geändert werden.");
        }
		$query = "UPDATE ausfall
				  SET title='$title', firstname='$firstname', lastname='$lastname', body='$body', start='$start', end='$end', typ=$typ, uid=$uid 
				  WHERE id=$id";
	}
	if (!$retv = mysqli_query($connection, $query)) {
		$connerr = $connection->error;
		if ($id == 0) {
			saveTransaction("BuchFreeFail", $id, $title, $firstname, $lastname, $telnumber2, $connerror, $start, $end, $typ, $uid);
		}
		die('Error2: ' . $connerr);
	}
	if ($id == 0) {
		$id = $connection->insert_id;
		saveTransaction("Free", $id, $title, $firstname, $lastname, $telnumber2, $body, $start, $end, $typ, $uid);

		return $id;
	} else {
		saveTransaction("ÄnderungFree", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
		return $id;
	}
}

function saveFixedEntry($id, $title, $firstname, $lastname, $telnumber, $body, $day, $start, $end, $typ, $uid) {
	global $connection;
	if (!isLoggedIn()) die('Nicht angemeldet.');
	if ($id == 0) {
		$query = $connection->prepare("INSERT INTO weekly (title, firstname, lastname, telnumber, body, day, start, end, typ, uid)
				  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$query->bind_param("ssssssssii", $title, $firstname, $lastname, $telnumber, $body, $day, $start, $end, $typ, $uid);
	} else {
		$query = $connection->prepare("UPDATE weekly
				  SET title=?, firstname=?, lastname=?, telnumber=?, body=?, day=?, start=?, end=?, typ=?, uid=? WHERE id=?");
		$query->bind_param("ssssssssiii", $title, $firstname, $lastname, $telnumber, $body, $day, $start, $end, $typ, $uid, $id);
	}
	$query->execute();
	if (!$retv = $query->get_result()) {
		die('Error: ' . $connection->error . " query: $query");
	}
	if ($id == 0)
		return $connection->insert_id;
	else
		return $id;
}

function deleteId($id, $telnumber, $typ) {
	global $connection;
	$title; $firstname; $lastname; $telnumber2; $body; $start; $end; $uid; 
	
	getEvent($id, $typ, $title, $firstname, $lastname, $telnumber2, $body, $start, $end, $uid);

    if ($typ <= 1) {
        if (strtotime($start)-time()<(60*60*24)) {
            if (!isLoggedIn())
                die("Buchungen können nur bis 24 Stunden vor Beginn geändert werden.");
        }
		$telnumber = check_code($telnumber, $typ);
		if (!isLoggedIn() && (strlen($telnumber) < 1 || ($telnumber != $telnumber2 && $telnumber != substr($telnumber2, -6)))) {
			saveTransaction("Ung.Del", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
			die("Löschen nur mit Code möglich.");
		}
		$query = "UPDATE marke SET used=0, custom_id=NULL WHERE custom_id=$id";
		if (!mysqli_query($connection, $query)) {
			die('Error: ' . $connection->error);
		}
		$query = "DELETE FROM custom WHERE id=$id";
	} else {
		if (!isLoggedIn()) die('Nicht angemeldet.');
		$query = "DELETE FROM weekly WHERE id=$id";
	}
	if (!mysqli_query($connection, $query)) {
		die('Error: ' . $connection->error);
	}
	saveTransaction("Löschung", $id, $title, $firstname, $lastname, $telnumber, $body, $start, $end, $typ, $uid);
	return '1';
}

function getEvent($id, $typ, &$title, &$firstname, &$lastname, &$telnumber, &$body, &$start, &$end, &$uid) {
	global $connection;
	if ($typ <= 1) {
		$query = "SELECT * FROM custom WHERE id=$id";
	} else {
		$query = "SELECT * FROM weekly WHERE id=$id";
	}
	$retv = mysqli_query($connection, $query);
	if (!$retv) {
		die('Error: ' . $connection->error);
	}
	while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
		$title		= addslashes($row['title']);
		$firstname	= $row['firstname'];
		$lastname	= $row['lastname'];
		$telnumber	= $row['telnumber'];
		$body		= $row['body'];
		$start		= date('c', strtotime($row['start']));
		$end		= date('c', strtotime($row['end']));
		$uid 		= (int)$row['uid'];
	}
	mysqli_free_result($retv);
	return true;
}

function getEvents($start, $end) {
	global $setupData, $connection;
	getSetupData();
	$arr=array();
	$maxdate2 = $setupData['maxdate2'];

	// Get all events in one big query
	$queryevents = "
		(SELECT id, 
				title, ";
	if (isLoggedIn()) {
	  $queryevents .= "
				firstname,
				lastname,
				telnumber,
				body, ";
	} else {
	  $queryevents .= "
				'' as firstname,
				case lastname when 'Platzpflege' then lastname else
					case typ when 2 then 'Abo' else 'Training' end
				end as lastname,
				'' as telnumber,
				'' as body, ";
	}
	$queryevents .= "
				ADDDATE(TIMESTAMP('$start', start), day-WEEKDAY('$start')-1) AS start, 
				ADDDATE(TIMESTAMP('$start', end), day-WEEKDAY('$start')-1) AS end, 
				typ, 
				uid,
				1 as readonly
				FROM weekly 
				WHERE day>=WEEKDAY('$start')+1 AND day<=WEEKDAY('$end' - INTERVAL 1 SECOND)+1
				  AND (typ = 2 or ADDDATE('$start', day-WEEKDAY('$start')-1) not in (select datum from holiday where datum = ADDDATE('$start', day-WEEKDAY('$start')-1)))
				  AND ADDDATE('$start', day-WEEKDAY('$start')-1) < '$maxdate2'
		)
		UNION
		(SELECT id, 
				title, 
				firstname,
				lastname,";
	if (isLoggedIn()) {
	  $queryevents .= "
				telnumber,";
	} else {
	  $queryevents .= "
				substr(telnumber, 1, 9) as telnumber,";
	}
	$queryevents .= "
				body, 
				start, 
				end, 
				typ, 
				uid,
				0 as readonly
				FROM custom 
				WHERE start>='$start' AND end<='$end')
		ORDER BY start";
	$retv = mysqli_query($connection, $queryevents);
	if (!$retv) {
		die("Error1: $retv " . $connection->error);
	}
	while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
		if (substr($row['start'],0,10) <= substr($setupData['maxdate'],0,10) &&
			substr($row['start'],0,10) >= substr($setupData['mindate'],0,10)
		) {
			$arr[]=array(
			'id'	    => $row['id'],
			'title'	    => addslashes($row['title']),
			'firstname'	=> $row['firstname'],
			'lastname'	=> $row['lastname'],
			'telnumber'	=> $row['telnumber'],
			'body'	    => $row['body'],
			'start'     => substr(date('c', strtotime($row['start'])),0,19),
			'end'	    => substr(date('c', strtotime($row['end'])),0,19),
			'typ'	    => (int)$row['typ'],
			'userId'    => array((int)$row['uid']),
			'readOnly'  => (boolean)$row['readonly']
			);
		}
	}
	mysqli_free_result($retv);
	
	return $arr;
}

function compressFreeDaySlots($free, $thisdate) {
	global $setupData;
	$freetimes = array();
	$courts = array (0, 1, 2);
	
	foreach ($courts as $court) {
		$freetimes[$court] = array();
		$t = strtotime($thisdate . ' ' . $setupData['startdaytime'] . ':00');
		$endtime = strtotime($thisdate . ' ' . $setupData['enddaytime'] . ':00');
		
		while ($t < $endtime) {
			if (($free[$court][$t]['st'] == "RESERVED") && ($free[$court][$t]['typ'] > 1)) {
				$startReserved = $t;
				while (($t < $endtime) && ($free[$court][$t]['st'] == "RESERVED") && ($free[$court][$t]['typ'] > 1)) {
					$t = strtotime('+1 hour', $t);
				}
				$freetimes[$court][$startReserved] = array();
				$freetimes[$court][$startReserved]['st'] = "RESERVED";
				$freetimes[$court][$startReserved]['end'] = $t;
			} else if (($free[$court][$t]['st'] == "RESERVED") && ($free[$court][$t]['typ'] <= 1)) {
				$freetimes[$court][$t] = $free[$court][$t];
				$t = strtotime('+1 hour', $t);
			} else {
				$freetimes[$court][$t]['st'] = "FREE";
				$t = strtotime('+1 hour', $t);
			}
		}
	}
	return $freetimes;
}

function getFreeDaySlots($events, $thisdate) {
	global $setupData;
	$free = array();
	$courts = array (0, 1, 2);
	
	foreach ($courts as $court) {
		$free[$court] = array();
		$t = strtotime($thisdate . ' ' . $setupData['startdaytime'] . ':00');
		$endtime = strtotime($thisdate . ' ' . $setupData['enddaytime'] . ':00');
		
		while ($t < $endtime) {
			$free[$court][$t] = array();
			$free[$court][$t]['st'] = "FREE";
			$t = strtotime('+1 hour', $t);
		}
	}
	
	foreach ($events as $value) {
		$t = strtotime($value['start']);
		while ($t < strtotime($value['end'])) {
			$free[$value['userId'][0]][$t]['st'] = "RESERVED";
			$free[$value['userId'][0]][$t]['id'] = $value['id'];
			$free[$value['userId'][0]][$t]['end'] = strtotime($value['end']);
			$free[$value['userId'][0]][$t]['typ'] = $value['typ'];
			$free[$value['userId'][0]][$t]['title'] = $value['title'];
			$t = strtotime('+1 hour', $t);
		}
	}
	//return $free;
	return compressFreeDaySlots($free, $thisdate);
}

function connectDB() {
	global $dbHost, $dbUser, $dbPassword, $dbName, $connection;

	$connection = new mysqli($dbHost, $dbUser, $dbPassword) or die("Verbindungsversuch fehlgeschlagen");
	if ($connection->connect_errno) {
		die("Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error);
	}
	$connection->set_charset('utf8');
	mysqli_select_db($connection, $dbName) or die("Konnte die Datenbank nicht waehlen.");
}

function provideVariables() {
	global $setupData, $connection;
	$ret = "";
	
	foreach ($setupData as $idx => $val) {
		if (($idx != "admin") && ($idx != "adminpwd")) {
			if (strstr($idx, "date") === false)
				$ret .= "var s$idx = \"$val\";";
			else
				$ret .= "var s$idx = ".strtotime($val).";";
		}
	}
	$ret .= "var \$vorname=\"".$_GET["vorname"]."\";";
	$ret .= "var \$nachname=\"".$_GET["nachname"]."\";";
	$ret .= "var \$marke=\"".$_GET["marke"]."\";";
	$ret .= "var \$typ=\"".$_GET["typ"]."\";";

	$dateTimeZoneBerlin = new DateTimeZone('Europe/Berlin');
	$dateTimeBerlin = new DateTime("now", $dateTimeZoneBerlin);
	$timeOffset = $dateTimeZoneBerlin->getOffset($dateTimeBerlin);
	$ret .= "var clientOffset = new Date().getTimezoneOffset()*60;var tz_offset=".$timeOffset."+clientOffset;";

	$arr = array();
	$query = "SELECT * FROM preise ORDER BY tagstunde";
	$retv = mysqli_query($connection, $query);
	if (!$retv) {
		die('Error: ' . $connection->error);
	}
	while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
		$arr[$row['tagstunde']]	= $row['euro'];
		$setupData["price".$row['tagstunde']] = $row['euro'];
	}
	mysqli_free_result($retv);
	$ret .= "var prices = ".json_encode($arr).";";

    if (isLoggedIn())
        $ret .= "var loggedIn=true";
    else
        $ret .= "var loggedIn=false";
	return $ret;
}

function login() {
	global $dbUser, $dbPassword;
	global $setupData, $connection;
	
	$thisip = getRemoteIP();

	if (!isset($_SESSION['SID'])) {
		if (isset($_REQUEST['uid']) && isset($_REQUEST['pwd'])) {
			$query = $connection->prepare("SELECT * FROM login WHERE name=? and pwd=?");
			$uid = $_REQUEST['uid'];
			$pwd = md5($_REQUEST['pwd']);
			$query->bind_param("ss", $uid, $pwd); // "s" bedeutet, dass als Zeichenkette gebunden
			$query->execute();
			$retv = $query->get_result();
			if (!$retv) {
				die('Error: ' . $connection->error);
			}
			if ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
				$query = $connection->prepare("INSERT INTO accesslog (typ, fail, ipaddr, text) VALUES (1, 0, '$thisip', ?)");
				$uidpwd = $_REQUEST['uid']."/";
				$query->bind_param("s", $uidpwd); // "s" bedeutet, dass als Zeichenkette gebunden
				if (!$query->execute()) {
					die('Error: ' . $connection->error . "\n" . $query);
				}

				$_SESSION['SID'] = session_id();
				$_SESSION['LID'] = $row['id'];
				// logged in
				return true;
			} else {
				echo 'Username or password wrong.';
				logout();

				$query = $connection->prepare("INSERT INTO accesslog (typ, fail, ipaddr, text) VALUES (1, 1, '$thisip', ?)");
				$uidpwd = $_REQUEST['uid']."/".md5($_REQUEST['pwd']);
				$query->bind_param("s", $uidpwd); // "s" bedeutet, dass als Zeichenkette gebunden
				if (!$query->execute()) {
					die('Error: ' . $connection->error . "\n" . $query);
				}
			}
		} else {
			echo 'No Username or password provided.';
			logout();
		}
	} else {
		echo 'Still logged on.';
		logout();
	}
	return false;
}

function isLoggedIn() {
	return isset($_SESSION['SID']);
}

function logout() {
	unset($_SESSION['SID']);
	setcookie("PHPSESSID","",time()-3600,"/");
	session_destroy();
}

// AJAX requests
if (isset($_REQUEST['action'])) {
	connectDB();
	date_default_timezone_set('Europe/Berlin');

	switch($_REQUEST['action']) {
		case 'save': {
			echo saveEntry((isset($_REQUEST['id'])?(int)$_REQUEST['id']:0),
						 (isset($_REQUEST['title'])?$connection->real_escape_string($_REQUEST['title']):''),
						 (isset($_REQUEST['firstname'])?$connection->real_escape_string($_REQUEST['firstname']):''),
						 (isset($_REQUEST['lastname'])?$connection->real_escape_string($_REQUEST['lastname']):''),
						 (isset($_REQUEST['telnumber'])?$connection->real_escape_string($_REQUEST['telnumber']):''),
						 (isset($_REQUEST['body'])?$connection->real_escape_string($_REQUEST['body']):''),
						 date('Y-m-d H:i:s',(int)$_REQUEST['start']),
						 date('Y-m-d H:i:s',(int)$_REQUEST['end']),
						 (isset($_REQUEST['typ'])?(int)$_REQUEST['typ']:0),
						 (isset($_REQUEST['uid'])?(int)$_REQUEST['uid']:0));
			exit;
		}
		case 'savefree': {
			echo saveEntryFree((isset($_REQUEST['id'])?(int)$_REQUEST['id']:0),
						 (isset($_REQUEST['title'])?$connection->real_escape_string($_REQUEST['title']):''),
						 (isset($_REQUEST['firstname'])?$connection->real_escape_string($_REQUEST['firstname']):''),
						 (isset($_REQUEST['lastname'])?$connection->real_escape_string($_REQUEST['lastname']):''),
						 (isset($_REQUEST['telnumber'])?$connection->real_escape_string($_REQUEST['telnumber']):''),
						 (isset($_REQUEST['body'])?$connection->real_escape_string($_REQUEST['body']):''),
						 date('Y-m-d H:i:s',(int)$_REQUEST['start']),
						 date('Y-m-d H:i:s',(int)$_REQUEST['end']),
						 (isset($_REQUEST['typ'])?(int)$_REQUEST['typ']:0),
						 (isset($_REQUEST['uid'])?(int)$_REQUEST['uid']:0));
			exit;
		}
		case 'savem': {
			echo saveEntry((isset($_REQUEST['id'])?(int)$_REQUEST['id']:0),
						 (isset($_REQUEST['title'])?$connection->real_escape_string($_REQUEST['title']):''),
						 (isset($_REQUEST['firstname'])?$connection->real_escape_string($_REQUEST['firstname']):''),
						 (isset($_REQUEST['lastname'])?$connection->real_escape_string($_REQUEST['lastname']):''),
						 (isset($_REQUEST['telnumber'])?$connection->real_escape_string($_REQUEST['telnumber']):''),
						 '',
						 date('Y-m-d H:i:s',(int)$_REQUEST['start']),
						 date('Y-m-d H:i:s',(int)$_REQUEST['end']),
						 0,
						 (isset($_REQUEST['uid'])?(int)$_REQUEST['uid']:0));
			exit;
		}
		case 'savefixed': {
			echo saveFixedEntry((isset($_REQUEST['id'])?(int)$_REQUEST['id']:0),
						 (isset($_REQUEST['title'])?$connection->real_escape_string($_REQUEST['title']):''),
						 (isset($_REQUEST['firstname'])?$connection->real_escape_string($_REQUEST['firstname']):''),
						 (isset($_REQUEST['lastname'])?$connection->real_escape_string($_REQUEST['lastname']):''),
						 (isset($_REQUEST['telnumber'])?$connection->real_escape_string($_REQUEST['telnumber']):''),
						 (isset($_REQUEST['body'])?$connection->real_escape_string($_REQUEST['body']):''),
						 date('N',$_REQUEST['start']),
						 date('Y-m-d H:i:s',(int)$_REQUEST['start']),
						 date('Y-m-d H:i:s',(int)$_REQUEST['end']),
						 (isset($_REQUEST['typ'])?(int)$_REQUEST['typ']:0),
						 (isset($_REQUEST['uid'])?(int)$_REQUEST['uid']:0));
			exit;
		}
		case 'delete': {
			echo deleteId((isset($_REQUEST['id'])?(int)$_REQUEST['id']:0), (isset($_REQUEST['telnumber'])?$connection->real_escape_string($_REQUEST['telnumber']):''), (isset($_REQUEST['typ'])?(int)$_REQUEST['typ']:0));
			exit;
		}
		case 'get_events': {
			$arr = getEvents(date('Y-m-d 00:00:00',$_REQUEST['start']+10*3600), date('Y-m-d 00:00:00',$_REQUEST['end']+10*3600));
			echo '{"events":'.json_encode($arr).'}';
			exit;
		}		
		case 'get_free_events': {
			$freelist = array();
			$count = 0;
			getSetupData();
			$arr = getEvents(date('Y-m-d H:i:s',$_REQUEST['start']), date('Y-m-d H:i:s',$_REQUEST['end']));
			$free = getFreeDaySlots($arr, date('Y-m-d',$_REQUEST['start']));

			echo json_encode($free);
			exit;
		}
		case 'getsetupdata': {
			getSetupData();
			echo provideVariables();
			exit;
		}
		case 'getsetup': {
            if (!isLoggedIn()) die("Nicht angemeldet.");
			getSetupData();
			echo json_encode($setupData);
            exit;
		}
		case 'setsetup': {
            if (!isLoggedIn()) die("Nicht angemeldet.");
            echo saveSetupData();
            exit;
		}
		case 'login': {
			if (login()) {
				echo '1';
			} else {
				echo '0';
			}
			exit;
		}
		case 'logout': {
			if (isLoggedIn()) {
				logout();
				echo '1';
			}
			exit;
		}
	}
}

?>