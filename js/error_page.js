function makeRandomLetter()
{
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    return possible.charAt( Math.floor( Math.random() * possible.length ) );
}
function fadeElement( element ){
	setTimeout( function(){
		element.css('opacity', 0.3);
		unfadeElement( element );
	}, 1500 );
}
function unfadeElement( element ){
	setTimeout( function(){
		element.css('opacity', 1);
		fadeElement( element );
	}, 500 );
}
$(document).ready(function(){
	
	var not_found = {10:'4',11:'0',12:'4', 23:'P', 24:'A', 25:'G', 26:'E', 30:'N', 31:'O', 32:'T', 44:'F', 45:'O', 46:'U', 47:'N', 48:'D'};
	var internal = {10:'5', 11:'0', 12:'0', 22:'S', 23:'e', 24:'r', 25:'v', 26:'e', 27:'r', 37:'E', 38:'r', 39:'r', 40:'o', 41:'r'};
	var forbidden = {10:'4', 11:'0', 12:'3', 22:'A', 23:'c', 24:'c', 25:'e', 26:'s', 37:'D', 38:'e', 39:'n', 40:'i', 41:'e', 42:'d'};

	if( error_number == 500)
	{
		var items_letters = internal;
		var css = 'error-item-red';
	}
	else if( error_number == 403)
	{
		var items_letters = forbidden;
		var css = 'error-item-blue';
	}
	else
	{	
		var items_letters = not_found;
		var css = 'error-item';
	}

	var cnt = $('#content-letters ul');
	var items = [];
	for( var i=1; i < 62; i++ )
	{
		if( items_letters.hasOwnProperty(i) )
		{
			$('<li>').addClass('item-' + i).appendTo( cnt ).html( items_letters[i] );
			items.push( '.item-' + i );
		}
		else
		{
			$('<li></li>').appendTo(cnt).html( makeRandomLetter() );
		}
		
	}
	$('<li></li>').appendTo(cnt).html( '<a href="http://'+window.location.host+'"><img src="http://'+window.location.host+'/libs/Handlers/Errors/homepage.png" /></a>' );
	
	for( var i=0; i <items.length; i++ )
	{
		setTimeout( "$('" + items[i] + "').addClass('" + css + "');", i * 300 );
	}
	
	fadeElement($('#content-letters ul li img'));
});