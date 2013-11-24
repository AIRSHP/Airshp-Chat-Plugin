<?php
/*
Description: Pop Up Feature for Chat
Author: Zachary Earle
*/
require( '../../../wp-load.php' );
$AIRSHPCHAT_URL = "http://".$_SERVER['HTTP_HOST']."/wp-content/plugins/Airshp-Chat/";
$stylesheet_URL = get_stylesheet_directory_uri().'/css/style.css';
/* 	Serve JS page with Admin controls to only users with permissions */
	if ( (current_user_can( 'manage_options' ))) {
		$script_url = $AIRSHPCHAT_URL.'clientAdmin.js';
	} else {
		$script_url = $AIRSHPCHAT_URL.'client.js';
	}
		//$username = $current_user->user_login;
		if(is_user_logged_in()):
			$current_user = wp_get_current_user();
		endif;
		$username = (isset($current_user->user_login) ? $current_user->user_login : '' );
		
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;
		//$roomURL = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$roomURL = $_GET['chatroom'];
		$client_ip = $_SERVER['REMOTE_ADDR'];
		
		echo $before_widget;

		// Display the widget title 
		echo $before_title . $title . $after_title;

		//Display the name 
		echo "
		<html>
			<head>
			<title>
				Chat
			</title>
			<script src='http://tourgigs.com:8080/socket.io/socket.io.js'></script>
			<script src='http://code.jquery.com/jquery-latest.min.js'></script>
			<script src='$script_url'></script>		
			<link rel='stylesheet' type='text/css' href = '$stylesheet_URL' />
			</head>
			<body class = 'popupWindow'>
					<div class = 'section discuss'>
		<div class = 'sectionContent'>
		<div class = 'tab-content'>
		<div class = 'tab-pane active' id = 'discuss-chat'>
		<div id = 'airshp-chat'>
		
				<input id = 'disable_chat' type = 'button' value = 'DISABLE CHAT' onclick = 'disableChat()' />
				<input id = 'enable_chat' type = 'button' value = 'ENABLE CHAT' onclick = 'enableChat()' />
			<div class='chat_pane'>
				<div id='chatroom'></div>
			</div>
			<div class='controls'>
				<input id = 'roomID' type = 'hidden' value = $roomURL></input>
				<input id = 'client_ip' type = 'hidden' value = $client_ip></input>
				<div id='namearea' style='display:none'>
					<div>
						<p>Set your chat name</p>
						<input id ='name' type = 'text' value='$username' placeholder='your name' />
						<p><em>press the 'Enter' key to save</em></p>
					</div>
				</div>
				<textarea autocomplete='off' class='message-input' cols='40' id='field' name='field' placeholder='Add a comment&hellip;' rows='1'></textarea>
				<input id='send' type='button' value='POST' />
				";
		if ( (current_user_can( 'manage_options' ))) {
			echo "<input id='clear' type='button' value='CLEAR CHAT' />";
		}
		
		echo"
			</div>		
		</div>
		</div>
		</div>
		</div>
		</div>

			</body>
		</html>
		";
?>