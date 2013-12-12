<?php
/*
Plugin Name: Airshp Hosted Chat Widget
Plugin URI: 
Description: NodeJS Chat widget hosted on a NodeJitsu Server
Author: Zachary Earle
Version: 1
Author URI: 
*/
define( 'AIRSHPCHAT_PATH', plugin_dir_path(__FILE__) );
define( 'AIRSHPCHAT_URL', plugin_dir_url(__FILE__) );
function airshp_chat_scripts() {
	//wp_enqueue_script( 'airshp_chat_socket.io', 'http://tourgigs.com:8080/socket.io/socket.io.js', array( 'jquery' ), date('Y-m-d').'12', true );
	wp_enqueue_script( 'airshp_chat_socket.io', 'http://beta.tourgigs.com:8080/socket.io/socket.io.js', array( 'jquery' ), date('Y-m-d').'12', true );
	/* 	Serve JS page with Admin controls to only users with permissions */
	if ( (current_user_can( 'manage_options' ))) {
		wp_enqueue_script( 'airshp_chat_client', AIRSHPCHAT_URL.'clientAdmin.js', array( 'jquery','childscripts' ), date('Y-m-d').'12', true );
	} else {
		wp_enqueue_script( 'airshp_chat_client', AIRSHPCHAT_URL.'client.js', array( 'jquery','childscripts' ), date('Y-m-d').'12', true );
	}
}

add_action( 'widgets_init', 'my_widget' );
function my_widget() {
	register_widget( 'AirshpChat' );
}



class AirshpChat extends WP_Widget {

	function AirshpChat() {
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget that adds a hosted chat feature.', 'example') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'example-widget' );	
		$this->WP_Widget( 'example-widget', __('Airshp Chat', 'example'), $widget_ops, $control_ops );
		add_action( 'wp_enqueue_scripts', 'airshp_chat_scripts' );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		global $post;
		
		if($post):
			$slug = get_post( $post )->post_name;
		elseif($roomURL):
			$slug = $roomURL;
		else:
			return false;
		endif;
		

		//Our variables from the widget settings.
		$title = '';
		//$username = $current_user->user_login;
		if(is_user_logged_in()):
			$current_user = wp_get_current_user();
		endif;
		$name = (isset($name) ? $name : '');
		
		$username = (isset($current_user->user_login) ? $current_user->user_login : $name );
		
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;
		//$roomURL = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		if(!isset($roomURL))
			$roomURL = $slug;
		$client_ip = $_SERVER['REMOTE_ADDR'];
		$popup = AIRSHPCHAT_URL.'popup.php?chatroom='.$roomURL;
		
		echo $before_widget;

		// Display the widget title 
		echo $before_title . $title . $after_title;

		//Display the name 
		echo ("
		<div id = 'airshp-chat' class='".( current_user_can( 'manage_options' ) ? 'chatadmin' : '')."'>
			<div class='chat_tools'>
				<div class='toggle'><a href='#'></a></div>
				<div class='toolbar'>
					<a id = 'disable_chat' href='#' title='DISABLE CHAT' onclick = 'disableChat();return false;'><span class='text'>Disable Chat</span></a>
					<a id = 'enable_chat'  href='#' title='ENABLE CHAT' onclick = 'enableChat();return false;'><span class='text'>Enable Chat</span></a>
					<a id = 'popup_chat'  href='#' title='POP OUT CHAT' onclick = 'openWindow(&quot;$popup&quot;);return false;'><span class='text'>Pop Out Chat</span></a>
					".(current_user_can( 'manage_options' ) ? "<a id='clear' href='#' title='CLEAR CHAT' ><span class='text'>Clear Chat</span></a>" : "" )."
				</div>
			</div>
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
			</div>	
			<div class='chat_disabled'><a href='#'>Enable Chat</a></div>	
		</div>
		");
		
		
		/*
		if ( $show_info )
			printf( $name );
			*/
		
		echo $after_widget;
	}
}

?>