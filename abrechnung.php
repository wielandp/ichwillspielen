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
	<title>TKL Hallenbelegung</title>
</head>

<body> 
<table cellpadding="1" cellspacing="1">

<?php
include "cal.php";
connectDB();
getSetupData();

if (!isLoggedIn())
	die("Kein Admin.");

$query = "SELECT id, start, end , uid +1 \"Platz\", Typ, `firstname` , `lastname` , 
	`telnumber` \"Code\", substr( telnumber, 1, 1 ) \"Verkauf\", 
	substr( telnumber, 3, 2 ) \"Preis\", `body`, `bem` 
FROM `custom` 
WHERE `telnumber` LIKE '_-__-___%-______'
  AND start > '2021-06-05'
ORDER BY START";

$retv = mysqli_query($connection, $query);
if (!$retv) {
	die('Error: ' . $connection->error);
}
echo "<tr>".
			"<th>id</td>".
			"<th>start</td>".
			"<th>end</td>".
			"<th>Platz</td>".
			"<th>Typ</td>".
			"<th>Vorname</td>".
			"<th>Nachname</td>".
			"<th>Code</td>".
			"<th>Verkauf</td>".
			"<th>Preis</td>".
			"<th>body</td>".
			"<th>Bem</td>".
		"</tr>";
while ($row = mysqli_fetch_array($retv, MYSQLI_ASSOC)) {
  echo "<tr>".
			"<td>".$row["id"]."</td>".
			"<td>".$row["start"]."</td>".
			"<td>".$row["end"]."</td>".
			"<td>".$row["Platz"]."</td>".
			"<td>".$row["Typ"]."</td>".
			"<td>".$row["firstname"]."</td>".
			"<td>".$row["lastname"]."</td>".
			"<td>".$row["Code"]."</td>".
			"<td>".$row["Verkauf"]."</td>".
			"<td>".$row["Preis"]."</td>".
			"<td>".$row["body"]."</td>".
			"<td>".$row["bem"]."</td>".
		"</tr>";
}
mysqli_free_result($retv);
		
?>

</table>
</body>
</html>
