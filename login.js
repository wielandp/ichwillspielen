$(document).ready(function() {

	// Login / Logout
	var $login = $("#login");
	var uidField = $login.find("input[name=\'uid\']");
	var pwdField = $login.find("input[name=\'pwd\']");
	$login.hide();
	
    if (!loggedIn) {
        $login.dialog({
            title: "Login",
            modal: true,
            closeOnEscape: false,
            beforeclose : function() { 
                return loggedIn; 
            },
            close: function() {
                $login.dialog("destroy");
                $login.hide();
            },
            open: function() {
                $(".ui-dialog-titlebar-close", this.parentNode).hide();
                $(this).parents('.ui-dialog-buttonpane button:eq(0)').focus();
                $(this).keypress(function(e) {
                if (e.keyCode == 13) {
                    $(this).parent().find('.ui-dialog-buttonpane button:first').click();
                    return false;
                }
            });
            },
            buttons: {
                "Login" : function() {
                    // post to server
                    $.post("cal.php?action=login&uid="+uidField.val()+"&pwd="+pwdField.val(), function(data) {
                        if (data == 1) {
                            loggedIn = true;
                            $login.dialog("close");
                            location.reload(true);
                        } else {
                            alert("Login nicht erfolgreich");
                        }
                    });
                }
            }
        }).show();
    }
});