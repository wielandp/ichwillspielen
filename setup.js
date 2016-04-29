$(document).ready(function() {

	// Setup
	var $setup = $("#setup");
	var adminField = $setup.find("input[name=\'admin\']");
	var adminpwdField = $setup.find("input[name=\'adminpwd\']");
	var apptitleField = $setup.find("input[name=\'apptitle\']");
	var startdaytimeField = $setup.find("input[name=\'startdaytime\']");
	var enddaytimeField = $setup.find("input[name=\'enddaytime\']");
	var mindateField = $setup.find("input[name=\'mindate\']");
	var maxdateField = $setup.find("input[name=\'maxdate\']");
	$setup.hide();
	
	$("#setup_button").click(function() {
		$setup.dialog({
			title: "Setup",
			modal: true,
			closeOnEscape: false,
			close: function() {
				$setup.dialog("destroy");
				$setup.hide();
			},
			open: function() {
				$.getJSON("cal.php?action=getsetup", function(data) {
					$.each(data, function(index, val) {
						if (index != "adminpwd") {
							var thisField = $setup.find("input[name=\'"+index+"\']");
							thisField.val(val);
						}
					});
				});
			},
			buttons: {
				"Speichern" : function() {
                    var str = '';
                    $("#setup :input").each(function() {
                        var input = $(this);
                        if (typeof input.attr("name") != "undefined" && input.val() != "")
                            str += "&" + input.attr("name")+ "=" + input.val();
                    });
					// post to server
					$.post("cal.php?action=setsetup"+str, function(data) {
						if (data == 1) {
							$setup.dialog("close");
                            location.reload(true);
						} else {
							alert("Speichern nicht erfolgreich");
						}
					});
				},
				"Abbrechen" : function() {
					$setup.dialog("close");
				}
			}
		}).show();
	});
});