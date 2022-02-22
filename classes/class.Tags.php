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

class Tags
{
    
    /**
     * We store the material ID
     */
    public $upload_id;
    
    
    /**
     * Store tags for a particular material
     */
    public static $tags_rowfile = false;
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    static function formatTags( $tags )
    {
        self::$tags_rowfile = array();
        if ( !empty( $tags ) )
		{
            $tags = explode( ',', $tags );
            
			foreach ( $tags as $key => $tag )
			{
                self::$tags_rowfile[] = array(
                    'tag' => $tag
                );
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
    static function getFileTags( $id, $links = false )
    {
        $tags = array();
        
        if ( IPS_ACTION != 'file_page' || empty( self::$tags_rowfile ) )
		{
            $upload_tags_post = PD::getInstance()->from( 'upload_tags_post' )->where( 'upload_id', $id )->fields('id_tag')->getQuery();
	
			self::$tags_rowfile = PD::getInstance()->from( 'upload_tags' )->where( 'id_tag', $upload_tags_post, 'IN' )->get();
        }
        
        if ( !empty( self::$tags_rowfile ) ) 
		{
            foreach ( self::$tags_rowfile as $tag )
			{
                $tags[] = ( $links ? '<a href="' . ABS_URL . 'tag/' . Tags::delimiter( $tag['tag'] ) . '">' . $tag['tag'] . '</a>' : $tag['tag'] );
            }
            
			return implode( ', ', $tags );
        }
        
        return false;
    }
    

    
    /**
     * Wyszukiwanie ID materiałów z tagami podobnymi do podanych
     * jako strong lub wyszukując po ID podanego materiału
     * Search ID materials with tags similar to given as strong or searching the ID of a given material
     *
     * @param string $upload_id - Or material ID tags
     * 
     * @return 
     */
    static function getSmilar( $upload_id, $limit_tags = false, $return_id_tag = false )
    {
        /** Search by file ID */
        if ( is_numeric( $upload_id ) )
		{
			//OPTIMIZE-IPS $row = PD::getInstance()->simpleSelect( db_prefix( 'upload_tags', 't' ) . " JOIN " . db_prefix( 'upload_tags_post', 't_r' ) ." ON t_r.id_tag = t.id_tag AND t_r.upload_id = '" . intval($upload_id) . "'");
			$row = PD::getInstance()->from( 'upload_tags t')->join('upload_tags_post t_r' )->on( array(
				't_r.id_tag' => 't.id_tag'
			))->where( 't_r.upload_id', intval( $upload_id ) )->get();
            
            if ( !empty( $row ) )
			{
                return Tags::findByTags( array_column( $row, 'tag' ), $limit_tags, $return_id_tag );
            }
        }
        
        return false;
    }
    
	/**
     * We are in the database smilar tags.
     * The input is an array of words.
     * You can also return an array of words instead of id materials.
     *
     * @param $tags
     * 
     * @return 
     */
    static function findByTags( array $tags, $limit_tags = false, $return_id_tag = false )
    {
        if ( empty( $tags ) )
		{
            return 0;
        }
        
        $tags = implode( '|', array_unique( $tags ) );
        
		$res = PD::getInstance()->from( 'upload_tags' )->where( 'tag', $tags, 'REGEXP' )->fields('id_tag')->limit( $limit_tags )->get();
        
        if ( $res == false )
		{
            return 0;
        }
        
        if( $return_id_tag )
		{
            return array_unique( array_column( $res, 'id_tag' ) );
        }
        
        $res = PD::getInstance()->select( 'upload_tags_post', array( 
			'id_tag', array( array_unique( array_column( $res, 'id_tag' ) ), 'IN' )
		), null, 'upload_id' );
        
        if ( empty( $res ) )
		{
            return 0;
        }
        
        return implode( ',', array_column( $res, 'upload_id' ) );
    }
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function tagFilesCount($text_tag)
    {
        return PD::getInstance()->cnt( 'upload_tags_post', array(
			'id_tag' => "( SELECT id_tag FROM `" . db_prefix( 'upload_tags' ) . "` WHERE tag = '" . $text_tag . "' )"
		));
    }
    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public static function tagFiles( $text_tag, $limit_result = false )
    {
        $res = PD::getInstance()->from( 'upload_tags_post')->where( 
			'id_tag', "( SELECT id_tag FROM `" . db_prefix( 'upload_tags' ) . "` WHERE tag = '" . $text_tag . "' )"
		)->limit( $limit_result )->fields('upload_id')->get();
        
        if ( empty( $res ) )
		{
            return false;
        }

        return array_unique( array_column( $res, 'upload_id' ) );
    }
    

    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    
    public static function delimiter( $tag )
    {
        return preg_replace('/\s+/', '+', trim( $tag ) );
    }
}