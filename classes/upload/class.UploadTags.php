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

class Upload_Tags extends Tags
{
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function __construct($upload_id = null)
    {
        $this->upload_id = $upload_id;
    }
	 /**
     * Cleaning tags with unnecessary characters and replace lowercase
     *
     * @param string $tag
     * 
     * @return string
     */
    public function getCensored()
    {
		if( !isset( $this->words ) )
		{
			$this->words = array_map( 'strtolower', array_column( (array)Config::getArray( 'censored_words' ), 'word' ) );
		}
		
		return $this->words;
	}
    /**
     * Cleaning tags with unnecessary characters and replace lowercase
     *
     * @param string $tag
     * 
     * @return string
     */
    public function prepare( $tag )
    {
		if ( $tag == null || strlen( $tag ) < (int)Config::get( 'upload_tags_options', 'length' ) || is_numeric( $tag ) )
		{
            return false;
        }
        
        $tag = mb_strtolower( $tag, 'UTF-8' );
        
		$blocked = explode( '|', Config::getArray( 'upload_tags_options', 'blocked' ) );
		
		if ( !empty( $blocked ) && in_array( $tag, $blocked ) )
		{
            return false;
        }

		if ( in_array( strtolower( $tag ), $this->getCensored() ) )
		{
            return false;
        }
		
        return $tag;
    }
    /**
     * Short feature adds an entry to the database
     *
     * @param $id_tag
     * 
     * @return void
     */
    public function assign( $id_tag )
    {
        if ( !empty( $id_tag ) )
		{
            return PD::getInstance()->insert( 'upload_tags_post', array(
                'upload_id' => $this->upload_id,
                'id_tag' => $id_tag
            ));
        }
		
		return false;
    }
    /**
     * 
     Returns the ID tag or false if there is no such database
     *
     * @param $tag
     * 
     * @return bool|int
     */
    public function getTagId( $tag )
    {
        $res = PD::getInstance()->from( 'upload_tags' )->where( 'tag', $tag )->fields('id_tag')->getOne();
        
        if ( isset( $res['id_tag'] ) )
		{
            return $res['id_tag'];
        }
		
        return false;
    }
    
    
    /**
     * Add a new tag to the base
     * Please ID or False
     *
     * @param  $tag
     * 
     * @return string|bool
     */
    public function create( $tag )
    {
        $tag = $this->prepare( $tag );
        
        if ( !$tag )
		{
            return false;
        }
        
        $id = $this->getTagId( $tag );
        
        if ( $id != false )
		{
            return $id;
        }
        
        return PD::getInstance()->insert( 'upload_tags', array(
            'tag' => $tag
        ));
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function saveTags( $tags, $upload_id = false )
    {
		if( $upload_id )
		{
			$this->upload_id = $upload_id;
		}
		
		if ( !empty( $tags ) )
		{
			$tags = array_map( 'trim', array_unique( explode( ',', $tags ) ) );
			
			if ( count( $tags ) > 0 )
			{
				$res = PD::getInstance()->select( 'upload_tags', array(
					'tag' => array( $tags, 'IN' )
				));
				
				if ( !empty( $res ) )
				{
					foreach( $res as $tag )
					{
						if ( in_array( $tag['tag'], $tags ) )
						{
							$this->assign( $tag['id_tag'] );
							unset( $tags[ array_search( $tag['tag'], $tags ) ] );
						}
					}
				}

				foreach( $tags as $k => $tag )
				{
					if ( !empty( $tag ) )
					{
						$this->assign( $this->create( $tag ) );
					}
				}

				unset( $tags, $res );
			}
		}
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function deleteTag( $tag_id )
    {
        PD::getInstance()->delete( 'upload_tags', array(
			'id_tag' => $tag_id
		));
        PD::getInstance()->delete('upload_tags_post', array(
			'id_tag' => $tag_id
		));
    }
    /**
     * Ajax called function while upload
     *
     * @param 
     * 
     * @return 
     */
	public function ajax_autocomplete( $data )
	{
		$tags = ['results' => []];
		
		if( isset( $data['q'] ) )
		{
			$res = PD::getInstance()->from( 'upload_tags' )
					->where( 'tag', $data['q'], 'R-LIKE' )
					->fields('tag')
					->limit( 10 )
					->get();
			
			if( !empty( $res ) )
			{
				foreach( $res as $tag )
				{
					$tags['results'][] = [
						'id' => $tag['tag'],
						'text' => $tag['tag']
					];
				}
			}
		}
		
		return $tags;
	}
	
}