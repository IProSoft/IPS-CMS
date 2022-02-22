
(function ($) { 

	$.ips_map = function (id, map_locations) {
       
		this.map_id = document.getElementById( id );
		this.map_locations = map_locations;
		this.timeoutId;

		return this;
    };

    $.ips_map.prototype = {
		InitEvents: function () {

			var instance = this;
			
			if( typeof this.map_id == 'undefined' )
			{
				return console.log('Wrong MAP ID');
			}

			/* Map wrapper without top panel */
			document.getElementById("map-canvas-wrapper").style.height = ( window.innerHeight - 45 ) + 'px';
			
			$.ips_map.map_options.center = this.getCenter();
			
			this.map = new google.maps.Map( this.map_id, $.ips_map.map_options );
			
			this.infowindow = new google.maps.InfoWindow({
				maxWidth: 200
			});

			this.places_service = new google.maps.places.PlacesService( this.map );
			this.service = new google.maps.places.AutocompleteService();
			
			this.user_location = false;
			this.deferred = this.getLocation();
			
			var marker_icons = this.getMarkers();

			if( Object.keys(marker_icons).length > 0 )
			{
				var bounds = new google.maps.LatLngBounds();
			
				

				var locations = this.map_locations.map( function( item, key ){

					var marker = instance.putMarker( item, ( typeof marker_icons[item.pin_id] == 'object' ? marker_icons[item.pin_id].upload_image : false ) );
					
					bounds.extend( marker.getPosition() );
				})
				
				this.map.fitBounds( bounds );
			}
			
			var listener = google.maps.event.addListener( this.map, "idle", function () {
				google.maps.event.removeListener( listener );
			});
			
			this.attachAddPlace();
			/* For data-response handler */
			window['maps_append_image'] = $.proxy( this.appendUploadImage, this )
			window['maps_append_pin'] = $.proxy( this.placePinAdded, this );
        },
		getCenter: function()
		{
			switch( $.ips_map.map_options.default_position )
			{
				case 'poland':
					return new google.maps.LatLng( 51.919438, 19.145136 )
				break;
				case 'germany':
					return new google.maps.LatLng( 37.09024, -95.712891	)
				break;
				case 'france':
					return new google.maps.LatLng( 51.165691, 10.451526 )
				break;
				case 'canada':
					return new google.maps.LatLng( 46.227638, 2.213749 )
				break;
				case 'usa':
				default:
					return new google.maps.LatLng( 37.090240, -95.712891 )
				break;
			}
		},	
        getMarkers: function () {
            var marker_icons = {};
			
			if( typeof ips_items[0].items == 'object' )
			{
				ips_items[0].items.map( function( item, key ){
					marker_icons[item.id] = item;
				})
			}
			
			return marker_icons;
        },
		putMarker : function( item, image )
		{
			var instance = this;
			
			var marker = new CustomMarker(
				new google.maps.LatLng( item.place_latitude, item.place_longitude ),
				this.map,
				'pin_map',
				'map_marker_' + item.pin_id,
				image
			);
			/** Ad info window */
			google.maps.event.addListener( marker, 'click', ( function(marker){
				return function() {
					instance.infowindow.setContent( item.place_name );
					instance.infowindow.open( instance.map, marker );
					instance.map.panTo( marker.getPosition() );
				}
			})(marker));
			
			return marker;
		},
		getLocation : function()
		{
			var deferred = $.Deferred();
			
			if( navigator.geolocation ) {
				
				navigator.geolocation.getCurrentPosition(function( position ) {
					this.user_location = new google.maps.LatLng( position.coords.latitude,position.coords.longitude );
					deferred.resolve();
				}, function() {
					deferred.reject();
				},{
					timeout: 20000,
					enableHighAccuracy: true,
					maximumAge: 10000
				});
			}
			else
			{
				deferred.reject();
			}
			
			return deferred;
		},
		noResult : function()
		{
			$('.map-search-empty').hide();
			$('.map-search-no-result').show();
		},
		resultsFound : function ()
		{
			$('.map-search-empty').show();
			$('.map-search-no-result').hide();
		},
		emptyInput : function ( input )
		{
			/** ma być this.input */
			input.autocomplete( "widget" ).hide();
		},
		selectPin : function ( pin )
		{
			var pin_image = pin.data('image');
			
			$('.map-second-step-cnt .items-block .item').removeClass('selected');
			
			pin.addClass('selected');
			
			$('#place_upload_image').val( pin_image );
			$('.map-second-step-cnt .ips-modal-submit').prop( 'disabled', false );
		},
		appendSelectablePins : function ( content, method )
		{
			var instance = this;
			
			var content = doTCompile( { 
				compile : 'doT/place_images',
				modal_content : content
			} );
			
			$('.map-second-step-cnt .items-block')[method]( content ).find('.pin-item').on( 'click', function(){
				instance.selectPin( $(this) );
			});
		},
		appendUploadImage : function ( pin )
		{
			this.appendSelectablePins( [{ 
				id : false,
				upload_image : pin[0].name, 
				upload_image_url : pin[0].url
			}], 'prepend' );
			
			this.selectPin( $('.map-second-step-cnt .items-block .item').first() );
		},
		placePinAdded : function ( data )
		{
			try{
				var marker = this.putMarker( data.pin.items[0], data.pin.items[0].upload_image );

				this.map.panTo( marker.getPosition() );
				
				$(window).trigger( 'isotope_loaded', [ $('.sub-content.items-loaded') ] );
				
			}catch(n){
				console.log( n );
			}
			
			var pin = doTCompile( { 
				compile : data.pin.template,
				modal_content : data.pin.items
			} );
			
			this.module.find('.module-close').trigger( 'click' );
			
			addItems( $('.sub-content.items-loaded'), pin, 'prepended' );
			
			this.map_locations.push( data );
			
		},
		pinSelectedPlace : function ( data )
		{
			
			if( $.ips_map.map_options.disallow_multiple_markers )
			{
				for( var index in this.map_locations ) {
					if( this.map_locations[index].place_id == data.place_id )
					{
						$('body').modalAlert( 'To miejsce zostało już umieszczone na tej mapie' );
						return false;
					}
				}
			}
			
			var instance = this;
			
			$('.map-first-step').hide();
			$('.map-second-step').show();
			
			$('.map-back').on( 'click', function(){
				$('.map-second-step').hide();
				$('.map-first-step').show();
				$('.map-second-step-cnt .items-block').find('.pin-item').remove();
			})
			
			$('.map-selected-place').html( data.adr_address );
			$('.map-selected-description textarea').html( data.formatted_address );
			
			data.geometry.location.latitude = data.geometry.location.lat();
			data.geometry.location.longitude = data.geometry.location.lng();

			$('#place_data').val( JSON.stringify( data ) );

			IpsApp._ajax( '/ajax/pinit/places/', { place_action : 'pins', place_id : data.place_id }, 'GET', 'json', true, function( response, ajax ){
				if( response.length > 0 )
				{
					instance.appendSelectablePins( response, 'append' );
				}
			})
			
			$('.ips-upload-file.async').removeClass('async').createUpload();
			
			
			
			//ips_ajax - załaduj obrazki
			
			//masonry
			
			//$('.ips-upload-file') appended
			
			//$('.ips-upload-file').removeClass('async').createUpload();
		},
		searchBoxPlaces : function ( input )
		{
			var instance = this;
			
			var cache = {};
			
			
			input.autocomplete({
				minLength: 0,
				appendTo: input.parents('.map-search-autosuggest'),
				autoFocus: true,
				focus: function( event, ui ) {
					
					if (instance.timeoutId) {
						window.clearTimeout( instance.timeoutId );
						instance.timeoutId = null;
					}
					
					instance.timeoutId = window.setTimeout(function() {
						instance.timeoutId = null;
						instance.places_service.getDetails( ui.item , function( data ){
							instance.fitToMarkers([data]);
						});
				   }, 500);
				},
				source: function( request, response ) {
					var term = request.term;
					
					if( term.length == 0 )
					{
						return this.emptyInput( input )
					}
					
					if ( term in cache ) {
						response( cache[ term ] );
						return;
					}
					
					var prediction_options = {
						input: term
					};
					
					if( this.user_location )
					{
						prediction_options.radius = 100000;
						prediction_options.location = instance.user_location;
					}
					
					//instance.service.getQueryPredictions({ input: term }, parseG );
					//instance.service.getQueryPredictions({ input: term, types : ['(regions)'] }, parseG );
					
					instance.service.getPlacePredictions( prediction_options , function( data , status) {
						if ( data == null || data.length <= 0 || status != google.maps.places.PlacesServiceStatus.OK) {
							return instance.noResult();
						}
						
						instance.resultsFound();
						
						var return_data = [];
						$.each( data, function( index, item ) {
							if( typeof item.types == 'object' )
							{
								return_data.push( item )
							}
						});
						
						return response( return_data );
					});
				},
				select: function( event, ui ) {
					

					instance.places_service.getDetails( ui.item , function( data ){
						instance.fitToMarkers([data]);
						instance.pinSelectedPlace( data );
					});
					
					return false;
				},
				open: function( event, ui ) {
					input.autocomplete( "widget" ).removeAttr('style');
				},
				close: function( event, ui ) {
					input.autocomplete( "widget" ).removeAttr('style');
				}
			})
			
			input.autocomplete( "instance" )._renderMenu = function( ul, items ) {

				var that = this;
				$.each( items, function( index, item ) {
					that._renderItemData( ul, item );
				});
				$( ul ).find( "li:odd" ).addClass( "odd" );
				
				$( ul ).hover(function () {}, function () {
					window.clearTimeout( this.timeoutId );
					this.timeoutId = null;
				}).find( "li:odd" ).addClass( "odd" );
				
			};
			
			input.autocomplete( "instance" )._renderItem = function( ul, item ) {
				
				return $( "<li>" ).append( '<a>' + item.description + '</a>' ).appendTo( ul );
			}
			
			return;
		},
		fitToMarkers : function ( markers ) {
			
			var bounds = new google.maps.LatLngBounds();
			
			// Create bounds from markers
			for( var index in markers ) {
				if( typeof markers[index].getPosition == 'function' )
				{
					var latlng = markers[index].getPosition();
				}
				else
				{
					/* cache image */
					var image = {
						url: "http://maps.gstatic.com/mapfiles/place_api/icons/generic_business-71.png",
						size: new google.maps.Size(71, 71),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(17, 34),
						scaledSize: new google.maps.Size(25, 25)
					};

					  // Create a marker for each place.
					var marker = new google.maps.Marker({
						map: this.map,
						icon: image,
						title: markers[index].description,
						position: markers[index].geometry.location
					});
					
					var latlng = markers[index].geometry.location;
				}
				bounds.extend(latlng);
			}

			// Don't zoom in too far on only one marker
			if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
			   var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
			   var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
			   bounds.extend(extendPoint1);
			   bounds.extend(extendPoint2);
			}

			this.map.fitBounds( bounds );

			// Adjusting zoom here doesn't work :/

		},
		attachAddPlace : function()
		{
			var instance = this;
			
			$('.ips-add-place').on( 'click', function(){
			
				if( !ips_user.is_logged )
				{
					return alert('Nie zalogowany');
				}
				
				$('.board-info-left,.sub-content,.ips-add-place,.board-info-left-shadow').hide();
				
				instance.module = $('body').moduleOverflow( function(){
					$('.board-info-left,.sub-content,.ips-add-place,.board-info-left-shadow').show();
				}, {
					template : 'doT/mapp_add_pin',
					data : [{
						board_id : $(this).data('map-id')
					}]
				} );
				
				instance.attachSearch( instance.module.find('.place-input' ) , true );
			}).show();
		},
		waitForLocation : function()
		{
			$('.wait-for-location').show();
		},
		locationFound : function()
		{
			$('.wait-for-location').hide();
		},
		attachSearch : function( input, wait_for_answer )
		{
			var instance = this;
			
			this.waitForLocation();
			
			if( wait_for_answer && this.deferred.state() == 'pending' )
			{
				console.log('wait_for_answer');
				return setTimeout( function (){
					
					instance.attachSearch( input, false );
					
				}, 2000 );
			}
			
			this.deferred.reject();

			this.deferred.always( function ()
			{
				instance.locationFound();
				instance.searchBoxPlaces( input  ); 
			})
		}
    };


    $.ips_map.map_options = {
        zoom: 7,
		scrollwheel: false,
		panControlOptions: {
			position: google.maps.ControlPosition.TOP_RIGHT
		},
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.MEDIUM,
			position: google.maps.ControlPosition.RIGHT_BOTTOM
		},
		streetViewControl: false,
		mapTypeControl: false
    };
	
	
	function CustomMarker( latlng, map, class_name, div_id, img_src ) {
		
		this.latlng_ = latlng;
		this.class_name = class_name;
		this.div_id = div_id;
		this.img_src = img_src;
		
		this.setMap( map );
	}
	
	CustomMarker.prototype = new google.maps.OverlayView();
	
	CustomMarker.prototype.draw = function() {
		
		var me = this;
		
		var div = this.div_;
		
		if (!div) {
			div = this.div_ = document.getElementById('custom_marker').cloneNode(true);
			
			div.id = this.div_id;
			div.className = this.class_name;

			div.getElementsByTagName('img')[0].src = this.img_src;

			var panes = this.getPanes();
			panes.overlayImage.appendChild( div );
		}
		
		
		
		
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
		
		if (point) {
			div.style.left = point.x + 'px';
			div.style.top = point.y + 'px';
		}
		
		this.getPanes().overlayMouseTarget.appendChild( div );

		// set this as locally scoped var so event does not get confused
		var me = this;

		// Add a listener - we'll accept clicks anywhere on this div, but you may want
		// to validate the click i.e. verify it occurred in some portion of your overlay.
		google.maps.event.addDomListener(div, 'click', function() {
			google.maps.event.trigger(me, 'click');
		});
	};
	
	CustomMarker.prototype.remove = function() {
	 // Check if the overlay was on the map and needs to be removed.
		if (this.div_) {
			this.div_.parentNode.removeChild(this.div_);
			this.div_ = null;
		}
	};

	CustomMarker.prototype.getPosition = function() {
		return this.latlng_;
	};
	
}(jQuery));





















	
	function parseG( data )
	{
		console.log(data);
	}
	

	
	

	
		
		

		function selectMarker( data )
		{
			console.log(data);
		}













