$(function() {
//POST
var postFile='login.php';
$(document).mouseup(function() {
	$("#loginform").mouseup(function() {
		return false
	});
	$("a.close").click(function(e){
		e.preventDefault();
		$("#loginform").hide();
		$(".lock").fadeIn();
	});

	if ($("#loginform").is(":hidden"))
	{
		$(".lock").fadeOut();
	} else {
		$(".lock").fadeIn();
	}				
	//$("#loginform").toggle();
});		
$("form#signin").submit(function() {
	//Validate the usuario field if it's blank
	var usuario = $("input#usuario").val();
	if (usuario == "") {
		$('#message').html("<b>Todos los Campos Son Requeridos</b>");
		$("#message").hide().fadeIn(1500);
		$("input#usuario").focus();
		return false;
	}
    			
	//Validate the Password field if it's blank
	var password = $("input#password").val();
	if (password == "") {
		$('#message').html("<b>Todos los Campos Son Requeridos</b>");
		$("#message").hide().fadeIn(1500);
		$("input#password").focus();
		return false;
	}
							
$.post(postFile, { usuarioPost: usuario, passwordPost: password }, function(data) {
	if(data.status==true) {
		var distance = 10;
		var time = 500;
		var myTimer = {};
		$("#loginform").animate({
			marginTop: '-='+ distance +'px',
			opacity: 0
		}, time, 'swing', function () {
			$("#loginform").hide();
		});		
			myTimer = $.timer(1000,function() {
			window.location=data.url;
		});
	} else {
		$("#message").html("<b>Usuario o Contrasena Invalida</b>");
		$("#message").css({color:"red"});
		$("#message").hide().fadeIn(1500);
		$("input#usuario").focus();
	}
}, "json");
return false;
});
$("input#cancel_submit").click(function(e) {
	$("#loginform").hide();
	$(".lock").fadeIn();
});			
});
