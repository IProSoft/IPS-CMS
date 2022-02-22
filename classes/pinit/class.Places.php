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

class Places{
	
	public function get( $action, $info = false )
	{
		if( is_callable( array( $this, $action ) ) )
		{
			return $this->$action( $info );
		}
	}
	
	public function create( $info )
	{
		$db = PD::getInstance();
		
		if( isset( $info['upload_image'] ) )
		{
			if( file_exists( IPS_TMP_FILES . '/' . $info['upload_image'] ) )
			{
				$info['upload_image'] = IPS_TMP_FILES . '/' . $info['upload_image'];
			}
			elseif( file_exists( IMG_PATH_LARGE . '/' . $info['upload_image'] ) )
			{
				$info['upload_image'] = IMG_PATH_LARGE . '/' . $info['upload_image'];
			}
			else
			{
				throw new Exception('Niestety wystąpił błąd z pobieranym obrazem');
			}
			
			$place_info = $this->createPlace( json_decode( $info['place_data'], true ) );
			
			if( $place_info )
			{
				if( Config::getArray( 'apps_google_maps_customize', 'disallow_multiple_markers' ) )
				{
					$places = $this->getPlaces( array(
						'pinit_places_pins.p_id' => $place_info['p_id'],
						'pinit_places_pins.board_id' => $info['pin_board_id']
					) );

					if( !empty( $places ) )
					{
						/** name as js_already_on_map */
						throw new Exception( 'To miejsce zostało już umieszczone na tej mapie' );
					}
				}
				
				$info['pin_from_url'] = $place_info['url'];
				$info['pin_has_place'] = true;
				
				$pin = new Pin();
				
				$response = $pin->create( $info );
				
				if( $response )
				{

					$place = $db->insert( 'pinit_places_pins', array(
						'p_id' => $place_info['p_id'],
						'pin_id' => $response['pin_id'],
						'board_id' => $info['pin_board_id']
					), 1 );
						
					$pin_info = $pin->getPin( $response['pin_id'] );
					$pin_info['upload_image_size']= $pin->getPinImagesSizes( $pin_info['upload_sizes'], 'medium_thumb' );
					$pin_info = array( array_merge( $place_info, $pin_info ) );
					
					return array(
						'modal_call' => 'maps_append_pin',
						'pin' => $pin->displayFiles( $pin_info, 'compile' )
					);
				}
				elseif( isset( $place_info['created'] ) )
				{
					$this->delete( $place_info['p_id'] );
				}
			}
			else
			{
				throw new Exception('Niestety wystąpił błąd z parametrami dodawanej lokalizacji');
			}
		}
		
		throw new Exception('Niestety wystąpił błąd z parametrami pliku');
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function delete( $p_id )
	{
		return PD::getInstance()->delete( 'pinit_places', array(
			'p_id' => $p_id
		));
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function pins( $place_id )
	{
		if( is_array( $place_id ) )
		{
			$place_id = ( isset( $place_id['place_id'] ) ? $place_id['place_id'] : false );
		}
		
		if( $place_id )
		{
			/** Cache for pin list */
			$pin_ids = $this->getPlaces( array(
				'pinit_places.place_id' => $place_id
			) );
			
			if( $pin_ids )
			{
				$pin_ids = array_column( $pin_ids, 'pin_id' );

				$pin = new Pin();
				
				$pins = $pin->getPins( array(
					'id' => $pin_ids,
				), '*', 50 );
				
				if( !empty( $pins ) )
				{
					foreach( $pins as $key => $item )
					{
						$pins[ $key ]['upload_image_url'] = ips_img( $item['upload_image'], 'medium' );
					}
					
					return $pins;
					
				}
			}
		}
		
		return array();
	}
	
	/**
	* 
	*
	* @param 
	* 
	* @return 
	*/
	public function createPlace( $data )
	{
		$db = PD::getInstance();
		
		$place = $db->select('pinit_places', array(
			'place_id' => $data['place_id']
		), 1 );
		
		if( !empty( $place ) )
		{
			return array_merge( $place, json_decode( $place['place_data'], true ) );
		}
		
		$insert = array(
			'place_id' => $data['place_id'],
			'place_longitude' => $data['geometry']['location']['longitude'],
			'place_latitude' => $data['geometry']['location']['latitude'],
			'place_name' => $data['formatted_address'],
			'place_data' => json_encode( (object)$data ),
		);
	
		$place_id = PD::getInstance()->insert( 'pinit_places', $insert );
		
		if( $place_id )
		{
			return array_merge( $data, $insert, array( 
				'p_id' => $place_id,
				'created' => true
			) );
		}
		
		return false;
	}
	
	public function getPlaces( $data )
	{

		$places = PD::getInstance()->from( 'pinit_places_pins p')->join( 'pinit_places' )->on('p.p_id', 'pinit_places.p_id')->setWhere( $data )->get();

		if( $places )
		{
			return $places;
		}
		
		return array();
	
	}
	
	
}
?>