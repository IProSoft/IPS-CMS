function in_array (val, heystack) {
    var key = '';
        for (key in heystack) { 
			if(heystack[key] == val) {
                return true;
            };
        };
   
    return false;
};
	
var last_voted_id = 0;

var voted = new Array();

function deleteContestCaption( caption_id )
{
	
	var response = IpsApp._ajax( '/ajax/contest/' + caption_id, { action : 'delete' }, 'POST' );
	
	if( typeof response.error !== 'undefined' )
	{
		var dialog = overflowMsg( response.error )
	}
	else if( typeof response.success !== 'undefined' )
	{
		
		$("#opinion_value_" + caption_id).parent().remove();
		nextprev(false);	
		var dialog = overflowMsg( response.success )	
	}
		
	closeOverflowMsg( dialog, 2500 );
		
}
function contestVote( caption_id, value )
{
	if ( ips_user.is_logged == false )
	{
		return userAuth('login');
	};
	
	var contest_vote_down = parseInt( $('#contest_vote_down .contest_votes_left').html() );
	var contest_vote_up = parseInt( $('#contest_vote_up .contest_votes_left').html() );
	
	var dialog = overflowMsg( js_contest_votes_empty + '... <img src="/images/loading.gif" />' );
	
	if (((value == 'votes_up') && (contest_vote_up <= 0)) || ((value == 'votes_down') && (contest_vote_down <= 0)))
	{
		dialog.text(js_contest_votes_empty);	
	};
	if(participate_in == true)
	{
		dialog.text(js_contest_votes_blocked);
	}
	else if(has_ended == 'true')
	{
		dialog.text(js_contest_already_finished);
	
	}
	else if( last_voted_id != caption_id && !in_array( caption_id, voted ) )
	{
	
		last_voted_id = caption_id;
		

		var response = IpsApp._ajax( '/ajax/contest/' + caption_id, { action : 'vote', contest_id: contest_id, value: value }, 'POST' );
	
		if( typeof response.error !== 'undefined' )
		{
			dialog.text( response.error );
		}
		else if( typeof response.success !== 'undefined' )
		{
			
			dialog.text( response.success );
			
			voted.push( caption_id );
			
			var opinion_value = $("#opinion_value_" + caption_id +" b:first");
			var votes = $("#opinion_value_" + caption_id +" b:last");
			
			if ( value == 'votes_down' )
			{
				$('#contest_vote_down .contest_votes_left').html(--contest_vote_down);
				var val = parseInt(opinion_value.html()) - 1;
			}
			else if ( value == 'votes_up' )
			{
				$('#contest_vote_up .contest_votes_left').html(--contest_vote_up);
				var val = parseInt(opinion_value.html()) + 1;
			};
			
			opinion_value.html( val );
			votes.html( parseInt(votes.html()) + 1 );
		}

	}
	else
	{
		dialog.text( js_contest_voted_already );
	};
	
			
	closeOverflowMsg( dialog, 3000 );
	
	return false;
};


function contests( contest_id )
{

	var dialog = overflowMsg( js_contest_wait + '... <img src="/images/loading.gif" />' );
	
	if ( $('#contests_title').val() == '' )
	{
		dialog.text( js_contest_item_title_req );
	}
	else
	{
	
		var caption_title = $('#contests_title').val();
		var caption = $('#contests_text').val();
		
		var response = IpsApp._ajax( '/ajax/contest/' + contest_id, { action : 'add', caption_title: caption_title, caption: caption }, 'POST' );
	
		if( typeof response.error !== 'undefined' )
		{
			dialog.text( response.error );
		}
		else if( typeof response.success !== 'undefined' )
		{
			dialog.text( response.success );
			html = '<div id="contest_demotivator"><div class="contest_demotivator_main"><div class="contest_demotivator_top"><div class="contest_demotivator_title">'+caption_title+'</div><div class="contest_demotivator_text">'+caption+'</div></div></div></div>';
			
			$("div#contest_demotivator_last").before(html);
			$('#contests_title').val('');
			$('#contests_text').val('');
			

			$("#warstwa-img-podpis").html( html );
			
			$("#contests_form").fadeOut();
			$(window).scrollTop( $('.warstwa_body').offset().top );
		}
	};

	closeOverflowMsg( dialog, 3000 );

};
$(document).ready(function() {
	var top = $(".contest_image_layer").height() / 2;
	
	$(".button-next").css("top", top);
	$(".button-previous").css("top", top);
	nextprev(false);
});

function nextprev (kierunek) {
	
	if( typeof set == 'undefined' )
	{
		set = $("#contest_demotivator:first");
	}
	else if( kierunek == 'down' )
	{
		set = $(set).prev();
	}
	else
	{
		set = $(set).next();
	}
	var id = set.attr("id");
	if(  typeof id == 'undefined'  )
	{
		set = $("#contest_demotivator:first");
		var id = set.attr("id");
		if(  typeof id == 'undefined'  )
		{
			alert(js_contest_no_captions);
			return;
		}
	}
	
	$("#warstwa-img-podpis").html( set.html() );
}
	