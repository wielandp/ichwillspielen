var admin = false;

$(document).ready(function() {

	var $calendar = $('#calendar');
	var id = 10;

	$calendar.weekCalendar({
		dateFormat: 'd. F Y',
		use24Hour: true,
		minDate: new Date(smindate*1000),
		maxDate: new Date(smaxdate*1000),
		eventHeader: function(calEvent, calendar) {
			if ($(window).width() <= 960) {
				return calendar.weekCalendar('formatTime', calEvent.start);
			} else {
				return calendar.weekCalendar('formatTime', calEvent.start) + ' - ' + calendar.weekCalendar('formatTime', calEvent.end);
			}
		
			return '';
		},
		eventBody: function(calEvent, calendar) {
			
			if(calEvent.lastname == "" && calEvent.firstname == ""){
				return "";
			}else{
				return calEvent.lastname + ", " + calEvent.firstname;
			}
	    },
		shortMonths: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
		longMonths: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
		shortDays: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fri', 'Sam'],
		longDays: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
		users: ['Platz1', 'Platz2', 'Platz3'],
		showAsSeparateUser: true,
		displayOddEven:false,
		hourLine: true,
		timeslotsPerHour : 1,
		defaultEventLength: 1,
		newEventText: 'Neue Buchung',
		allowCalEventOverlap : false,
		overlapEventsSeparate: true,
		firstDayOfWeek : 1,
		businessHours :{start: parseInt(sstartdaytime), end: parseInt(senddaytime), limitDisplay: true },
		daysToShow : 7,
		buttons: true,
		textSize: 12,
		timeslotHeight: 36,
		timeSeparator: ' - ',
		buttonText: {
			today: 'heute',
			lastWeek: 'zurück',
			nextWeek: 'vor'
		},

		//minBodyHeight: 480,
		switchDisplay: {'Tag': 1, 'Woche': 7},
		title: function(daysToShow) {
			return daysToShow == 1 ? '%date%' : 'Hallenbelegung %start% - %end%';
		},
		height : function($calendar) {
            return 700;
			//return $(window).height() - 80 - 1;
			//return $(window).height() - $("h1").outerHeight() - 1;
		},
		eventRender : function(calEvent, $event) {
			/*if (calEvent.end.getTime() < new Date().getTime()) {
				$event.css("backgroundColor", "#aaa");
				$event.find(".wc-time").css({
					"backgroundColor" : "#999",
					"border" : "1px solid #888"
				});
			}*/
			if (calEvent.typ == 0) {
				if ((calEvent.start-(new Date())) < (1000*60*60*12) && !admin) {
					$event.find(".wc-time").css({
						"backgroundColor" : "#999",
						"border" : "1px solid #888"
					});
				}
			} else if (calEvent.typ == 1) {
				$event.css("backgroundColor", "rgb(40, 180, 50)");
				if ((calEvent.start-(new Date())) < (1000*60*60*12) && !admin) {
					$event.find(".wc-time").css({
						"backgroundColor" : "#999",
						"border" : "1px solid #888"
					});
				}
			} else if (calEvent.typ == 2) {
				$event.css("backgroundColor", "rgb(220, 33, 39)");
				$event.find(".wc-time").css({
					"backgroundColor" : "#999",
					"border" : "1px solid #888"
				});
			} else if (calEvent.typ == 3) {
				$event.css("backgroundColor", "rgb(251, 215, 91)");
				$event.find(".wc-time").css({
					"backgroundColor" : "#999",
					"border" : "1px solid #888"
				});
			}
		},
		draggable : function(calEvent, $event) {
			if (!admin) { return false; }
			if (new Date(calEvent.start)-(new Date()) < (1000*60*60*12) && !admin) {
				return false;
			}
			return calEvent.readOnly != true;
		},
		resizable : function(calEvent, $event) {
			if (!admin) { return false; }
			if (new Date(calEvent.start)-(new Date()) < (1000*60*60*12) && !admin) {
				return false;
			}
			return calEvent.readOnly != true;
		},
		eventNew : function(calEvent, $event) {
			var $dialogContent = $("#event_edit_container");
			resetForm($dialogContent);
			if (calEvent.start.toISOString().substr(0,10) > this.maxDate.toISOString().substr(0,10)
			||  calEvent.start.toISOString().substr(0,10) < this.minDate.toISOString().substr(0,10)
			) {
				alert("Ausserhalb der Saison");
				$calendar.weekCalendar("removeEvent", calEvent.id);
				return false;
			}
			if (new Date(calEvent.start)-(new Date()) < (-1000*60*60*48) && !admin) {
				alert("Buchungen können nur bis 48 Stunden in die Vergangeneit gemacht werden. Falsche Woche?")
				alert("Buchungen können nur bis 48 Stunden in die Vergangeneit gemacht werden. Falsche Woche?")
				$calendar.weekCalendar("removeEvent", calEvent.id);
				return;
			}
			if (new Date(calEvent.end)-(new Date(calEvent.start)) > (1000*60*60) && !admin) {
				alert("Buchungen können nur für 1 Stunde gemacht werden. Oder Admin fragen.")
				$calendar.weekCalendar("removeEvent", calEvent.id);
				return;
			}
			var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
			var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
			var firstnameField = $dialogContent.find("input[name='firstname']");
			firstnameField.val($vorname);
			var lastnameField = $dialogContent.find("input[name='lastname']");
			lastnameField.val($nachname);
			var telnumberField = $dialogContent.find("input[name='telnumber']");
			telnumberField.val($marke);
			//var titleField = $dialogContent.find("input[name='title']");
			var bodyField = $dialogContent.find("textarea[name='body']");
			var typField = $dialogContent.find("select[name='typ']").val(calEvent.typ);
			typField.val($typ);
			
			$currentEvent = $event;

			$dialogContent.dialog({
				modal: true,
				width: 520,
				title: "Neue Buchung",
				close: function() {
					$dialogContent.dialog("destroy");
					$dialogContent.hide();
					$('#calendar').weekCalendar("removeUnsavedEvents");
				},
				buttons: {
					"Buchen" : function() {
						if (((jQuery.trim(firstnameField.val()).length > 0) && (jQuery.trim(lastnameField.val()).length > 0) 
								&& (jQuery.trim(telnumberField.val()).length > 0 || typField.val() > 0)) || (admin)) {
							calEvent.id = id;
							id++;
							calEvent.start = new Date(startField.val());
							calEvent.end = new Date(endField.val());
							//calEvent.title = titleField.val();
							calEvent.firstname = firstnameField.val();
							calEvent.lastname = lastnameField.val();
							calEvent.telnumber = jQuery.trim(telnumberField.val());
							calEvent.body = bodyField.val();
							calEvent.typ = (isNumber(typField.val())) ? typField.val() : 0;

							if (calEvent.telnumber.indexOf("O") >= 0) {
								alert("Sie haben im Code den Großbuchstaben O eingegeben. Das muss wahrscheinlich die Ziffer 0 sein.");
								telnumberField.focus();
								return;
							}

							if (calEvent.telnumber.indexOf("I") >= 0) {
								alert("Sie haben im Code den Großbuchstaben I eingegeben. Das muss wahrscheinlich der Kleinbuchstabe L sein.");
								telnumberField.focus();
								return;
							}

							if (jQuery.trim(telnumberField.val()).length > 6) {
								alert("Sie haben im Code mehr als 6 Zeichen eingegeben.");
								telnumberField.focus();
								return;
							}

							if (calEvent.telnumber.indexOf(" ") >= 0) {
								alert("Sie haben im Code Leerzeichen eingegeben. Ein Code besteht nur aus Kleinbuchstaben und Ziffern.");
								telnumberField.focus();
								return;
							}

							if (calEvent.telnumber.indexOf("-") >= 0) {
								alert("Sie haben im Code - eingegeben. Ein Code besteht nur aus Kleinbuchstaben und Ziffern.");
								telnumberField.focus();
								return;
							}

							if ((jQuery.trim(telnumberField.val()).length > 0) && (jQuery.trim(telnumberField.val()).length < 6)) {
								alert("Sie haben im Code weniger als 6 Zeichen eingegeben.");
								telnumberField.focus();
								return;
							}

							// post to server
							var action = "save";
							if (calEvent.typ > 3) {
								action = "savefree";
							}else if (calEvent.typ > 1) {
								action = "savefixed";
							}
							$.post("cal.php?action="+action+"&id=0&start="+(calEvent.start.getTime()/1000-tz_offset)+"&end="+(calEvent.end.getTime()/1000-tz_offset)+"&firstname="+calEvent.firstname+"&lastname="+calEvent.lastname+"&telnumber="+calEvent.telnumber+"&body="+calEvent.body+"&typ="+calEvent.typ+"&uid="+calEvent.userId, function(data) {
								if (isNumber(data)) {
									calEvent.id = data;
								} else if (data.substr(0, 7) == "Achtung") {
									calEvent.id = data.substr(data.lastIndexOf("\n")+1, 10);
									alert(data.substr(0, data.lastIndexOf("\n")));
								} else {
									$calendar.weekCalendar("removeEvent", calEvent.id);
									alert(data);
								}
							});

							$calendar.weekCalendar("removeUnsavedEvents");
							$calendar.weekCalendar("updateEvent", calEvent);
							$dialogContent.dialog("close");
						} else {
							if ((jQuery.trim(firstnameField.val()).length == 0)){
								alert("Bitte Vorname eingeben.");
								firstnameField.focus();
							}else if ((jQuery.trim(lastnameField.val()).length == 0)){
								alert("Bitte Nachname eingeben.");
								lastnameField.focus();
							}else if ((jQuery.trim(telnumberField.val()).length == 0)){
								alert("Bitte Code eingeben.");
								telnumberField.focus();
							}
						}
					},
					"Abbrechen" : function() {
						$dialogContent.dialog("close");
					}
				}
			}).show();

			$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start)+" Platz "+(calEvent.userId+1));
			setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
			bodyField.val(prices[""+(calEvent.start.getDay()*100+calEvent.start.getHours())]+" EUR");
			
			startField.attr('disabled', 'disabled');
			if (!admin) {
				endField.attr('disabled', 'disabled');
			}
			firstnameField.focus();
			firstnameField.trigger("change");

		},
		eventDrop : function(calEvent, $event) {
			if (admin) {
				// post to server
				$.post("cal.php?action=save&id="+calEvent.id+"&start="+calEvent.start.getTime()/1000+"&end="+calEvent.end.getTime()/1000+"&firstname="+calEvent.firstname+"&lastname="+calEvent.lastname+"&telnumber="+calEvent.telnumber+"&body="+calEvent.body+"&typ="+calEvent.typ+"&uid="+calEvent.userId, function (data) {
					if (isNumber(data)) {
						calEvent.id = data;
					} else {
						alert(data);
						refreshCalEvents();
					}
				});
			} else {
				alert("nur Admins dürfen verschieben");
				refreshCalEvents();
			}
		},
		eventResize : function(calEvent, $event) {
			if (admin) {
				// post to server
				$.post("cal.php?action=save&id="+calEvent.id+"&start="+calEvent.start.getTime()/1000+"&end="+calEvent.end.getTime()/1000+"&firstname="+calEvent.firstname+"&lastname="+calEvent.lastname+"&telnumber="+calEvent.telnumber+"&body="+calEvent.body+"&typ="+calEvent.typ+"&uid="+calEvent.userId, function(data) {
					if (isNumber(data)) {
						calEvent.id = data;
					} else {
						alert(data);
						refreshCalEvents();
					}
				});
			} else {
				alert("nur Admins dürfen verschieben");
				refreshCalEvents();
			}
		},
		eventClick : function(calEvent, $event) {

			if (calEvent.readOnly && !admin) {
				return;
			}
			
			if (new Date(calEvent.start)-(new Date()) < (-1000*60*60*24*7) && !admin) {
				alert("Buchungen können nur bis 7 Tage nach Beginn angesehen werden.")
				return;
			}

			if (admin) {
				var $dialogContent = $("#event_edit_container");
			} else {
				var $dialogContent = $("#event_edit_container2");
			}			
			resetForm($dialogContent);
			telnumberField = $dialogContent.find("input[name='telnumber']").val(calEvent.telnumber);
			var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
			var endField = $dialogContent.find("select[name='end']");
//			endField.val(calEvent.end);
			firstnameField = $dialogContent.find("input[name='firstname']").val(calEvent.firstname);
			
			lastnameField = $dialogContent.find("input[name='lastname']").val(calEvent.lastname);
				
			var bodyField = $dialogContent.find("textarea[name='body']");
			bodyField.val(calEvent.body);
			var typField = $dialogContent.find("select[name='typ']").val(calEvent.typ);
			
			$currentEvent = $event; 

			$dialogContent.dialog({
				modal: true,
				width: 520,
				title: "Buchung - " + calEvent.lastname + ", " + calEvent.firstname + " - Platz " + (parseInt(calEvent.userId) + 1),
				close: function() {
				   $dialogContent.dialog("destroy");
				   $dialogContent.hide();
				   $('#calendar').weekCalendar("removeUnsavedEvents");
				},
				buttons: {
				   "Speichern" : function() {

						if (admin) {
							calEvent.start = new Date(startField.val());
							calEvent.end = new Date(endField.val());
							//calEvent.title = titleField.val();
							calEvent.firstname = firstnameField.val();
							calEvent.lastname = lastnameField.val();
							//calEvent.telnumber = telnumberField.val();
							calEvent.body = bodyField.val();
							calEvent.typ = typField.val();

							// post to server
							var action = "save";
							if (calEvent.typ > 1) {
								action = "savefixed";
							}
							$.post("cal.php?action="+action+"&id="+calEvent.id+"&start="+calEvent.start.getTime()/1000+"&end="+calEvent.end.getTime()/1000+"&firstname="+calEvent.firstname+"&lastname="+calEvent.lastname+"&body="+calEvent.body+"&typ="+calEvent.typ+"&uid="+calEvent.userId, function(data) {
								if (data.substr(0, 7) == "Achtung") {
									alert(data);
								}
							});

							$calendar.weekCalendar("updateEvent", calEvent);
							$dialogContent.dialog("close");
						} else {
							alert("Nur Admins können ändern.");
						}
				   },

				   "Löschen" : function() {
						if (new Date(calEvent.start)-(new Date()) < (1000*60*60*12) && !admin) {
							alert("Buchungen können nur bis 12 Stunden vor Beginn gelöscht werden.")
							return;
						}

						// post to server
						$.post("cal.php?action=delete&id="+calEvent.id+"&telnumber="+telnumberField.val()+"&typ="+calEvent.typ, function(data) {
                            if (isNumber(data) && data == 1) {
                                $calendar.weekCalendar("removeEvent", calEvent.id);
                                $dialogContent.dialog("close");
                            } else {
                                alert(data);
                            }
                        });
						
				   },
				   "Abbrechen" : function() {
						$dialogContent.dialog("close");
				   }
				}
			}).show();

			startField = $dialogContent.find("select[name='start']").val(calEvent.start);
//			endField.val(calEvent.end);
			$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start));
			setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
			//$(window).resize().resize(); //fixes a bug in modal overlay size ??
			
			startField.attr('disabled', 'disabled');
			if (!admin) {
				endField.attr('disabled', 'disabled');
			}

			firstnameField.focus();
			firstnameField.trigger("change");
		},
		eventMouseover : function(calEvent, $event) {
		},
		eventMouseout : function(calEvent, $event) {
		},
		noEvents : function() {
		},
		data: 'cal.php?action=get_events'
	});
	
	function isNumber(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}

	function resetForm($dialogContent) {
		$dialogContent.find("input").val("");
		$dialogContent.find("textarea").val("");
	}
	
	/*
	* Sets up the start and end time fields in the calendar event
	* form for editing based on the calendar event being edited
	*/
	function setupStartAndEndTimeFields($startTimeField, $endTimeField, calEvent, timeslotTimes) {
		var regDateTime = new RegExp("^[-+ \(\):0-9a-zA-Zä]+$");
		var regTime = new RegExp("^[:0-9]+$");
		$startTimeField.empty();
		$endTimeField.empty();

		for (var i = 0; i < timeslotTimes.length; i++) {
			var startTime = timeslotTimes[i].start;
			var endTime = timeslotTimes[i].end;
			var startFormatted = timeslotTimes[i].startFormatted;
			var endFormatted = timeslotTimes[i].endFormatted;

			if (!regDateTime.test(endTime) ||
				!regDateTime.test(startTime) ||
				!regTime.test(startFormatted)
			) {
				endTime = "xx:xx";		// causes error soon
			}
			if (!regTime.test(endFormatted)) { endFormatted = "xx:xx"; }		// causes error soon

			var startSelected = "";
			if (startTime.getTime() === calEvent.start.getTime()) {
				startSelected = "selected=\"selected\"";
			}
			var endSelected = "";
			if (endTime.getTime() === calEvent.end.getTime()) {
				endSelected = "selected=\"selected\"";
			}
			$startTimeField.append("<option value=\"" + startTime + "\" " + startSelected + ">" + startFormatted + "</option>");
			if (admin || endSelected != "") {
				$endTimeField.append("<option value=\"" + endTime + "\" " + endSelected + ">" + endFormatted + "</option>");
			}

			$timestampsOfOptions.start[timeslotTimes[i].startFormatted] = startTime.getTime();
			$timestampsOfOptions.end[timeslotTimes[i].endFormatted] = endTime.getTime();

		}
//		$endTimeOptions = $endTimeField.find("option");
//		$startTimeField.trigger("change");
//		$endTimeField.trigger("change");
	}

	var $endTimeField = $("select[name='end']");
	var $endTimeOptions = $endTimeField.find("option");
	var $timestampsOfOptions = {start:[],end:[]};
	
	var $currentEvent; 

	//reduces the end time options to be only after the start time options.
	$("select[name='start']").change(function() {
		var startTime = $timestampsOfOptions.start[$(this).find(":selected").text()];
		var currentEndTime = $endTimeField.find("option:selected").val();

		// New code to check next event start time
		var nextEvent = $currentEvent.siblings().sort(function(a, b) {
				return $(a).data("calEvent").start.getTime() - $(b).data("calEvent").start.getTime();
			}).filter(function(i) {
				return $(this).data("calEvent").start.getTime() >= $currentEvent.data("calEvent").start.getTime();
		}).data("calEvent");

		var newEndOptions = $endTimeOptions.filter(function() {
			return startTime < $timestampsOfOptions.end[$(this).text()] && (nextEvent == undefined ? true : nextEvent.start.toString() >= $(this).val());
		})
		$endTimeField.html( newEndOptions );

		/* Old code
		$endTimeField.html(
			$endTimeOptions.filter(function() {
			   return startTime < $timestampsOfOptions.end[$(this).text()];
			})
			);*/

		var endTimeSelected = false;
		$endTimeField.find("option").each(function() {
			if ($(this).val() === currentEndTime) {
				$(this).attr("selected", "selected");
				endTimeSelected = true;
				return false;
			}
            return false;
		});

		if (!endTimeSelected) {
			//automatically select an end date 2 slots away.
//			$endTimeField.find("option:eq(1)").attr("selected", "selected");
		}

	});
	
	// Name is mandatory
	$("input[name='lastname']").change(function() {
		if (jQuery.trim($(this).val()).length > 0) {
			$(this).css("background-color","");
		} else {
			$(this).css("background-color","#FBEFEF"); // rgb(220, 33, 39)
		}
	});
	$("input[name='lastname']").keyup(function() {
		$(this).trigger("change");
	});
	
	
	$("#logout_button").click(function() {
		$.post("cal.php?action=logout", function() {
			location.reload(true);
		});
	});

	var $about = $("#about");

	$("#about_button").click(function() {
		window.location.href = "http://www.tennisklub-langen.de/traglufthalle/";
		/*$about.dialog({
			title: "TKL Tennis",
			width: 600,
			close: function() {
				$about.dialog("destroy");
				$about.hide();
			},
			buttons: {
				"Schließen" : function() {
					$about.dialog("close");
				}
			}
		}).show();
		*/
	});
	
	function refreshCalEvents() {
		$calendar.weekCalendar("refresh");
	}
	window.setInterval(refreshCalEvents, (1000*60*10));

});
