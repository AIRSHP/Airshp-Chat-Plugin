<?php
/*
Description: Pop Up Feature for Chat
Author: Zachary Earle
*/
//require( '../../../wp-load.php' );
require_once( dirname( dirname( dirname( dirname( __FILE__ )))) . '/wp-load.php' );

$stylesheet_URL = get_stylesheet_directory_uri().'/css/style.css';
/* 	Serve JS page with Admin controls to only users with permissions */
if ( (current_user_can( 'manage_options' ))) {
	$script_url = AIRSHPCHAT_URL.'clientAdmin.js';
} else {
	$script_url = AIRSHPCHAT_URL.'client.js';
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
$name = $_GET['name'];

echo("
<html class='popup'>
	<head>
	<title>
		Chat
	</title>
	<script src='http://beta.tourgigs.com:8080/socket.io/socket.io.js'></script>
	<script src='http://code.jquery.com/jquery-latest.min.js'></script>
	<script src='".get_stylesheet_directory_uri()."/js/plugins.js'></script>

	<script src='$script_url'></script>		
	<link rel='stylesheet' type='text/css' href = '$stylesheet_URL' />
	</head>
	<body class='popupWindow'>
		<div class='section discuss'>
			<div class='sectionContent'>
				<div class='tab-content'>
					<div class='tab-pane active' id = 'discuss-chat'>
	");
	$widgetargs = array('roomURL' 	=> 	$roomURL, 'name' => $name);
	the_widget('AirshpChat',NULL,$widgetargs);
echo("
	<script>
	jQuery(document).ready(function($){
		//autoscroll the CHAT
		$('div.chat_pane').each(function(){
			var chatwindow = $(this);
			var chatpane = $(this).jScrollPane({
				autoReinitialise: true,
				animateDuration: 50,
				contentWidth: 276
			});
			var api = chatpane.data('jsp');
			//console.log(api);
			chatpane.bind('jsp-initialised',function(event, isScrollable){
					//console.log('Handle jsp-initialised', this,'isScrollable=', isScrollable);
					//api.scrollToBottom(true);
					$('body').trigger('chat_update');
				}
			);
			
			var ishovered = false;
			$(this).hover(function(){
				ishovered = true;
				//console.log('chat is hovered');
			},function(){
				ishovered = false;
				//console.log('chat is unhovered');
			});
			
			$('body').bind('chat_update',function(){
				if(api.getIsScrollableV()){
					api.reinitialise();
					if(ishovered){
						//api.scrollToBottom(false);
						//console.log('should not have scrolled');
					}else{
						api.scrollToBottom(true);
						//console.log('should scroll to bottom');
					}
					
					//console.log('just scrolled to bottom');
				}else{
					//console.log('chat update! but its not scrollable');
				}
			});
			$('body').trigger('chat_update');
		});
	});
	</script>

");

echo("
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
");

?>