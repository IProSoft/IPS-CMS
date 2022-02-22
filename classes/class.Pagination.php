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

class Pagination
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function __construct( )
	{
	}
	
	/* Decydujemy jaki typ paginacji mamy wyświetlić,
	 * można dodać własne style edytując dowolny warunek
	 * CASE.
	 * Decide what type of pagination we show, you can add your own style by editing any condition CASE.
	 * @param $stron specifies the number of pages
	 * @param $strona the current page number
	 * @param $page_url Link to pagination, just add a number to the end
	 * @param $counted_data - defines if records was counted or are loaded by AJAX
	 * To download volume of material to the site- Config::get( 'files_on_page')
	 */
	
	public function getPagin( $current_page, $limit, $page_url, $counted_data = true )
	{
		
		$this->limit        = $limit;
		$this->current_page = $current_page;
		$this->page_url     = $page_url;
		
		$pagin_switch = Config::get( 'pagin_css' );
		
		if ( strpos( $page_url, 'moderator/' ) !== false || strpos( $page_url, 'search/' ) !== false )
		{
			/* Mod normal pagin */
			$pagin_switch = 'demot';
		}
		
		switch ( $pagin_switch )
		{
			//pagination block
			case 'kwejk':
				return $this->blockPagin();
			break;
			
			case 'block':
				return $this->simplePagin();
			break;
			
			//pagination without reloading the page
			case 'infinity':
				return $this->infiniteScroll( $counted_data );
			break;
			
			//pagination with slider
			default:
				return $this->paginator300();
			break;
		}
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function infiniteScroll( $counted_data )
	{
		if ( $counted_data && ( $this->current_page >= $this->limit || $this->limit == 1 ) )
		{
			return '<script type="text/javascript">/** NO PAGES **/</script>' . "\n";
		}
		
		$http_build = array_map( function( &$value )
		{
			if ( $value != '' )
			{
				return str_replace( array(
					'\\',
					'/' 
				), array(
					'',
					'' 
				), $value );
			}
			return $value;
		}, $_GET );
		
		$http_build['page'] = $this->current_page;
		
		return Templates::getInc()->getTpl( 'js_infinity.html', array(
			'next_page' => ( substr( $this->page_url, -1 ) != ',' ? rtrim( $this->page_url, '/' ) . '/' : $this->page_url ) . ( $this->current_page + 1 ),
			'ajax' => ( IPS_VERSION == 'pinestic' ? 'pinit/items/' : 'items/' ) . '?id=' . IPS_ACTION . '&' . http_build_query( $http_build ) 
		) );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function paginator300( )
	{
		
		return '
		<div class="pagination">
			<div class="paginator" id="paginator_3000"></div>
			<script type="text/javascript">
				paginator_3000 = new Paginator(
					"paginator_3000", ' . $this->limit . ', ' . Config::get( 'pagin_css_pages' ) . ', ' . $this->current_page . ', "' . $this->page_url . '"
				);
			</script>
		</div>
		';
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function simplePagin( )
	{
		
		$pagin = '';
		
		if ( $this->current_page > 1 )
		{
			$pagin .= '<li><a href="' . $this->page_url . ( $this->current_page - 1 ) . '" title="" class="previous-page"><span> < </span></a></li><li><a href="' . $this->page_url . '1" title="" class="first-page"><span> << </span></a></li>';
		}
		else
		{
			$pagin .= '<li><div class="previous-page"><span> < </span></div></li>';
		}
		if ( $this->limit <= 10 )
		{
			$pagin .= $this->listElements( 1, $this->limit );
		}
		else if ( $this->limit > 10 )
		{
			$min = $this->current_page - 5;
			$min = $min > 0 ? $min : 1;
			$pagin .= $this->listElements( $min, ( $min + 10 > $this->limit ? $this->limit : $min + 10 ) );
		}
		
		if ( $this->current_page < $this->limit )
		{
			$pagin .= '<li><a href="' . $this->page_url . ( $this->current_page + 1 ) . '" title="" class="next-page"><span> > </span></a></li><li><a href="' . $this->page_url . $this->limit . '" title="" class="last-page"><span> >> </span></a></li>';
		}
		else
		{
			$pagin .= '<li><div class="next-page"><span> > </span></div></li>';
		}
		return '<ul class="pagination-simple">' . $pagin . '</ul>';
		
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function blockPagin( )
	{
		$pagin = '';
		if ( $this->limit <= 10 )
		{
			$pagin .= $this->listElements( 1, $this->limit );
		}
		else if ( $this->limit > 10 )
		{
			$min = $this->current_page;
			$min = $min > 0 ? $min : 1;
			if ( ( $this->limit - 5 ) <= $this->current_page )
			{
				$pagin .= $this->listElements( $min - 3, $min - 1 );
				$pagin .= '<b>...</b>';
				$pagin .= $this->listElements( $min - ( 5 - ( $this->limit - $this->current_page ) ), $min + ( $this->limit - $this->current_page ) );
			}
			elseif ( $min < 3 )
			{
				$min = $min == 2 ? 1 : $min;
				$pagin .= $this->listElements( $min, $min + 4 );
				$pagin .= '<b>...</b>';
				$pagin .= $this->listElements( $min + 5, $min + 7 );
			}
			else
			{
				$pagin .= $this->listElements( $min - 2, $min + 2 );
				$pagin .= '<b>...</b>' . $this->listElements( $min + 3, $min + 5 );
			}
			
		}
		
		if ( $this->current_page > 1 )
		{
			$pagin = '<li><a href="' . $this->page_url . ( $this->current_page - 1 ) . '"><span>&laquo;</span></a></li>' . $pagin;
		}
		if ( $this->current_page < $this->limit )
		{
			$pagin .= '<li><a href="' . $this->page_url . ( $this->current_page + 1 ) . '"><span>&raquo;</span></a></li>';
		}
		
		return '<ul class="pagination-kwejk">' . $pagin . '</ul>';
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	private function listElements( $from_num, $to_num )
	{
		$list = '';
		
		for ( $i = $from_num; $i <= $to_num; $i++ )
		{
			$list .= '<li><a href="' . $this->page_url . $i . '" title="" class="' . ( $i == $this->current_page ? 'currentPage' : '' ) . '"><span>' . $i . '</span></a></li>';
			
		}
		
		return $list;
	}
}
?>