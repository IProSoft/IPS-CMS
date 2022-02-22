! function ( obj_window, obj_document, obj_self) {
    var ips_e = {
        w: obj_window,
        d: obj_document,
        a: obj_self,
        func: function () {
            return {
                callback: [],
                get: function (a, b) {
                    var c = null;
                    return c = "string" === typeof a[b] ? a[b] : a.getAttribute(b)
                },
                prepare: function ( options ) {
					armap = [];
					for ( var k in ips_e.a )
					{
						if( typeof options[k] !== 'undefined' )
						{
							ips_e.a[k] = options[k];
						}
						armap.push( k + '=' + ips_e.a[k] );
					}
					return armap.join('&');
                },
				append: function( content, options ){
					var div = document.createElement('div');
					div.style.cssText = 'opacity:0';
					div.innerHTML = content;
					var parentDiv = ips_e.d.gallery_script.parentNode;
					parentDiv.insertBefore( div, ips_e.d.gallery_script);
					jQuery(document).ready(function(){
						jQuery('.bxslider').bxSlider({
							mode: ips_e.a['mode'],
							minSlides: 1,
							maxSlides: ips_e.a['limit'],
							slideWidth: ips_e.a['img_size'],
							slideMargin: 10,
							pager: false,
							captions: ips_e.a['captions'],
						});
						/* console.log({
							mode: ips_e.a['mode'],
							minSlides: 1,
							maxSlides: ips_e.a['limit'],
							slideWidth: ips_e.a['img_size'],
							slideMargin: 10,
							pager: false,
							captions: ips_e.a['captions'],
						}); */
						jQuery('.bx-wrapper').addClass( 'bx-' + options['template'] );
						div.style.cssText = '';
					});
					
				},
				css: function ( embed_url, adress ) {
					var file = document.createElement("link");
					file.setAttribute("rel", "stylesheet");
					file.setAttribute("type", "text/css");
					file.setAttribute("href", embed_url + adress );
					return document.getElementsByTagName("head")[0].appendChild( file )
				},
				js: function ( embed_url, adress ) {
					var file = document.createElement("script");
					file.setAttribute("type", "text/javascript");
					file.setAttribute("src", embed_url + adress );
					return document.getElementsByTagName("head")[0].appendChild( file )
				},
                init: function ( options ) {
                    //ips_e.func.css( options.embed_url, 'css/embed.css' );
					if( ips_e.func.css( options.embed_url, 'libs/galleries/bxslider/jquery.bxslider.compiled.css' ) )
					{
						if( ips_e.func.js( options.embed_url, 'libs/galleries/bxslider/jquery.bxslider.min.js' ) )
						{
							ips_e.d.gallery_script = ips_e.d.getElementById( options.rand_id );
							
							var url = options.embed_url + 'ips-embed.php?load=true&' + ips_e.func.prepare( options );
							
							ips_e.func.httpGet( url, options );
						}
					}
                },
				httpGet: function ( url, options )
				{
					(function($) {
						$.ajax({
						   type: 'GET',
							url: url,
							async: true,
							jsonpCallback: 'requestembed',
							contentType: "application/json",
							dataType: 'jsonp',
							success: function(json) {
							   ips_e.func.append( json.content, options );
							},
							error: function(e) {
							   console.log(e);
							}
						});
					})(jQuery);
				}
            }
        }()
    };
	
	
	setTimeout(function(){
		ips_e.func.init( ips_embed );
	}, 100 );
	
}(window, document, {
    rand_date	: (new Date).getHours(),
	img_size	: 'small',
	img_width	: false,
	img_height	: false,
	source		: 'all', 
	category_id	: false,
	source_order: 'date_add',
	limit		: 10,
	mode		: 'horizontal',
	captions	: false
});

function createDiv(responsetext)
{
    var _body = document.getElementsByTagName('body')[0];
    var _div = document.createElement('div');
    _div.innerHTML = responsetext;
    _body.appendChild(_div);
}