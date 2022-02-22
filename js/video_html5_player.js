if( typeof ips_video_ready === 'undefined')
{
	function ips_video_ready( id, autoplay )
	{
		
		if( $('#html5_video_' + id).hasClass('mediaelement') )
		{
			return MEplayer( $('#html5_video_' + id).find('video'), autoplay );
		}
		
		this.containter = $('#html5_video_' + id);
		
		this.status = 0;
		this.video = false;
		this.video_id = id;
		this.video_config = [];
		
		/**
		* Initialize for each video
		*/
		this.initialize = function( autoplay )
		{
			if( this.status != 0 )
			{
				return true;
			}
			
			this.video_config = JSON.parse( this.containter.attr('video-config') );
			
			var id = 'html5_video_id_' + this.video_id;
			
			var src_video = '<source src="' + this.video_config.video_src + '" type="video/mp4">';
			
			if( this.video_config.video_webm )
			{
				var src_video = src_video + "\n" + '<source src="' + this.video_config.video_src.substr(0, this.video_config.video_src.lastIndexOf(".")) + ".webm" + '" type="video/webm">';
			}
			
			var src_subtitles = '';
			
			if( typeof this.containter.attr('video-vtt') !== 'undefined' )
			{
				var src_subtitles = '<track kind="subtitles" srclang="pl" src="' + this.containter.attr('video-vtt') + '" label="Polski" default></track>';
			}
			
			if( ips_config.is_mobile )
			{
				$('.html5_video_cover, .html5_video_controls, .html5_video_caption').remove();
			}
			
			
			var video_element = $( '<video id="'+id+'" ' +( ips_config.is_mobile ? '' : 'loop' ) + ' preload="auto" '+
				'width="' + this.video_config.width + '" ' +
				'height="' + this.video_config.height + '" ' +
				'poster="' + this.video_config.media_poster + '">' +
				src_video +
				src_subtitles +
				'</video>'
			);
		
			this.containter.find('.html5_video_container').html( video_element );
			
			var tech_order = ["html5", "flash"];
			

			this.video = videojs( id, { 
				ips_video_autoplay: autoplay, 
				parent_func: this, 
				techOrder: tech_order
			}).ready( function () {

				this.load();
				
				this.height( this.options().height );
				this.width( this.options().width );

				if( this.options().parent_func.video_config.watermark == false )
				{
					this.watermark({
						file: 'http://vins.pl/_vines_extract/watermark.png',
						xpos: 0,
						ypos: 0,
						xrepeat: 0,
						opacity: 0.9,
					});
				}
				
				if( this.options().ips_video_autoplay == 1 )
				{
					/* var only_autoplay = false; */
					only_autoplay_func = function() {
						
						if ( only_autoplay )
						{ 
							return;
						}
						
						only_autoplay = true;

						if ( !ips_config.is_mobile )
						{
							this.options().parent_func.on_play();
							this.play();
						}
					};
					
					this.on( 'canplaythrough', only_autoplay_func );
					this.on( 'canplay', only_autoplay_func );
				}

				if ( ips_config.is_mobile )
				{
					this.on( 'ended', function(){
						this.currentTime=0.1;
						this.play();
					});
				}
			});

			/* 
			_V_( id, {}, function(){
				var myPlayer = this;
			});
			
			this.video = video_element.get( 0 );
			*/
			
			this.status = 1;
			
			return true;
		}
		
		/**
		* Plays video
		*/
		this.play = function()
		{
			if( this.status == 0 )
			{
				this.initialize();
			}
			
			if( this.video && this.video.paused() )
			{
				$.each( ips_videos_html5, function( node, video ) {
					if( typeof video.video !== 'undefined' )
					{
						video.video.pause();
					}
				});
			
				this.video.play();
				
				this.on_play();
				
				this.captionPosition();
			}
		}
		
		/**
		* Sets Timeout for video play and fire play for mouse triggers. 
		* Time allow user not to play video id takes out mouse before time
		*/
		this.slow_play = function()
		{
			if( this.status == 0 )
			{
				this.initialize();
			}
			
			if( this.video && this.video.paused() )
			{
				if( current_video.timeout )
				{
					window.clearTimeout( current_video.timeout );
				}
				
				current_video.timeout = window.setTimeout( 'get_ips_video( ' + this.video_id + ' ).play()', 300 );
				
				this.containter.removeClass('playing loading paused').addClass('loading');
			}
		}
		
		/**
		* Sets position of video captions
		*/
		this.captionPosition = function()
		{
			$('.html5_video_caption').each(function(){
				$(this).removeAttr('style');
			})
			
			if( this.containter.hasClass( 'playing' ) )
			{
				this.containter.find('.html5_video_caption').css( {top: ( this.containter.parents('.file-container').height() - this.containter.find('.html5_video_caption').height() ) - 10 } );
			}
		}
		
		/**
		* Fire action when paused
		*/
		this.pause = function()
		{
			if( this.status > 0 && this.video && !this.video.paused() )
			{
				this.video.pause();
				
				this.containter.removeClass('playing loading paused').addClass('paused');
				
				this.captionPosition();
			}
			
		}
		
		/**
		* Fire action when clicked
		*/
		this.onclick = function()
		{
			
			var video_cnt = get_ips_video( $(this).attr('video-id') );
			
			if( video_cnt.status == 0 || ( video_cnt.video && video_cnt.video.paused() ) )
			{
				video_cnt.play();
			}
			else
			{
				video_cnt.pause();
			}
		}
		
		this.on_play  = function()
		{
			$( this.containter ).removeClass('playing loading paused').addClass('playing');
		}

		/**
		* Fire if video is set to autoplay
		*/
		
		this.initialize( autoplay );

		/**
		*
		*/
		$( this.containter ).click( this.onclick );
		
		/**
		* Actions only for vines template
		*/
		if( ips_config.version == 'vines' )
		{
			this.containter.find('.html5_video_cover').on('mouseenter', function(){
				get_ips_video( $(this).attr('video-id') ).slow_play();
			}).on('mouseleave', function(){
				//get_ips_video( $(this).attr('video-id') ).pause();
			});
		}
	}
	
	function MEplayer( video, autoplay )
	{
		var video_config = JSON.parse( video.parent().attr('video-config') );
		
		if( video_config.width > video.width() && video.width() > 100 )
		{
			var ratio = video.width() / video_config.width;

			video.parent().css( 'width', video_config.width * ratio );
			video.parent().css( 'height', video_config.height * ratio );
		}

		video.mediaelementplayer({
			pluginPath: '/libs/MediaElement/',
			loop: true,
			success: function (mediaElement, domObject) {
				$(mediaElement).css( 'opacity', 1 );
				if( autoplay )
				{
					if( ips_image_lock )
					{
						return $(mediaElement).lock();
					}
					mediaElement.play();
				}
			}
		});

	}
}
