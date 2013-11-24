jQuery(document).ready(function($){

	if($("#airshp-chat").length > 0){
		// From http://stackoverflow.com/a/4835406
		function escapeHtml(unsafe) {
			unsafe += '';
			return unsafe
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		}
		
		//if io is undefined then socket.io didnt load, the server is prolly down
		if(typeof io === 'undefined'){
			//do something to tell user that the chat server is down
			$("#airshp-chat").addClass('serverdown');
			$('#namearea').hide();
			$("#airshp-chat #send, #airshp-chat #field").attr('disabled','disabled');
			$('#chatroom').append('<div class="chat_entry"><b>the chat server is currently offline</b></div>');
			
		//else socket.io is loaded and we can chat
		}else{
			var messages = [];
						
			var socket = io.connect('http://airshp-chat_server.nodejitsu.com:80');
/* 			var socket = io.connect('http://tourgigs.com:8080'); */
			var field = document.getElementById("field");
			var sendButton = document.getElementById("send");
			var chatroom = document.getElementById("chatroom");
			var name = document.getElementById("name");
			var roomID = document.getElementById("roomID");
			var clientIP = document.getElementById("client_ip");
			
			
			socket.on('connect', function (data) {
				socket.emit('join room', roomID.value);
				//maybe put Welcome message here to slightly reduce server load
			});	
			
		
			socket.on('message', function (data) {
				var html = '';
				var bycurrentuserclass = '';
				if(data.message || ($.isArray(data) && data[0])) {
					if (data.message) {
						messages.push(data); 
					} else { 
						for (var i = data.length - 1; i >= 0; i--) {
				    		messages.push(data[i]);
				    	}
					}
					for(var i=0;i<messages.length; i++) {
						var bycurrentuserclass = '';						
						if(messages[i].username && messages[i].username === name.value){
							bycurrentuserclass = 'currentuser';
						}else if(!messages[i].username){
							bycurrentuserclass = 'nouser';
						}
						
						html += '<div class="chat_entry ' + bycurrentuserclass + '">';
						html += '<b>' + escapeHtml(messages[i].username ? messages[i].username : 'Server') + ': </b>';
						html += '<span class="message">'+ replaceURLWithHTMLLinks(escapeHtml(messages[i].message).replace('\n','<br/>')) + '</span>';
						html +=	'</div>';
					}
					chatroom.innerHTML = html;
					chatroom.scrollTop = chatroom.scrollHeight;
				} else {
					console.log('There is a problem:', data);
				}
				$('body').trigger('chat_update');
			});
			
			socket.on('clearChat', function () {
				html = '';
				html += '<div class="chat_entry">';
				html += '<span class="message">Chat cleared by admin!</span>';
				html +=	'</div>';
				messages = [];
				chatroom.innerHTML = html;
			});
			
			socket.on('userBanned', function () {
				html += '<div class="chat_entry">';
				html += '<span class="message">You have been banned by an admin!</span>';
				html +=	'</div>';
				chatroom.innerHTML = html;
			});		

		
			$('#namearea').hide();
			var setName = function(){
				$('#namearea').show();	
				//console.log('showing name field');
				$('#name').keyup(function(e) {
					var code = (e.keyCode ? e.keyCode : e.which);
					if(code == 13) {
						$('#field').attr('placeholder',$(this).val()+'...');
						$('#namearea').hide();
						sendMessage();
					};
				});
			};
			
			$('#messageOptions').hide();
			var showOptions = function() {
				$('#messageOptions').show();
			};
			
			enableChat = function() {
				socket.socket.reconnect() 
				$('#disable_chat').show();
				$('#enable_chat').hide();
				$("#airshp-chat #send, #airshp-chat #field").removeAttr('disabled');
			}
			$('#enable_chat').hide();
			disableChat = function() {
				html = '';
				messages = [];
				chatroom.innerHTML = html;
				socket.emit("leaveChat");
				$('#enable_chat').show();
				$('#disable_chat').hide();
				$('#namearea').hide();
				$("#airshp-chat #send, #airshp-chat #field").attr('disabled','disabled');
			}
			sendButton.onclick = sendMessage = function() {
				if(name.value == '') {
					//alert('Please type your name!');
					setName();
				} else {
					socket.emit('send', { message: field.value, username: name.value, client_ip: clientIP.value});
					field.value = '';
				}
			};
		};//end check for io
	};//end check if the  #airshp-chat is on the page
	
	
	
	function replaceURLWithHTMLLinks(text) {
	    var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|;])/ig; // ; added to final [] to allow escaped chars to be matched
	    return text.replace(exp,"<a href='$1' target='_blank'>$1</a>"); 
	};
});

jQuery(document).ready(function($){
	$('#field').keyup(function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code == 13 && !e.shiftKey) {
			sendMessage();
		};
	});
});		
			
			
	
