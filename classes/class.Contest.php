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


class Contest
{
	
	public $contestID;
	
	public $_stan = true;
	

	public function getOrderField()
	{
		$order_filed = array(
			'votes_opinion' => IPS__FILES . '.votes_opinion DESC, ' . IPS__FILES . '.date_add',
			'share' => 'shares.share DESC, ' . IPS__FILES . '.date_add',
			'comments' => IPS__FILES . '.comments DESC, ' . IPS__FILES . '.date_add' 
		);
		
		if ( IPS_VERSION == 'pinestic' )
		{
			$order_filed['votes_opinion'] = str_replace( 'votes_opinion', 'pin_likes', $order_filed['votes_opinion'] );
		}
		
		return $order_filed;
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function contestsList()
	{
		$result = PD::getInstance()->select( 'contests', array(
			'contest_activ' => 1
		) );

		if ( !empty( $result ) )
		{
			$tpl = Templates::getInc();
			
			foreach ( $result as $key => $row )
			{
				
				$row['contest_url'] = ABS_URL . 'contest/' . $row['id'];
				
				if ( !empty( $row['contest_thumb'] ) )
				{
					$row['contest_thumb'] = ABS_URL . 'upload/contest_files/' . $row['contest_thumb'];
				}
				
				if ( $row['contest_expire'] < date( "Y-m-d H:i:s" ) )
				{
					$row['contest_expire_text'] = __s( 'contest_finished'], $row['contest_expire'] );
				}
				else
				{
					$row['contest_expire_text'] = __s( 'contest_remain_end'], formatDateRemain( $row['contest_expire'], time() ) );
				}
				
				$row['contest_files'] = '&nbsp;';
				
				if ( $row['contest_type'] == 'demotywator' )
				{
					$row['contest_files'] = __s( 'contest_captions_participation', PD::getInstance()->cnt( "contests_captions", array( 
						'contest_id' => $row['id']
					)) );
				}
				elseif ( $row['contest_type'] != 'normal' )
				{
					$row['contest_files'] = __s( 'contest_items_participation', PD::getInstance()->cnt( IPS__FILES, "date_add > '" . $row['contest_start'] . "'" ) );
				}
				
				$tpl->tplVars = $row;
				
				$result[$key] = $tpl->getLoopTpl( 'contest_small.html' );
				
			}
			
			return implode( '', $result );
		}
		else
		{
			return ips_redirect( 'index.html', 'contest_not_created' );
		}
		
	}
	/**
	 * Display contest
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function showContest( $id )
	{
		$this->contestID = $id;
		
		if ( $this->getContestData() )
		{
			return $this->getContent();
		}
		
		return ips_redirect( 'index.html', 'contest_not_exists' );
	}
	
	/**
	 * Get contest data
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getContestData()
	{
		
		$row = PD::getInstance()->select( 'contests', array(
			'id' => $this->contestID,
			'contest_activ' => 1
		), 1 );
		
		if ( !empty( $row ) )
		{
			$this->sqlRow = $row;
			
			if ( $row['contest_status'] == 1 && $row['contest_expire'] < date( "Y-m-d H:i:s" ) )
			{
				$this->endContest();
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Kończymy konkurs i zapisujemy do bazy
	 * login osoby, która wygrała konkurs
	 * We finish the competition and save the database username of the person who won the competition
	 */
	public function endContest()
	{
		
		$data = array(
			'contest_status' => 0,
			'contest_winner' => 0 
		);
		
		if ( $this->sqlRow['contest_type'] == 'demotywator' )
		{
			$row = PD::getInstance()->select( 'contests_captions', array(
				'contest_id' => $this->contestID
			), 1, null, 'caption_opinion' );
			
			if ( !$this->demotContestEnd( $row ) )
			{
				unset( $row );
			}
		}
		elseif ( $this->sqlRow['contest_type'] != 'normal' )
		{
			
			$field = $this->getOrderField()[$this->sqlRow['contest_type']];
			
			
			$conditions = array(
				'up.id' => 'field:s.upload_id',
				'up.date_add' => array( $this->sqlRow['contest_start'], '>' ),
				'up.date_add' => array( $this->sqlRow['contest_expire'], '<' ),
			)
			
			if ( $this->sqlRow['contest_category_id'] != 0 )
			{
				$conditions[] = array( 
					'category_id' => $this->sqlRow['contest_category_id']
				);
			}
			
			$row   = PD::getInstance()->from( array( 
				IPS__FILES => 'up', 
				'shares' => 's'
			)->setWhere( $conditions )->orderBy( $field )->fields("up.*, s.*")->get( 1 );
		}
		if ( !empty( $row ) )
		{
			$data['contest_winner_id'] = isset( $row['upload_id'] ) ? $row['upload_id'] : $row['id'];
			$data['contest_winner']    = $row['user_login'];
		}
		PD::getInstance()->update( 'contests', $data, array(
			'id' => $this->contestID
		) );
		
		
		$this->sqlRow = PD::getInstance()->select( 'contests', array(
			'id' => $this->contestID
		), 1 );
	}
	/**
	 * Kończymy konkurs na najlepszy
	 * podpis i generujemy image z tym podpisem
	 * We finish the competition for the best caption and generate an image of the signature
	 */
	public function demotContestEnd( $row )
	{
		
		if ( empty( $row ) )
		{
			error_log( "No winning on demoty contest", 0 );
			return false;
		}
		
		$filename = md5( $this->sqlRow['id'] );
		try
		{
			
			$_POST['top_line'] = $row['caption_title'];
			$_POST['bottom_line']  = $row['podpis'];
			require_once( ABS_PATH . '/functions-upload.php' );
			
			$_POST['upload_url'] = ABS_URL . '/upload/contest_files/' . $this->sqlRow['contest_thumb'];
			$upload        = new Upload_Extended();
			$upload->makeFileConfig( 'demotywator', 'image' );
			$upload->uploadFile();
			$file = $upload->Load( IMG_PATH . "/" . $upload->getName() );
			
			$file['upload_type']= 'image';
			$file['font_color'] = Config::getArray( 'upload_demotywator_text', 'font_color' );
			$file['font']       = Config::getArray( 'upload_demotywator_text', 'font' );
			$file['title']      = $row['caption_title'];
			$file['top_line'] = $row['caption_title'];
			$file['bottom_line']  = $row['podpis'];
			
			$upload->configuration( $file );
			$upload->initCreateImage();
			
			
			
			File::deleteFile( IMG_PATH . '/' . $upload->getName() );
			File::deleteFile( $upload->config['up_folder']['large'] . '/' . basename( $upload->getName() ) );
			File::deleteFile( $upload->config['up_folder']['thumb'] . '/' . basename( $upload->getName() ) );
			
			if ( !is_dir( ABS_PATH . '/upload/contest_files' ) )
			{
				File::createDir( ABS_PATH . '/upload/contest_files' );
			}
			
			$file = 'upload/contest_files/' . $filename . '.jpg';
			
			rename( $upload->config['up_folder']['medium'] . '/' . basename( $upload->getName() ), ABS_PATH . '/' . $file );
			
		}
		catch ( Exception $e )
		{
			return false;
		}
		return true;
	}
	public function getContent()
	{
		
		global ${IPS_LNG};
		$contest_winning_file = '';
		if ( $this->sqlRow['contest_status'] == 0 )
		{
			$win = sprintf( ( empty( $this->sqlRow['contest_winner'] ) ? ${IPS_LNG}['contest_awaiting'] : ${IPS_LNG}['contest_winner_is'] ), '<a href="' . ABS_URL . 'profile/' . $this->sqlRow['contest_winner'] . '">' . $this->sqlRow['contest_winner'] . '</a>' );
			
			if ( $this->sqlRow['contest_type'] == 'demotywator' )
			{
				$contest_winning_file = '<img class="contest-win-img" alt="' . $this->sqlRow['contest_title'] . '" src="' . ABS_URL . '/upload/contest_files/' . md5( $this->sqlRow['id'] ) . '.jpg" />';
			}
			elseif ( $this->sqlRow['contest_type'] != 'normal' )
			{
				$row = PD::getInstance()->select( IPS__FILES, array(
					'id' => $this->sqlRow['contest_winner_id']
				), 1 );
				
				if ( empty( $row ) )
				{
					ips_redirect( 'index.html', 'contest_not_exists' );
				}
				
				$display              = new Core_Query();

				$contest_winning_file = '<div class="contest-win-img file-container">' . $display->loadFile( $row, true ) . '</div>';
				
			}
			
			return Templates::getInc()->getTpl( 'contest.html', array(
				'tytul' => $this->sqlRow['contest_title'],
				'contest_expire' => ${IPS_LNG}['contest_finished'] . $this->sqlRow['contest_expire'],
				'contest_description' => nl2br( $this->sqlRow['contest_description'] ),
				'contest_winner_answer' => $win,
				'contest_winning_file' => $contest_winning_file 
			) );
			
		}
		else
		{
			if ( $this->sqlRow['contest_type'] == 'demotywator' )
			{
				$row                  = PD::getInstance()->select( "contests_captions", array( 'contest_id' => $this->contestID ));
				$contest_winning_file = $this->contestPodpisDemota( $row );
			}
			elseif ( $this->sqlRow['contest_type'] != 'normal' )
			{
				$field = $this->getOrderField()[$this->sqlRow['contest_type']];
				
				$conditions = array(
					'up.date_add' => array( $this->sqlRow['contest_start'], '>' ),
					'up.date_add' => array( $this->sqlRow['contest_expire'], '<' ),
				)
				
				if ( $this->sqlRow['contest_category_id'] != 0 )
				{
					$conditions[] = array( 
						'category_id' => $this->sqlRow['contest_category_id']
					);
				}
				
				$row   = PD::getInstance()->from( array( 
					IPS__FILES => 'up', 
					'shares' => 's'
				)->setWhere( $conditions )->orderBy( $field )->fields("up.*, s.*")->get( 1 );
				
				$contest_winning_file = $this->contestShare( $row );
			}
			
			return Templates::getInc()->getTpl( 'contest.html', array(
				'contest_title' => $this->sqlRow['contest_title'],
				'contest_expire' => ${IPS_LNG}['contest_date_end'] . $this->sqlRow['contest_expire'],
				'contest_description' => nl2br( $this->sqlRow['contest_description'] ),
				'contest_winning_file' => $contest_winning_file,
				'contest_winner_answer' => __s( 'contest_remain_end'], formatDateRemain( $this->sqlRow['contest_expire'], time() ) ) 
			) );
		}
		
		
		
		
	}
	
	
	public function contestPodpisDemota( $result )
	{
		
		$participate_in = 'false';
		
		if ( USER_LOGGED )
		{
			$row = PD::getInstance()->select( 'contests_captions', array(
				'contest_id' => $this->contestID,
				'caption_author' => USER_LOGIN
			));
			
			if ( !empty( $row ) )
			{
				$participate_in = 'true';
			}
		}
		
		return Templates::getInc()->getTpl( 'contest_demotivators.html', array(
			'contest_thumb' => ABS_URL . '/upload/contest_files/' . md5( $this->sqlRow['id'] ) . '.jpg',
			'contest_id' => $this->contestID,
			'vote_points' => $this->contestVoting(),
			'contest_captions' => $result,
			'participate_in' => $participate_in,
			'has_ended' => 'false' 
		) );
		
	}
	
	
	/**
	 * Wyświetlanie konkursów Udostępnień|Oceny|Komentarzy
	 * Displaying competitions of disclosures | Rating | Comments
	 * @param array $result - an array of materials to display in the loop
	 * @return mixed - turn prepared the code to display
	 */
	
	public function contestShare( $result )
	{
		
		global ${IPS_LNG};
		$container = '';
		if ( !empty( $result ) )
		{
			
			$sort = ${IPS_LNG}['contest_' . $this->sqlRow['contest_type']];
			
			$container[] = 0;
			foreach ( $result as $row )
			{
				$container[] = array(
					'user_login' => $row['user_login'],
					'link' => seoLink( $row['upload_id'], $row['title'] ),
					'title' => $row['title'],
					'sort' => $row[$this->sqlRow['contest_type']] 
				);
			}
			unset( $container[0] );
			
			$variables = array(
				'sort' => $sort,
				'container' => $container 
			);
			
			$container = Templates::getInc()->getTpl( 'contest_list.html', array(
				'sort' => $sort,
				'container' => $container 
			) );
		}
		else
		{
			$container = '<h3>' . ${IPS_LNG}['contest_no_users'] . '</h3>';
		}
		
		return $container;
	}
	
	public function contestVoting()
	{
		
		$votes_up = $votes_down = 0;
		if ( USER_LOGGED )
		{
			$row = PD::getInstance()->select( 'contests_users', array(
				'contest_id' => $this->contestID,
				'contest_user_id' => USER_ID
			), 1);
			
			$votes_up  = 50;
			$votes_down= 50;
			
			if ( !empty( $row ) )
			{
				$votes_up  = $row['remain_votes_up'];
				$votes_down= $row['remain_votes_down'];
			}
		}
		
		return array(
			'votes_up' =>  $votes_up,
			'votes_down' => $votes_down
		);
	}
	/**
	 * Add caption to demotywator contest 
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function addCaption( $contest_id, $caption, $caption_title )
	{
		$row = PD::getInstance()->select( 'contests_captions', array(
			'contest_id' => $contest_id,
			'caption_author' => USER_LOGIN
		), 1 );
		
		/** Addded caption */
		if( !empty( $row ) )
		{
			throw new Exception( 'contest_blocked_add_more' );
		}
		
		$success = PD::getInstance()->insert( 'contests_captions', array( 
			'caption' => $caption, 
			'caption_title' => $caption_title, 
			'caption_author' => USER_LOGIN, 
			'caption_opinion' => 0,
			'caption_votes' => 0, 
			'contest_id' => IPS_ACTION_GET_ID 
		));
		if( !$success )
		{
			throw new Exception( 'err_unknown' );
		}			
		
		return true;
	}
	/**
	 * Vote for caption
	 *
	 * @param 
	 * 
	 * @return 
	 */	
	public function voteCaption( $caption_id, $contest_id, $vote_type )
	{	
		$contests_vote = PD::getInstance()->select( "contests_voting", array(
			'contest_user_id' => USER_ID,
			'caption_id' => $caption_id,
			'contest_id' => $contest_id
		), 1);
		
		if( !empty( $contests_vote ) )
		{
			throw new Exception( 'contest_voted_before' );
		}
	

		PD::getInstance()->increase( 'contests_captions', array(
			'caption_votes' => '1', 
			'caption_opinion' => ( $vote_type == 'votes_down' ? -1 : 1 )
		), array( 
			'id' => $caption_id
		) );
		
		PD::getInstance()->insert( 'contests_voting', array( 
			'contest_id' => $contest_id,
			'contest_user_id' => USER_ID, 
			'caption_id' => $caption_id
		));
		
		$contests_user = PD::getInstance()->select( 'contests_users', array(
			'contest_id' => $contest_id,
			'contest_user_id' => USER_ID
		), 1);
			
		if( empty( $contests_user ) )
		{
			/** Not voted yet */
			PD::getInstance()->insert( 'contests_users', array(
				'contest_user_id' => USER_ID,
				'contest_id' => $contest_id,
				'remain_votes_up' => ( $vote_type == 'votes_up' ? 49 : 50 ),
				'remain_votes_down' => ( $vote_type == 'votes_down' ? 49 : 50 )
			));
		}
		else
		{
			PD::getInstance()->update( 'contests_users', array(
				'remain_' . $vote_type => $contests_user['remain_' . $vote_type] - 1
			), array(
				'id' => $contests_user['id']
			) );
		}

		return true;
	}
}
function formatDateRemain( $d1, $d2 )
{
	
	$temp = $d1;
	
	$d1 = date_parse( $d1 );
	$d2 = date_parse( date( "Y-m-d H:i:s", $d2 ) );
	
	if ( $d1['second'] >= $d2['second'] )
	{
		$diff['second'] = $d1['second'] - $d2['second'];
	}
	else
	{
		$d1['minute']--;
		$diff['second'] = 60 - $d2['second'] + $d1['second'];
	}
	if ( $d1['minute'] >= $d2['minute'] )
	{
		$diff['minute'] = $d1['minute'] - $d2['minute'];
	}
	else
	{
		$d1['hour']--;
		$diff['minute'] = 60 - $d2['minute'] + $d1['minute'];
	}
	if ( $d1['hour'] >= $d2['hour'] )
	{
		$diff['hour'] = $d1['hour'] - $d2['hour'];
	}
	else
	{
		$d1['day']--;
		$diff['hour'] = 24 - $d2['hour'] + $d1['hour'];
	}
	if ( $d1['day'] >= $d2['day'] )
	{
		$diff['day'] = $d1['day'] - $d2['day'];
	}
	else
	{
		$d1['month']--;
		$diff['day'] = date( "t", $temp ) - $d2['day'] + $d1['day'];
	}
	if ( $d1['month'] >= $d2['month'] )
	{
		$diff['month'] = $d1['month'] - $d2['month'];
	}
	else
	{
		$diff['month'] = 12 - $d2['month'] + $d1['month'];
	}
	
	
	$month = $diff['month'] > 0 ? $diff['month'] . ' m,' : false;
	
	$day = $diff['day'] > 0 ? $diff['day'] . ' d,' : false;
	
	$hour = $diff['hour'] > 0 ? $diff['hour'] . ' h,' : false;
	
	$minute = $diff['minute'] > 0 ? $diff['minute'] . ' min,' : false;
	
	$second = $diff['second'] . ' s';
	
	return $month . ' ' . $day . ' ' . $hour . ' ' . $minute . ' ' . $second;
}

?>
