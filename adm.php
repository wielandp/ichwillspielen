<?php 
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>TKL Hallenbelegung Admininstration</title>

	<link href="images/icon-4x.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <link rel="manifest" href="/manifestadm.json">
	<link rel='stylesheet' type='text/css' href='reset.css' />
	<link rel='stylesheet' type='text/css' href='libs/css/smoothness/jquery-ui-1.8.11.custom.css' />
	<link rel='stylesheet' type='text/css' href='jquery.weekcalendar.css' />
	<link rel='stylesheet' type='text/css' href='platzkalender.css' />
	<link rel='stylesheet' type='text/css' href='skins/gcalendar.css' />
	<link rel='stylesheet' type='text/css' href='libs/DataTables-1.9.4/media/css/demo_table.css' />

	<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'></script>
	<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js'></script>
	<script type="text/javascript" src="libs/date.js"></script>
	<script type='text/javascript' src='jquery.weekcalendar.js'></script>
	<script type="text/javascript" src="libs/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script>
	<script type='text/javascript'><?php include "cal.php"; connectDB(); getSetupData(); echo provideVariables();?></script>
	<script type='text/javascript' src='platzkalender.js'></script>
	<script type='text/javascript' src='setup.js'></script>
	<script type='text/javascript' src='login.js'></script>
	<script type='text/javascript'>admin = true;<?php //if (!isset($_SESSION['SID'])) include "login.js";?></script>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            if (loggedIn) {
                $('#ttable').dataTable( {
                    "bProcessing": true,
                    "bServerSide": true,
                    "aaSorting": [[ 1, "desc" ]],
                    "sAjaxSource": "dt_server_processing.php"
                } );
            }
        } );
    </script>
</head>

<body> 
	<div id="logo"><img src="images/headlogo.jpg" width="102" height="114" alt="" border="0"/></div>
	<div id="about_button_container">
		<div id="switcher"></div>
		<a TARGET="_blank" href="abrechnung.php">Abrechnung</a>
		<button type="button" id="setup_button">Einstellungen</button>
		<button type="button" id="logout_button">Logout</button>
	</div>
	<div id='calendar'></div>
	<div id="event_edit_container">
		<form>
			<input type="hidden" />
			<ul>
				<li>
					<span>Datum: </span><span class="date_holder"></span> 
				</li>
				<li>
					<label for="start">Start: </label><select name="start"><option value="">Start</option></select>
				</li>
				<li>
					<label for="end">Ende: </label><select name="end"><option value="">Ende</option></select>
				</li>
				<li>
					<label for="typ">Typ: </label>
					<select name="typ">
						<option value="0">Buchung Erwachsene</option>
						<option value="1">Freie Buchung Jugend</option>
						<option value="2">Abo</option>
						<option value="3">Training</option>
						<option value="4">Ausfall</option>
					</select>
				</li>
				<li>
					<label for="firstname">Vorname<sup>*</sup>: </label><input type="text" name="firstname" />
				</li>
				<li>
					<label for="lastname">Name<sup>*</sup>: </label><input type="text" name="lastname" />
				</li>
				<li>
					<label for="telnumber">Code<sup>*</sup>: </label><input type="text" name="telnumber" />
				</li>
				<li>
					<label for="body">Kommentar: </label><textarea name="body"></textarea>
				</li>
			</ul>
		</form>
	</div>
	<div id="login">
		<form>
			<input type="hidden" />
			<ul>
				<li>
					<label for="uid">Benutzer: </label><input type="text" name="uid" />
				</li>
				<li>
					<label for="pwd">Passwort: </label><input type="password" name="pwd" />
				</li>
			</ul>
		</form>
	</div>
	<div id="setup">
		<form>
			<input type="hidden" />
			<ul>
				<li>
					<label for="admin">Admin: </label><input type="text" name="admin" />
				</li>
				<li>
					<label for="adminpwd">Passwort: </label><input type="password" name="adminpwd" />
				</li>
				<li>
					<label for="apptitle">Apptitle: </label><input type="text" name="apptitle" />
				</li>
				<li>
					<label for="startdaytime">Startzeit: </label><input type="text" name="startdaytime" />
				</li>
				<li>
					<label for="enddaytime">Endzeit: </label><input type="text" name="enddaytime" />
				</li>
				<li>
					<label for="mindate">Mindate: </label><input type="text" name="mindate" />
				</li>
				<li>
					<label for="maxdate">Maxdate: </label><input type="text" name="maxdate" />
				</li>
			</ul>
		</form>
	</div>
    <div id="transaction">
        <hr style="color:#ACADC9;background-color:#ACADC9;height:1px;">
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="ttable">
            <thead>
                <tr>
                    <th width="5%">Aktion</th>
                    <th width="10%">Datum</th>
                    <th width="10%">IP</th>
                    <th width="15%">Vorname</th>
                    <th width="15%">Name</th>
                    <th width="10%">Code</th>
                    <th width="10%">Start</th>
                    <th width="10%">Ende</th>
                    <th width="3%">Typ</th>
                    <th width="3%">Platz</th>
                    <th width="3%">User</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="dataTables_empty"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>Aktion</th>
                    <th>Datum</th>
                    <th>IP</th>
                    <th>Vorname</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Start</th>
                    <th>Ende</th>
                    <th>Typ</th>
                    <th>Platz</th>
                    <th>User</th>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
