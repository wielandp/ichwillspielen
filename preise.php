<?php
session_start();
##header('Content-Type:	application/pdf');
#header("Content-Type:	application/vnd-ms-excel");
#header("Content-Disposition:	attachment; filename=Abrechnung.xls");
## in head
#    <meta http-equiv="Content-Type" content="application/vnd-ms-excel" />
?>
<html>
<head>
	<title>TKL Hallenpreise</title>
</head>

<body> 
<table cellpadding="1" cellspacing="1">

<?php
include "cal.php";
connectDB();
getSetupData();

$query = "SELECT tagstunde DIV 100 tag, tagstunde MOD 100 stunde, euro FROM preise order by stunde, if (tag=0, 7, tag)";

# Preise ändern z.B. so (Mo-Fr ab 21h 18EUR):
# UPDATE `preise` SET `euro`=18 WHERE (tagstunde DIV 100) BETWEEN 1 and 5 and (tagstunde MOD 100) > 20 

$retv = mysqli_query($connection, $query);
if (!$retv) {
	die('Error: ' . $connection->error);
}
echo "<tr>".
			"<th>Zeit</td>".
			"<th>Montag</td>".
			"<th>Dienstag</td>".
			"<th>Mittwoch</td>".
			"<th>Donnerstag</td>".
			"<th>Freitag</td>".
			"<th>Samstag</td>".
			"<th>Sonntag</td>".
		"</tr>";
while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
  if ($row["tag"] == 1) {
    echo "<tr><td>".$row["stunde"]."</td>";
  }
  echo  	"<td>".$row["euro"]."</td>";
  if ($row["tag"] == 0) {
    echo "</tr>";
  }
}
mysqli_free_result($retv);
		
?>

</table>
</body>
</html>
