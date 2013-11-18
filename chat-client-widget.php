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
	wp_enqueue_script( 'airshp_chat_socket.io', 'http://tourgigs.com:8080/socket.io/socket.io.js', array( 'jquery' ), date('Y-m-d').'asdfasd', true );
/* 	Serve JS page with Admin controls to only users with permissions */
	if ( (current_user_can( 'manage_options' ))) {
		wp_enqueue_script( 'airshp_chat_client', AIRSHPCHAT_URL.'clientAdmin.js', array( 'jquery','childscripts' ), date('Y-m-d'), true );
	} else {
		wp_enqueue_script( 'airshp_chat_client', AIRSHPCHAT_URL.'client.js', array( 'jquery','childscripts' ), date('Y-m-d'), true );
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
		$slug = get_post( $post )->post_name;

		//Our variables from the widget settings.
		$title = '';
		//$username = $current_user->user_login;
		if(is_user_logged_in()):
			$current_user = wp_get_current_user();
		endif;
		$username = (isset($current_user->user_login) ? $current_user->user_login : '' );
		
		$show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;
		//$roomURL = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$roomURL = $slug.'_2';
		$client_ip = $_SERVER['REMOTE_ADDR'];
		
		echo $before_widget;

		// Display the widget title 
		echo $before_title . $title . $after_title;

		//Display the name 
		echo "
		<div id = 'airshp-chat'>
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
		";
		
		if ( $show_info )
			printf( $name );

		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['name'] = strip_tags( $new_instance['name'] );
		$instance['show_info'] = $new_instance['show_info'];

		return $instance;
	}

	
	function form( $instance ) {

	
	}
}

?>