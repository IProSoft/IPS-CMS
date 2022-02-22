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

class Search
{
	
	/* 
	 * minimalna długośc frazy do wyszukiwania 
	 * optymalnia wartość 3 - 4
	 * The minimum length of a phrase to search for optimal value of 3 - 4
	 */
	private $_min = 4;
	
	
	private $phrase = '';
	
	/* 
	 * \ Produce / nice link for the search in the form of /fraza1+fraza2+.../gdzie1+gdzie1+../sortowanie1+sortowanie2+../widok
	 In the search phrase leave only letters and numbers, and then combine with +
	 */
	
	public function rewriteSearch( $search_phrase )
	{
		if ( empty( $search_phrase ) )
		{
			return 'search/';
		}
		
		$string = preg_replace( '/\s+/', '+', trim( $search_phrase ) ) . '/';
		
		$string .= get_input( 'search_place', '+' ) . '/';
		$string .= get_input( 'search_sorting', '+' ) . '/';
		$string .= get_input( 'search_display', '+' ) . '/';
		
		if ( isset( $_POST['search_files'] ) && is_array( $_POST['search_files'] ) )
		{
			$string .= implode( '+', array_keys( $_POST['search_files'] ) );
		}
		
		return 'search/' . $string;
	}
	
	/* 
	 * We begin scanning the database of the whole phrase
	 */
	public function startSearch( $search_phrase )
	{
		$this->phrase = $search_phrase;
		
		$query = PD::getInstance()->from( IPS__FILES . ' up' )->join( 'upload_text t' )->on( 'up.id', 't.upload_id' );
		
		$query = $this->setVariables( $query )->where( 'upload_status', 'public' );
		
		$row = null;
		
		if ( !empty( $this->phrase ) )
		{
			$query = $query->brackets( '(', 'AND');
			
			$this->setPhrases( $query );
			
			$query->brackets( ')' );
			
			$count = $this->getCount();
			
			if( !$count )
			{
				$counted = $query->fields( 'COUNT(*) as counted' )->getOne();
				
				Session::setChild( 'search_count', md5( $this->phrase ), $counted['counted'] );
				
				$query->replaceQuery( 'fields', array( 'up.*,t.long_text' ) );
			}
			
			$row = $query->limit( xy( IPS_ACTION_PAGE, Config::get('files_on_page') ) )->get();
			
		}

		return $this->displaySearchForm() . $this->displayResult( $row );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function setVariables( $query )
	{
		if ( isset( $_GET['search_place'] ) )
		{
			$query = $query->where( 'upload_activ', (int)( $_GET['search_place'] == 'main' )  );
		}
		
		if ( isset( $_GET['search_files'] ) && $_GET['search_files'] != '+' )
		{
			$allow_add = array_keys( Config::getArray( 'upload_type' ) );
			
			$search_files = explode(' ', $_GET['search_files'] );
			
			foreach ( $search_files as $key => $search_file )
			{
				if ( !in_array( $search_file, $allow_add ) )
				{
					unset( $search_files[$key] );
				}
			}
			
			if ( count( $search_files ) > 0 )
			{
				$query = $query->where( 'upload_type', $search_files, 'IN' );
			}
		}
		
		if ( isset( $_GET['search_sorting'] ) && $_GET['search_sorting'] != '+' && strpos( $_GET['search_sorting'], '-' ) !== false )
		{
			list( $field, $direction ) = explode( '-', $_GET['search_sorting'] );
			
			if ( $field == 'votes_opinion' || $field == 'date_add' )
			{
				$query = $query->orderBy( $field, ( strtolower( $direction ) == 'asc' ? 'ASC' : 'DESC' ) );
			}
		}
		
		return $query;
	}
	
		
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function setPhrases( $query )
	{
		
		$phrases = explode(" ", $this->phrase );
		
		foreach ( $phrases as $key => $value )
		{
			if ( strlen( $phrases[$key] ) < $this->_min )
			{
				unset( $phrases[ $key ] );
			}
		}
		 
		foreach( $phrases as $phrase )
		{
			$query->orWhere( "CONCAT(' ', title, top_line, bottom_line, ' ' )", $phrase, 'LIKE' );
			$query->orWhere( 'long_text', $phrase, 'LIKE');
		}
		
		return $query;
		
		/* var_dump($concat);exit;
		return $this->concatPhrases( $phrases, 'title', true ) 
		. $this->concatPhrases( $phrases, 'top_line' )
		. $this->concatPhrases( $phrases, 'bottom_line' )
		. $this->concatPhrases( $phrases, 'long_text' ); */
		
	}

	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	/* private function concatPhrases( $phrases, $column, $start = false )
	{
		
		$search = !$start ? ' OR ' : '';
		
		$search .= "`" . $column . "` LIKE LOWER('%" . implode("%') OR `" . $column . "` LIKE LOWER('%", $phrases) . "%')";
		$search .= " OR `" . $column . "` LIKE LOWER('%" . implode("') OR `" . $column . "` LIKE LOWER('%", $phrases) . "')";
		$search .= " OR `" . $column . "` LIKE LOWER('" . implode("%') OR `" . $column . "` LIKE LOWER('", $phrases) . "%') ";
		
		return $search;
	} */
	/* 
	 * The final function displays sophisticated materials
	 */
	private function displayResult($row)
	{
		
		$display = new Core_Query();
		
		if ( !empty( $row ) )
		{
			$display->files = $row;
			
			$display->args['widget_layout'] = 'one';
			
			if ( $_GET['search_display'] == 'small' )
			{
				/** Small results */
				$display->args['widget_layout'] = 'three';
				
				App::$base_args['body_class'] = str_replace(array(
					'one_columns',
					'two_columns',
					'three_columns'
				), '', App::$base_args['body_class']) . ' three_columns';
			}
			
			$paginator = '/search/' . $_GET['phrase'] . '/' . $_GET['search_place'] . '/' . $_GET['search_sorting'] . '/' . $_GET['search_display'] . '/' . (!empty($_GET['search_files']) ? $_GET['search_files'] : '+') . '/';
			
			$display->setPaginator($display->paginator( ceil( $this->getCount() / Config::get('files_on_page') ), str_replace(' ', '+', $paginator)), 'search');
			
			return $display->displayData('search');
			
		}
		
		return ;
	}

	
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function displaySearchForm()
	{
		
		$data = array(
			'search_phrase' => $this->phrase,
			'search_results_count' => '_'
		);
		
		if ( !empty( $this->phrase ) )
		{
			$data['search_results_count'] = __s( 'search_results_found', (int)$this->getCount() );
		}
		
		return Templates::getInc()->getTpl('search.html', $data );
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function getCount()
	{
		return Session::getChild( 'search_count', md5( $this->phrase ), 0 );
	}
}
?>