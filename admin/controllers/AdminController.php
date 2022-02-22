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
	
	
	
class AdminController
{
	
	/**
	 * Get default or current action
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function action()
	{
		return has_value( $this->params, 'action', $this->action );
	}
	
	/**
	 * 
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function route( $params )
	{
		$action = $this->action();
		
		if( is_callable( [ $this, $action ] ) )
		{
			return call_user_func_array( [ $this, $action ], array_slice( $this->params, 1 ) );
		}
	}
	
	/**
	 * Set actions params
	 *
	 * @param array $params
	 * 
	 * @return void
	 */
	public function setParams( $params )
	{
		$this->params = $params;
	}
	
	/**
	 * Set $_POST params
	 *
	 * @param array $post
	 * 
	 * @return void
	 */
	public function setPost( $post )
	{
		$this->post = $post;
	}
	
	/**
	 * Set $_POST params
	 *
	 * @param array $post
	 * 
	 * @return void
	 */
	public function setModel( $model )
	{
		$this->model = $model;
	}
	
	public function delete( $id )
	{
		return $this->model->delete( $id );
	}
	
	public function create()
	{
		return $this->model->create( $this->post );
	}
	
	public function edit( $id )
	{
		return $this->model->edit( $id );
	}
	
	public function modify( $id )
	{
		return $this->model->modify( $id, $this->post );
	}
}