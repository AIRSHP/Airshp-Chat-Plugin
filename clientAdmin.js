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
			$('#chatroom').append('<div class="chat_entry nouser"><b>the chat server is currently offline</b></div>');
			
		//else socket.io is loaded and we can chat
		}else{
			var messages = [];
			//var socket = io.connect('http://tourgigs.com:8080'); 
			var socket = io.connect('http://beta.tourgigs.com:8080'); 
			//var socket = io.connect('http://airshp-chat_server.nodejitsu.com:80');

			var field = document.getElementById("field");
			var sendButton = document.getElementById("send");
			var chatroom = document.getElementById("chatroom");
			var name = document.getElementById("name");
			var roomID = document.getElementById("roomID");
			var clearButton = document.getElementById("clear");
			var clientIP = document.getElementById("client_ip");
			
			
			socket.on('connect', function (data) {
				socket.emit('join room', roomID.value);
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
						} else if (!messages[i].username){
							bycurrentuserclass = 'nouser';
						}
						html += '<div class="chat_entry ' + bycurrentuserclass + '" id= "'+ messages[i]._id + '">';
						html += '<b>' + escapeHtml(messages[i].username ? messages[i].username : 'Server') + ': </b>';
						html += '<span class="message">'+ replaceURLWithHTMLLinks(escapeHtml(messages[i].message).replace('\n','<br/>')) + '</span>';
						html +=	'<div id = "options"><input class = "banUser" type = "button" value = "Ban User" onclick = "banUser(this);return false;"/><input class = "unbanUser" type = "button" value = "Un-Ban User" onclick = "unbanUser(this);return false;"/><input class = "delete" type = "button" value = "Delete MSG" onclick = "deleteMessage(this); return false;" /></div></div>';
					}
					chatroom.innerHTML = html;
					chatroom.scrollTop = chatroom.scrollHeight;
				} else {
					console.log('There is a problem:', data);
				}
				$('body').trigger('chat_update');
			});
			
			socket.on('clearChat', function () {
				messages = [];
				chatroom.innerHTML = '';
			});
		
			$('#namearea').hide();
			var setName = function(){
				$('#namearea').show().find('input').focus();
				//console.log('showing name field');
				$('#name').keyup(function(e) {
					var code = (e.keyCode ? e.keyCode : e.which);
					if(code == 13) {
						$('#field').attr('placeholder',$(this).val()+'...').focus();
						$('#namearea').hide();
						sendMessage();
					};
				});
			};
			
			
			
			sendButton.onclick = sendMessage = function() {
				if(name.value == '') {
					setName();
				} else if(field.value.trim() != ''){
					socket.emit('send', { message: field.value, username: name.value, client_ip: clientIP.value});
					field.value = '';
				}else{
					field.value = '';
					return false;
				}
			};
			
			clearButton.onclick = function() {
				socket.emit('clear');
			};
			
			deleteMessage = function(selectedMessage) {
				var messageID = $(selectedMessage).closest('.chat_entry').attr('id');
				socket.emit('delete', messageID);	
			};
			
			$('#enable_chat').hide();
			enableChat = function() {
				//html = '';
				//messages = [];
				//chatroom.innerHTML = html;
				socket.socket.connect() 
				$("#airshp-chat").addClass('enabled');
				$("#airshp-chat").removeClass('disabled');
				
				$('#disable_chat').show();
				$('#enable_chat').hide();
				$("#airshp-chat #send, #airshp-chat #field").removeAttr('disabled');
			}
			
			disableChat = function() {
				html = '';
				messages = [];
				chatroom.innerHTML = html;
				socket.emit("leaveChat");
				$("#airshp-chat").addClass('disabled');
				$("#airshp-chat").removeClass('enabled');
				
				$('#enable_chat').show().css('display','inline-block');
				$('#disable_chat').hide();
				$('#namearea').hide();
				$("#airshp-chat #send, #airshp-chat #field").attr('disabled','disabled');
			}
			
			banUser = function(selectedMessage) {
				var messageID = $(selectedMessage).parent().parent().attr('id');
				socket.emit('banUser', messageID);	
			};
			unbanUser = function(selectedMessage) {
				var messageID = $(selectedMessage).parent().parent().attr('id');
				socket.emit('unbanUser', messageID);	
			};
			openWindow = function(url){
				var strWindowFeatures = "menubar=no,location=no,resizable=0,scrollbars=no,status=yes,width=325,height=378";
				window.open(url + '&name=' + name.value, "CHAT", strWindowFeatures);
				disableChat();
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
	
	//toolbar
	$('#airshp-chat .chat_tools').each(function(){
		var tools = $(this);
		$(this).addClass('closed');
		$(this).find('.toggle a').click(function(){
			tools.toggleClass('closed');
			return false;
		});
	});
	$('#airshp-chat .chat_disabled a').click(function(){
		enableChat();
		return false;
	});
	
});		
			
			
	
