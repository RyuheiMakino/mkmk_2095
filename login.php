<?php
require('./dbconnect.php');

error_reporting(E_ALL & ~E_NOTICE);

session_start();

if ($_SESSION['email'] != '') {
	$_POST['email'] = $_SESSION['email'];
	$_POST['password'] = $_SESSION['password'];
	$_POST['save'] = 'on';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// ログインの処理
	if ($_POST['email'] != '' && $_POST['password'] != '') {
		$sql = sprintf("SELECT * FROM members WHERE email='%s' AND password='%s'",
			pg_escape_string($db, $_POST['email']),
			pg_escape_string($db, sha1($_POST['password']))
		);
		$record = pg_query($db, $sql) or die(pg_error($db));
		if ($table = pg_fetch_assoc($record)) {
				// ログイン成功
				$_SESSION["user"]['id'] = $table['id'];
				$_SESSION["user"]['time'] = time();
				$_SESSION["user"]["picture"] = $table["picture"];
				$_SESSION["user"]["name"] = $table["name"];
				$_SESSION["user"]["link"] = "";
				$_SESSION["user"]["data"] = $table;
				$_SESSION["user"]["account_type"] = "inside";
			// ログイン情報を記録する
			if ($_POST['save'] == 'on') {
				//setcookie('email', $_POST['email'], time()+60*60*24*14);
				//setcookie('password', $_POST['password'],
				//time()+60*60*24*14);

				$_SESSION["email"] = $_POST['email'];
				$_SESSION["password"] = $_POST['password'];
			}

			header('Location: /home/');
			exit();
		} else {
			$error['login'] = 'failed';
		}
	} else {
		$error['login'] = 'blank';
	}
}
?>

<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ログイン</title>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css">
	<style>
		body {
			font-family: Arial, Helvetica, sans-serif;
		}

		table {
			font-size: 1em;
		}

		.ui-draggable, .ui-droppable {
			background-position: top;
		}
		label, input { display:block; }
		input.text { margin-bottom:12px; width:95%; padding: .4em; }
		fieldset { padding:0; border:0; margin-top:25px; }
		h1 { font-size: 1.2em; margin: .6em 0; }
		.ui-dialog .ui-state-error { padding: .3em; }
		.validateTips { border: 1px solid transparent; padding: 0.3em; }
		#login_header {
			padding-bottom: 10px;
		}
		#login_other {
			text-align: center;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script>
	$( function() {
		var dialog, form,

			email = $( "#email" ),
			password = $( "#password" ),
			allFields = $( [] ).add( email ).add( password ),
			tips = $( ".validateTips" );

		function updateTips( t ) {
			tips
				.html( t )
				.addClass( "ui-state-highlight" );
			setTimeout(function() {
				tips.removeClass( "ui-state-highlight", 1500 );
			}, 500 );
		}

		function checkLength( o, n, min, max ) {
			if ( o.val().length > max || o.val().length < min ) {
				o.addClass( "ui-state-error" );
				updateTips( n + "の長さは" +
					min + "～" + max + "文字にしてください。" );
				return false;
			} else {
				return true;
			}
		}

		function addUser() {
			var valid = true;
			allFields.removeClass( "ui-state-error" );

			valid = valid && checkLength( email, "メールアドレス", 6, 80 );
			valid = valid && checkLength( password, "パスワード", 4, 32 );

			if ( valid ) {
				dialog.dialog( "close" );
			}
			return valid;
		}

		function loginConfirm(){
			if(addUser()){
				$("form").submit();
			}
		}

		dialog = $( "#dialog-form" ).dialog({
			autoOpen: true,
			height: 620,
			width: 480,
			modal: false,
			title: "ログイン",
			buttons: {
				"ログイン": loginConfirm
			},
			open:function(event, ui){ $(".ui-dialog-titlebar-close").hide();}
		});

		form = dialog.find( "form" ).on( "submit", function( event ) {
			if(!addUser()){
				event.preventDefault();
			}
		});
		
		$("#login_other a").button();
		<?php if ($error['login'] == 'blank'): ?>
		updateTips( "メールアドレスとパスワードをご記入ください。" );
		<?php endif; ?>
		<?php if ($error['login'] == 'failed'): ?>
		updateTips( "ログインに失敗しました。正しくご記入ください。" );
		<?php endif; ?>
	} );
	</script>
</head>
<body>

<div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons ui-draggable ui-resizable" aria-describedby="dialog-form" aria-labelledby="ui-id-1" style="position: absolute; height: auto; width: 350px; top: 467.5px; left: 1101px; display: none; z-index: 101;"><div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle"><span id="ui-id-1" class="ui-dialog-title">Create new user</span><button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close"><span class="ui-button-icon ui-icon ui-icon-closethick"></span><span class="ui-button-icon-space"> </span>Close</button></div><div id="dialog-form" class="ui-dialog-content ui-widget-content" style="width: auto; min-height: 0px; max-height: none; height: 268px;">
	  <div class="login_header">
		<p>
         卒業研究のため本システムの利用者を募集しております。<br>
         登録された個人情報は、研究目的以外の使用は致しません。<br>
         責任をもって取り扱います。<br>
         初めての方は利用者登録にご協力、よろしくお願いします。
		</p>
		<span>利用者登録がまだの方はこちらからどうぞ。</span>
		<br>
		<span><a href="signup.php">利用者登録する</a></span>
	  </div>
	<div id="login_other">
	  <span>外部アカウントでログイン</span><br>
	　　<a href="/login_other.php?type=google" class="text ui-widget-content ui-corner-all">Google</a>
	　　<a href="/login_other.php?type=twitter" class="text ui-widget-content ui-corner-all">Twitter</a>
　　  </div>
	<p class="validateTips"></p>

	<form action="login.php" method="POST">
		<fieldset>
			<label for="email">メールアドレス</label>
			<input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all">
			<label for="password">パスワード</label>
			<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all">
			<!-- Allow form submission with keyboard without duplicating the dialog button -->
			<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
		</fieldset>
	</form>
</div><div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset"><button type="button" class="ui-button ui-corner-all ui-widget">Create an account</button><button type="button" class="ui-button ui-corner-all ui-widget">Cancel</button></div></div><div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div><div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div></div></body></html>
