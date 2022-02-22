<?php
/**
 * IPS-CMS
 *
 * Copyright (c) IPROSOFT
 * Licensed under the Commercial License
 * http://www.iprosoft.pro/ips-license/	
 *
 * Project home: http://iprosoft.pro
 *
 * Version:  2.0
 */ 
	
class Comments extends Templates
{
	
	/**
	* Identyfikator materiału
	*/	
	public $file_id;
	
	/**
	* Pobrane komentarze
	*/
	public $comments;

	public function get( $file_id, $options = array() )
	{
		if( !empty( $file_id ) )
		{
			$this->file_id = $file_id;
			
			$conditions = array( 
				'c.user_login'	=> 'field:u.login',
				'c.upload_id'	=> $this->file_id,
				'c.is_reply'	=> 0,
			);
			
			if( isset( $options['conditions'] ) )
			{
				$conditions = array_merge( $conditions , $options['conditions'] );
			}
			
			if( !isset( $options['order'] ) )
			{
				$options['order'] = 'c.date_add';
			}
			
			if( !isset( $options['limit'] ) )
			{
				$options['limit'] = false;
			}
			
			$comments = PD::getInstance()->from( array( 
				'upload_comments' => 'c',
				'users' => 'u'
			) )->setWhere( $conditions )->fields( array(
				'c.*',
				'u.avatar',
				'u.first_name',
				'u.last_name'
			))->orderBy( $options['order'], 'DESC' )->get( $options['limit'] );
			
			if( isset( $options['return'] ) )
			{
				return $comments;
			}
			
			$this->comments = $comments;
			unset( $comments );
		}

		return $this;
	}
	

	public function __set( $var, $val )
	{
		$this->{$var} = $val;
	}
	
	
	/**
	* Sortowanie komentarzy wraz z odpowiedziami.
	*
	* @param null
	* 
	* @return void
	*/
	public function sortComments( &$comments )
	{
		$comments_answers = $this->get( $this->file_id, array( 
			'conditions' => array( 
				'c.is_reply' => 1
			),
			'return' => true
		));
		
		$comment_has_answers = array_column( $comments_answers, 'is_reply_for_id', 'id' );

		$file_comments = array();

		if( is_array( $comments ) )
		{
			foreach( $comments as $key => $comment )
			{
				$file_comments[] = $comment;
		
				if( in_array( $comment['id'], $comment_has_answers ) )
				{
					$this->searchAnswers( $comment['id'], $file_comments, $comments_answers );
				}
			}
		}
		$comments = $file_comments;
	}	
	/**
	* Wyszukiwanie w tablicy odpowiedzi na komentarz
	*
	* @param array $file_comments
	* @param array $comment
	* @param array $comments_answers
	* 
	* @return array $file_comments
	*/
	public function searchAnswers( $comment_id, &$file_comments, &$comments_answers )
	{
		
		foreach( $comments_answers as $next_key => $answer_comment )
		{
			if( $answer_comment['is_reply_for_id'] == $comment_id )
			{
				$file_comments[] = $answer_comment;
				$this->searchAnswers( $answer_comment['id'], $file_comments, $comments_answers );
			}
		}
	}
	
	/**
	* Function to load comments from variable $loadedComments
	*
	* @param 
	* 
	* @return 
	*/
	public function load()
	{
		if( empty( $this->comments ) )
		{
			return false;
		}
		
		$this->sortComments( $this->comments );
		
		$comments = '';
		$comment_negative = Config::getArray( 'comments_options', 'below_vote' );
			
			$tpl = Templates::getInc();	
			
			foreach( $this->comments as $key => $r )
			{
				$comments .= AdSystem::getInstance()->showAd( 'between_comments', $key );

				$is_negative = ( $r['comment_opinion'] < $comment_negative ? 'comment_negative' : false );

				$comments .= $tpl->getTpl( 'comments_display.html', array_merge( $r, array(
					'avatar'		=> ips_user_avatar( $r['avatar'] ),
					'content'		=> nl2br( $r['content'] ),
					'moderator'		=> USER_MOD,
					'can_delete'	=> ( USER_MOD || ( USER_LOGGED && $r['user_login'] == USER_LOGIN ) ),
					'div_comment_css' => $is_negative . ' ' . ( $r['is_reply'] != 0 ? 'is_reply' : false ),
					'is_negative'	=> (bool)$is_negative,
					'full_name'		=> $r['first_name'] . ' ' . $r['last_name'],
					'date_format'	=> formatDate( $r['date_add'] )
				) ) );

			}
			
		return $comments;

	}

	/**
	* 
	*
	* @param $file_id - comment id
	* 
	* @return 
	*/
	public function bestComments( $file_id )
	{
		return $this->get( $file_id, array(
			'order' => Config::get( 'widget_top_comments_options', 'sort' ),
			'limit' => Config::get( 'widget_top_comments_options', 'count' ),
			'return' => true
		) );	
	}
	/**
	* 
	*
	* @param $comment - content
	* @param $reply_to_id - comment id
	* @param $upload_id - file id
	* @param  $user_id - user id
	* 
	* @return 
	*/
	public function add( $comment_content, $upload_id, $reply_to_id = false, $user )
	{
		$comment_content = App::censored( apply_filters( 'upload_comment_content', $comment_content ) );
		
		preg_match( '@([a-zA-Z]*)::(.{1,})::(.{1,})@iu', $comment_content, $matches );
		
		if( !empty( $matches ) )
		{
			/**
			* Comment as demot
			*/
			if( Config::getArray( 'comments_options', 'as_image' ) )
			{	
				$comment_content = $this->demotComment( $comment_content, $upload_id, $matches );
			}
			else
			{
				throw new Exception('comments_add_blocked');
			}
		}
		else
		{
			
			$comment_content = Sanitize::cleanXss( Sanitize::nl2br2( $comment_content ) );

			if( Config::getArray( 'comments_options', 'emots' ) )
			{
				$comment_content = $this->bbCode( $comment_content );
			}
		}
		
		return $this->insert( $comment_content, $upload_id, $reply_to_id, $user );
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function insert( $comment_content, $upload_id, $reply_to_id, &$user )
	{
		$insert_id = PD::getInstance()->insert( 'upload_comments', array(
			'user_login' => $user['login'],
			'user_id' => $user['id'], 
			'content' => $comment_content,
			'upload_id' => $upload_id,
			'date_add' => IPS_CURRENT_DATE,
			'is_reply' => ( $reply_to_id ? 1 : 0 ),
			'is_reply_for_id' => $reply_to_id
		));
		
		if( $insert_id )
		{
			PD::getInstance()->update( IPS__FILES, array(
				'comments' => PD::getInstance()->cnt( 'upload_comments', array( 
					'upload_id' => $upload_id
				))
			), array( 'id' => $upload_id ));
			
			Operations::updateUserStats( $user['id'] );
			
			do_action( 'comment_add', array( 
				'id' => $insert_id
			) );
			
			if ( Config::get('module_history') )
			{
				Ips_Registry::get('History')->storeAction( 'comment', array( 
					'upload_id' => $upload_id
				) );
			}
			
			return array( 
				'insert_id' => $insert_id,
				'content' => $this->get( $upload_id, array(
					'conditions' => array(
						'c.id' => $insert_id
					)
				) )->load(),
				'message' => __( 'comments_added' )
			);
			
		}
		else
		{ 
			throw new Exception('err_unknown');
		}
	}

	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function demotComment( $comment_content, $upload_id, $matches )
	{
		$sql = PD::getInstance()->select( IPS__FILES, array(
			'id' => $upload_id
		), 1);
		
		if( $sql['upload_type'] == 'video' )
		{
			throw new Exception('comments_add_video_blocked');
		}
		else
		{
			require_once( ABS_PATH . '/functions-upload.php' );
			
			$data = Upload_Meta::get( $upload_id, 'upload_file_data' );
			
			$upload = new Upload_Extended();

			$_POST['top_line'] = $matches[1];
			$_POST['bottom_line'] = $matches[3];
			$_POST['upload_url'] = $data['image'];


			$upload->makeFileConfig( 'demotywator', 'image' );
	
			$upload->uploadFile();

			$file = $upload->Load( IMG_PATH . '/' . $upload->getName() );
	
			$file['upload_type']	= 'demotywator';
			$file['font_color']	= Config::getArray( 'upload_demotywator_text', 'font_color' );
			$file['font']	= Config::getArray( 'upload_demotywator_text', 'font' );
			$file['title']	= $upload->getTitle();
			$file['top_line']	= ( isset( $_POST['top_line'] )	? $_POST['top_line']	: $file['title'] );
			$file['bottom_line']	= ( isset( $_POST['bottom_line']  )	? $_POST['bottom_line']   : $file['title'] );
			
			$upload->configuration( $file );
					
			$upload->initCreateImage();

			File::deleteFile( IMG_PATH. '/' .$upload->getName() );
			File::deleteFile( $upload->config['up_folder']['large'] . '/' . basename( $upload->getName() ) );
			File::deleteFile( $upload->config['up_folder']['thumb'] . '/' . basename( $upload->getName() ) );
			
			if( !is_dir( ABS_PATH . '/upload/upload_comments' ) )
			{
				File::createDir( ABS_PATH . '/upload/upload_comments' );
			}
			
			$file = 'upload/upload_comments/' . basename( $upload->getName() );
			
			rename( $upload->config['up_folder']['medium'] . '/' . basename( $upload->getName() ), ABS_PATH . '/' . $file );
			
			return '<img class="demot_in_comment" src="' . IPS_DNS_URL . '/' . $file . '">';
		}
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function bbCode( $content )
	{
		$string = array(
			array(':p',';p', ';P', ':-P', ';-P', ':-p', ';-p', ':P' ),
			array(':-)', ':)', ':]' ),
			array(':(', ';[', ';[', ';(', ':[', ';[', ':-[', ';-(', ';-[', ':-(' ),
			array(';*', ':*', ';**', ':-*', ';-*'),
			array('xd', 'Xd', 'xD', ':d', ';d', ';D', ';-D', ':-d', ':-D', ':D' ), 
			array(':o', ';o', ';-o', ':O', ';O', 'lol', 'LoL', 'LOL', ':-O', ';-O'), 
			array(':-@', ';@', ';-@', ':@'), 
			array(';)', ';-)'), 
		);
  
		$obrazki = array('bbcode_tongue', 'bbcode_smiled', 'bbcode_sad', 'bbcode_kiss', 'bbcode_happy', 'bbcode_lol', 'bbcode_threatens', 'bbcode_eyelet');

		foreach ( $string as $key => $val )
		{
			$content = str_replace( $val, '<span class="images_bbcode '. $obrazki[$key] . '""></span>', $content );
		}

			$content = preg_replace(array(
				'#\[b\](.*?)\[/b\]#i', // Pogrubienie
				'#\[i\](.*?)\[/i\]#si', // Kursywa
				'#\[u\](.*?)\[/u\]#si', // Podkreślenie
				'#\[center\](.*?)\[/center\]#si', // Wyśrodkowanie
				'#\[quote\](.*?)\[/quote\]#si',
				'#\[video\](.*?)\[/video\]#si',
				'#\[img\](.*?)\[/img\]#si'
			), array(
				'<b>\\1</b>',
				'<i>\\1</i>',
				'<u>\\1</u>',
				'<center>\\1</center>',
				'<cite>\\1</cite>',
				'\\1',
				'<img src="\\1" />',
			), $content); 
			
			if( Config::getArray( 'comments_options', 'as_video' ) )
			{
				$content = preg_replace( "#\[video\](.*?)\[/video\]#si", " \\1 ", $content );
				
				$upload = new Upload();
				$content = $upload->findYoutube( $content );
			}
			/* while(preg_match('#(?:http://)?(?:www\.)?(?:youtube\.com/(?:v/|watch\?v=)|youtu\.be/)([\w-]+)$#', $content, $match))
			{
				$content = str_replace($match[0], videobb($match[0]), $content);
			} */
			
		return $content;
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/	
	public static function vote( $comment_id, $vote_action )
	{
		$user = getUserInfo( USER_ID, true );
		
		if( $user['user_banned'] == 1 )
		{
			throw new Exception('comments_vote_banned');
		}
		
		$row_temporary = Ips_Registry::get('Temporary')->get( array(
			'object_id' => $comment_id,
			'user_id' => $user['id'],
			'action' => 'vote_comment',
		));
		
		if( time() - $row_temporary['time'] < 86400 )
		{
			throw new Exception('comments_voted');
		}
		
		$coomment_row = PD::getInstance()->select( 'upload_comments', array(
			'id' => $comment_id
		), 1 );
		
		/** User is author */
		if( $coomment_row['user_login'] == USER_LOGIN )
		{
			throw new Exception('comments_vote_own');
		}

		PD::getInstance()->update( 'upload_comments', array(
			'comment_opinion' => ( $vote_action == 'votes_up' ? ( $coomment_row['comment_opinion'] + 1 ) : ( $coomment_row['comment_opinion'] - 1 ) ),
			'comment_votes' => $coomment_row['comment_votes'] + 1
		), array(
			'id' => $comment_id
		) );
		
		Ips_Registry::get('Temporary')->set( $row_temporary );
		
		return array(
			'success' => __('vote_added'),
			'comment_opinion' => ( $vote_action == 'votes_up' ? $coomment_row['comment_opinion'] + 1 : $coomment_row['comment_opinion'] - 1 ),
			'comment_votes' => $coomment_row['comment_votes'] + 1
		);
		
	}
	

}
?>