$(function() {
	page_num = 1;
	
	var hash = window.location.hash;
	if( hash != '' )
	{
		page_num = parseInt(hash.substring(1)) > paginationTotal ? 1 : hash.substring(1);
	}

	if( paginationTotal > 1 )
	{
		$("#containerPaginate").paginate({
			count 		: paginationTotal,
			start 		: page_num,
			display     : 10,
			border		: true,
			images		: false,
			mouse		: 'press',
			onChange    : function(page){ 
				sortowanieLoad(page);
				window.location.hash = page;
			}
		});
	}
	
	
	sortowanieLoad( page_num );
});

function sortowanieLoad(page)
{
	var sort_by = $("#sort_by").length > 0 ? $("#sort_by").val() : '';
	var order = $("#sort_by_order").length > 0 ? $("#sort_by_order").val() : '';
	var privacy = $("#privacy").length > 0 ? $("#privacy").val() : '';
	var login = $("#login").length > 0 ? $("#login").val() : '';
	var tag = $("#tag").length > 0 ? $("#tag").val() : '';
	var file_category = $("#file_category").length > 0 ? $("#file_category").val() : '';
	var addString = '';
	
	if( file_category != '')
	{
		var addString = addString + "&file_category="+file_category;
	}

	if( login != '' )
	{
		var addString = addString + "&login=" + login;
	}
	
	if(tag != '')
	{
		var addString = addString + "&tag="+tag;
	}
	
	if(order != '')
	{
		var addString = addString + "&order="+order;
	}
	
	if(sort_by != '')
	{
		var addString = addString + "&sort_by="+sort_by;
	}
	
	if(page != '')
	{
		var addString = addString + "&page="+page;
	}
	
	if( paginationAdres == 'users' && $("#ban").is(':checked') )
	{
		var addString = addString + "&ban=1";
	}


	$.ajax({
		type: "GET",
		async: true, 
		url: 'load.php?action='+paginationAdres + addString + ( typeof privacy != 'undefined' ? '&privacy='+privacy : '' ),
		success: function(html){
			$("#wrapper").empty().append(html);	
			if ( $.isFunction( window.editbox_init ) )
			{
				editbox_init();
			}
			
		},
		failure: function(html){
			$("#"+id).innerHTML = "Sorry, please try again..."
		}
	});
}