$(document).ready(function(){
	onFast = true;
	startGallery();
	$('#go-prev').click(function(){
		setNav( 'previous' );
		return false;
	});

	$('#go-next').click(function(){
		setNav( 'next' );
		return false;
	});
});
function setNav( action )
{
	if( action == 'previous' )
	{
		$('#go-next').animate({opacity: 1.0}, 1000);
		galleryPrev();
	}
	else
	{
		$('#go-prev').animate({opacity: 1.0}, 1000);
		galleryNext();
	}
}
function startGallery()
{
	$('#go-prev').animate({opacity: 0.0}, 100);
	start = true;
	loadGallery( 'next', 0 );
}
function loadGallery( load, upload_id )
{
	// jeśli zwróci false blokujemy button
	
	IpsApp._ajax( '/ajax/fast/' + upload_id, { direct: load, action: containerSlide }, 'POST', 'json', false, function( response ){
		if( typeof response.error !== 'undefined' )
		{
			$('#go-' + load).animate( {opacity: 0.0}, 1000 );
			if( start )
			{
				alert('Niestety nie ma nic do wyświetlenia');
			}
		}
		else if( typeof response.content !== 'undefined' )
		{
			var $response = $( response.content );
			
			if( load == 'next' )
			{
				$('#fast-slider').append( $response );
			}
			else if( load == 'prev' )
			{
				$('#fast-slider').prepend( $response );
			}
				
			setTimeout(function() {
				if( !start )
				{
					setTimeout( function() { 
						gapi.plusone.go();
					}, 3000 ); 
				}
				
				if( start )
				{
					$('#fast-slider li').first().before( '<li id="0" class="show" style="opacity: 0;"></li>' );
					start = false;
				}
				
				galleryNext();
				
			}, 1000 );
				
			/*$.getScript("js/facebook_share.js", function(){});
			FB = FBIPS
			FB.XFBML.parse();
			FB.Share.renderPass();*/
		}
	});	

	return true;
}
function getCurrent(){

	if( $("#fast-slider li.show").attr("id") !== null )
	{
		return $('#fast-slider li.show');
	}
	else if( typeof $('#fast-slider li').first().lenght != 'undefined' )
	{
		return $('#fast-slider li').first();
	}
	else
	{
		return false;
	}
}
function galleryPrev() {
	
	var current = getCurrent();
	if( current )
	{
		if( current.prev().length )
		{
			animateGallery( current.prev(), current );
		}
		if( !current.prev().length )
		{
			loadGallery( 'prev', ( current.prev().attr("id") ? current.prev().attr("id") : ( current == 'false' ? current : current.attr("id") ) ) );
		}
		
	}

}
function galleryNext() {
	
	var current = getCurrent();
	
	if( current )
	{
		/*FB.Share.renderPass();*/
		if( current.next().length  )
		{
			animateGallery( current.next(), current );
		}
		if( !current.next().length )
		{
			loadGallery( 'next', ( current.next().attr("id") ? current.next().attr("id") : ( current == 'false' ? current : current.attr("id") ) ) );
		}
	}
}
function animateGallery( next, current ){
	
	var id = next.attr("id");
	
	$("#to-load").html( $("#fast-load_" + id + " span.fast-load-title").html() );
			
	var $response = $("#fast-load_" + id + " span.load-buttons").html();
	
	$("#to-load-likes").html( $response );
	
	/**
	* Załadowane pod logo, teraz render
	*/
	$("#to-load-likes .tools").each(function()
	{
		FB.XFBML.parse( this );
		/**
		console.log( $(this).getElementsByTagName('a').getAttribute('data-url'))
		FB.Share.renderButton( $(this).find('a') );
		*/
	});
	/**
	$("#fast-load_" + id + "  a.ips-share-link").each(function()
	{
		$(this).attr('name','fb_share');
		FB.Share.updateButton( $(this) );
	});
	*/
	next.css({opacity: 0.0})
	.addClass('show')
	.animate({opacity: 1.0}, 1000);

	current.animate({opacity: 0.0}, 1000)
	.removeClass('show');
}