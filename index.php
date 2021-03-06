﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>TKL Hallenbelegung</title>
	
	<link href="images/icon-4x.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
    <link rel="manifest" href="/manifest.json">
	<link rel='stylesheet' type='text/css' href='reset.css' />
	<link rel='stylesheet' type='text/css' href='libs/css/smoothness/jquery-ui-1.8.11.custom.css' />
	<link rel='stylesheet' type='text/css' href='jquery.weekcalendar.css' />
	<link rel='stylesheet' type='text/css' href='platzkalender.css' />
	<link rel='stylesheet' type='text/css' href='skins/gcalendar.css' />

	<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'></script>
	<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js'></script>
	<script type="text/javascript" src="libs/date.js"></script>
	<script type='text/javascript'><?php include "cal.php"; connectDB(); getSetupData(); echo provideVariables();?></script>
	<script type='text/javascript' src='jquery.weekcalendar.js'></script>
	<script type='text/javascript' src='platzkalender.js'></script>
</head>

<body> 
	<div id="logo"><img src="images/headlogo.jpg" width="102" height="114" alt="" border="0"/></div>
	<div id="about_button_container">
		<div id="switcher"></div>
		<button type="button" id="about_button">Hilfe</button>
	</div>
	<div id='calendar'></div>
	<div id="event_edit_container">
		<form>
			<input type="hidden" />
			<ul>
				<li>
					<label>Datum: </label><span class="date_holder"></span> 
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
				<li>
				<br>
				Falls Sie Probleme haben, wenden Sie sich bitte per Mail 
				oder Telefon an Herrn Wieland Pusch <a href="mailto:wieland@wielandpusch.de">wieland@wielandpusch.de</a> (0157/78910386).<br>
				Die eingegebenen Information werden gespeichert und veröffentlicht. Sie werden nach drei Jahren gelöscht.
<!--				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=wieland%40wielandpusch%2ede&lc=DE&item_name=TKLHalle&no_note=0&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest">
				Paypal</a>
-->
<!--				<a href="https://www.paypal.me/TKLangen">Paypal</a>
-->
				</li>
			</ul>
		</form>
	</div>
	<div id="event_edit_container2">
		<form>
			<input type="hidden" />
			<ul>
				<li>
					<label>Datum: </label><span class="date_holder"></span> 
				</li>
				<li>
					<label for="start">Start: </label><select name="start"><option value="">Start</option></select>
				</li>
				<li>
					<label for="end">Ende: </label><select name="end"><option value="">Ende</option></select>
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
				<li>
				<br>
				Falls Sie Probleme haben, wenden Sie sich bitte per Mail 
				oder Telefon an Herrn Wieland Pusch <a href="mailto:tkl@wielandpusch.de">tkl@wielandpusch.de</a> (0157/78910386).<br>
				Die eingegebenen Information werden gespeichert und veröffentlicht. Sie werden nach drei Jahren gelöscht.
				</li>
			</ul>
		</form>
	</div>	
	<div id="about">
		<h2>Hilfe</h2>
		<p>
			<a href="http://www.tennisklub-langen.de/traglufthalle/" target="_blank">Hinweise zur Buchung auf der TKL Seite</a>
		</p>
	</div>
	<div class="leftrow">
        <a href="/app.apk">Android App</a> | <a href="/pwa/">Web App</a> | <a href="https://www.tennisklub-langen.de/about/">Impressum</a> | <a href="https://www.tennisklub-langen.de/j/privacy">Datenschutz</a>
	</div>
</script>

</body>
</html>
