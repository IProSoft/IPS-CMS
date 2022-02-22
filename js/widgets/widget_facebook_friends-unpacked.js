$(document).ready(function(){

IPSDIV = '#IPS-app ';

(function($) {

	$.fn.FBIPS = function(method) {

		var methods = {

			init : function(options) {
				
				this.FBIPS.config = $.extend({}, this.FBIPS.defaults, options);
				FB.getLoginStatus(function(response) {
				/* FB.Event.subscribe('auth.statusChange', function(response) { */
					if( response.status == "connected" )
					{
						FB.api('/me', function(response) {
							$.fn.FBIPS.config.user = response;
							$.fn.FBIPS('connectedUserActions');
							actionReadArticle();
						});						
					}
					else
					{
						console.log(response);
					}
				});
				
			},
			checkPublishPrivileges : function( ) {
				
				if( this.is(':checked') )
				{
					$.cookie('activityPublish', 'true', { path: '/' });
					this.FBIPS( 'publishPrivileges', (1), ('Włączone') );
				}
				else
				{
					$.cookie('activityPublish', 'false', { path: '/' });
					this.FBIPS( 'publishPrivileges', (0.5), ('Wyłączone') );
				}
			},
			connectedUserActions: function (){
				
				$("#user-friends .preload").fadeIn();
				
				$("#not-logged-user").fadeOut('fast', function(){
					$("#logged-user").fadeIn('fast', function(){
						if( $.cookie('activityPublish') == 'false' )
						{
							$("#activityPublishCheck").attr('checked', false);
						}
						$("#activityPublishCheck").FBIPS( 'checkPublishPrivileges' );
					});
				});
						
				
				$( IPSDIV + " #history ul:first" ).FBIPS( 'loadUserActivity' );
				
				$("#user-image").html( userAvatar( $.fn.FBIPS.config.user.id, $.fn.FBIPS.config.user.name ) );
				$("#actions span.login").html( $.fn.FBIPS.config.user.name );
				
				userAppFriends();
				
				$(document).on("click", IPSDIV + " #user-friends .inviteFriend, " + IPSDIV + " .add-friend", function(){
					$(this).FBIPS( 'appRequestUsers' );
					return false;
				});
			},
			loadUserActivity: function(){
				container = $(this);
				FB.api('/me/news.reads?date_format=d-m-Y%20H:i&limit=50', function(response){
					var crt;
					var content = [];
					for(i in response.data){
						crt = response.data[i];
						content.push( '<li id="act-' + crt.id + '" class="activity-title"><ul><li class="title">'+ crt.data.article.title + '</li><li class="actions"><a href="#" class="user-activity-delete" data-object-id="'+ crt.id +'">usuń</a></li></ul></li>');
					}
					container.append(content.join('\n'));
				});
				
			},
			publishPrivileges: function( op, txt ){
				$( IPSDIV + " #user-friends").animate({opacity: op});
				$( IPSDIV + " #actions label").text( txt );
				if( op != 1 ){
					$( IPSDIV + " #activity-off").fadeIn();
				}else{
					$( IPSDIV + " #activity-off").fadeOut();
				}
				
			},
			appendUserActivityLinks: function( userData ){
				var content = [];
			
				if( userData.data.length > 0 ){
					for(i in userData.data)
					{
						content.push( "<li id='"+ userData.uid +"'>Materiał <a href='"+ userData.data[i].data.article.url + "' alt='"+ userData.data[i].data.article.title +"'>" + userData.data[i].data.article.title +"</a></li>" );
					}
				}
				else
				{
				  content.push("Twój znajomy nie przeglądał do tej pory żadnych materiałów");
				}
				$(this).append('<div class="friend" id="friend-'+userData.uid+'-more" style="display: none;"><h3>'+ userData.name +'</h3><ul>' + content.join('\n') + '</ul></div>');
			},
			appRequestUsers: function(){
					FB.ui({
						method: 'apprequests',
						dispay: 'popup',
						message: 'Zaproszenie do aplikacji Share',
						filters: ['app_non_users']
					}, function(response){});
					return false;
			},
			deleteActivity : function(){
				IDActivity = $(this).attr('data-object-id');
				FB.api('/' + IDActivity, 'delete', function(response){
					if(response){
						$( IPSDIV + " #history ul li#act-" + IDActivity ).fadeOut('slow');
					}
					console.log(response);
				});
				return false;
			}

		};
		settings = $.fn.FBIPS.config;
		var helpers = {
			private_method: function() {}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error( 'Method "' +  method + '" does not exist in FBIPS plugin!');
		};

	};

	$.fn.FBIPS.defaults = {
		activityPublish: 'true',
		user: false
	};
	
	$.fn.FBIPS.config = {};
	
})(jQuery);
	

FBINIT = $(document).FBIPS();
	
	$(document).on("change, click", "#activityPublishCheck", function(){
		$(this).FBIPS('checkPublishPrivileges');
	});
	
	$(document).on("click", ".pubHistory", function(e){
		e.preventDefault();
		$( IPSDIV + " #history").toggle()
	});
	
	$(document).on("mouseenter", IPSDIV + " #user-friends ul.user-friends-list li", function(){
		if( $.cookie('activityPublish') != 'false' ){
			$( IPSDIV + ' #user-friends #friends-activity .friend').fadeOut('fast');
			$("#" + $(this).attr('id') + "-more").css('left', $(this).position().left).fadeIn('fast');
		}
	});
	$(document).on("mouseleave", IPSDIV + " #user-friends ul.user-friends-list li", function(){
		if( $.cookie('activityPublish') != 'false'  && $("#" + $(this).attr('id') + "-more:hover").lenght == 0 ){
			$("#"+$(this).attr('id')+"-more").fadeOut('fast');
		} 
	}) ;
	$(document).on("mouseleave", IPSDIV + " #user-friends #friends-activity .friend", function(){
		$(this).fadeOut(); 
	}) ;
	$(document).on("click", IPSDIV + " #history a.user-activity-delete", function(){
			$(this).FBIPS( 'deleteActivity' );	
		return false;
	});
  
  
	maxVisibleFriends = 9;
  
	function userAppFriends(){
		var friends = [];
		var appUsersFriends = [];
		FB.api(
		  {
			method: 'fql.query',
			query: "SELECT is_app_user,name,uid FROM user WHERE uid in( SELECT uid1 FROM friend WHERE uid2=me() ) AND is_app_user='true'"
		  },
		  function( friends ) {
				for( i in friends ){
					appUsersFriends.push( friends[i] );
					if( i > maxVisibleFriends ){
						break;
					}
				}
				userAppFriendsTimeline(appUsersFriends);
				$( IPSDIV + " #user-friends h3").append(" ("+ friends.length +")");
			}
		);
	}
  
	function userAppFriendsTimeline(users){
		var batchQ = [];
		for(i in users){
		  batchQ.push(
			  {
				"method": "GET",
				"relative_url": "/" + users[i].uid + "/news.reads?date_format=d-m-Y%20H:i&limit=60"
			  }
		  );
		}
		FB.api("/", "POST", {
			batch: batchQ
		}, function( response ) {
			var userFriendsActivity = [];
			for(i in response){
				userFriendsActivity.push(
					$.extend(
						users[i],
						$.parseJSON(response[i].body)
					)
				);
			};
			appendUsersData(userFriendsActivity);
		})
	}
  
  
	function appendUsersData( u ){
		var userData = null;
		var appendUsers = [];
		$("#user-friends .preload").fadeOut(function(){
	  
		  if( u.length > 0){
			for(i in u){
			  if( i < maxVisibleFriends ){
				userData = u[i];
				$( IPSDIV + " #user-friends #friends-activity").FBIPS( 'appendUserActivityLinks', userData );
				appendUsers.push('<li id="friend-' + userData.uid + '">' + userAvatar( userData.uid, userData.name ) + '</li>');	 
			  }
			}
		  }
			
			anonymousCount = maxVisibleFriends - appendUsers.length;

			for( i=0; i < anonymousCount; i++){
				appendUsers.push('<li><img src="http://profile.ak.fbcdn.net/static-ak/rsrc.php/v2/yo/r/UlIqmHJn-SK.gif" alt="Dodaj znajomego" class="user-avatar add-friend"/></li>');			
			}
			
			$(appendUsers.join('\n')).appendTo( IPSDIV + " #user-friends ul.user-friends-list");
			
			$( IPSDIV + " #user-friends ul.user-friends-list li").each( function (index){
				$(this).delay(50 * index).animate({'opacity': 1});
			});

		});
	}

  function userAvatar( uid, name ){
	return '<img src="//graph.facebook.com/' + uid + '/picture" alt="' + name + '" class="user-avatar" />';
  }
	function actionReadArticle(){
		if( ips_config.file_id && $.cookie('activityPublish') == 'true' )
		{
			setTimeout(function(){ 
				FB.api('/me/news.reads','post',{ article: window.location.href },
				function(response) {
					if (!response || response.error) {
						console.log(response);
					} else {
						alert('Akcja została zapisana !');
					}
				});
			}, 10000 );
		}
	}
});