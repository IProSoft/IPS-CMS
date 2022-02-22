<?php
/**
 * Menu Builder
 *
 * This class can be used to easily build out a menu in the form
 * of an unordered list. You can add any attributes you'd like to
 * the main list, and each list item has special classes to help
 * you style it. You can also set the current active item.
 *
 * @author   Corey Worrell
 * @homepage http://coreyworrell.com
 * @version  1.0
 */

class MenuBuilder {

	// Associative array of list items
	public $items = array();
	
	// Associative array of attributes for list
	public $attrs = array();
	
	// Current active URL
	public $current;
	
	public $only_list_li = false;
	/**
	 * Creates and returns a new menu object
	 *
	 * @chainable
	 * @param   array   Array of list items (instead of using add() method)
	 * @return  menu
	 */
	public static function factory(array $items = null)
	{
		return new MenuBuilder( $items );
	}
	
	/**
	 * Constructor, globally sets $items array
	 *
	 * @param   array   Array of list items (instead of using add() method)
	 * @return  void
	 */
	public function __construct(array $items = null, $only_list_li = false)
	{
		$this->only_list_li = $only_list_li;
		$this->items = $items;
	}
	
	/**
	 * Add's a new list item to the menu
	 *
	 * @chainable
	 * @param   string   Title of link
	 * @param   string   URL (address) of link
	 * @param   menu     Instance of class that contain children
	 * @return  menu
	 */
	public function add( $text, $title, $url, $class = null, $target = null, MenuBuilder $children = null )
	{
		$this->items[] = array
		(
			'text'     => $text,
			'class'     => $class,
			'title'    => $title,
			'url'      => $url,
			'target'   => $target,
			'children' => is_object($children) ? $children : null,
		);
		
		return $this;
	}
	
	/**
	 * Renders the HTML output for the menu
	 *
	 * @param   array   Associative array of html attributes
	 * @param   array   Associative array containing the key and value of current url
	 * @param   array   The parent item's array, only used internally
	 * @return  string  HTML unordered list
	 */
	public function render(array $attrs = null, $current = null, array $items = null, $sub_menu = false)
	{
		static $i;
		
		$items = empty($items) ? $this->items : $items;
		$current = empty($current) ? $this->current : $current;
		$attrs = empty($attrs) ? $this->attrs : $attrs;
		
		$i++;
		if( !$this->only_list_li || $sub_menu )
		{
			$menu = '<ul'. self::attributes( $attrs ) .'>';
		}
		foreach ($items as $key => $item)
		{
			$has_children = isset($item['children']);
			
			$class = isset( $item['class'] ) ? array( $item['class'] ) : array();
			
			$has_children ? $class[] = 'parent' : null;
			
			if ( ! empty( $current ) )
			{
				if ( $current_class = self::current($current, $item) )
				{
					$class[] = $current_class;
				}
			}
			
			$classes = ! empty($class) ? self::attributes( array('class' => implode(' ', $class)) ) : null;
			
			$menu .= '<li'.$classes.'><a href="'.$item['url'].'"' 
			. ( !empty( $item['title'] ) ? ' title="' . $item['title'] . '"' : '' )  
			. ( !empty( $item['target'] ) && $item['target'] == '_blank' ? ' target="_blank"' : '' ) .'>' 
			. $item['text'] . '</a>';
			
			$menu .= $has_children ? $this->render( $item['children']->attrs, $current, $item['children']->items, true ) : null;
			$menu .= '</li>';
		}
		if( !$this->only_list_li || $sub_menu )
		{
			$menu .= '</ul>';
		}
		$i--;
		
		return $menu;
	}
	
	/**
	 * Renders the HTML output for menu without any attributes or active item
	 *
	 * @return   string
	 */
	public function __toString()
	{
		return $this->render();
	}
	
	/**
	 * Easily set the current url, or list attributes
	 *
	 * @param   mixed   Value to set to
	 * @return  void
	 */
	public function __set($key, $value)
	{
		$this->attrs[$key] = $value;
	}
	
	/**
	 * Get the current url or a list attribute
	 *
	 * @return   mixed   Value of key
	 */
	public function __get($key)
	{
		if (isset($this->attrs[$key]))
			return $this->attrs[$key];
	}
	
	/**
	 * Nicely outputs contents of $this->items for debugging info
	 *
	 * @return   string
	 */
	public function debug()
	{
		return '<pre>'.print_r($this->items, TRUE).'</pre>';
	}
	
	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * @param   string|array  array of attributes
	 * @return  string
	 */
	protected static function attributes($attrs)
	{
		if (empty($attrs))
			return '';

		if (is_string($attrs))
			return ' '.$attrs;

		$compiled = '';
		foreach ($attrs as $key => $val)
		{
			$compiled .= ' '.$key.'="'.htmlspecialchars($val).'"';
		}

		return $compiled;
	}
	
	/**
	 * Figures out if items are parents of the active item.
	 *
	 * @param   array   The current url array (key, match)
	 * @param   array   The array to check against
	 * @return  bool
	 */
	protected static function current($current, array $item)
	{
		if ($current === $item['url'])
			return 'active current';
			
		else
		{
			if (self::active($item, $current, 'url'))
				return 'active';
		}
		
		return '';
	}
	
	/**
	 * Recursive function to check if active item is child of parent item
	 *
	 * @param   array   The list item
	 * @param   string  The current active item
	 * @param   string  Key to match current against
	 * @return  bool
	 */
	public static function active($array, $value, $key)
	{
		foreach ($array as $val)
		{
			if (is_array($val))
			{
				if (self::active($val, $value, $key))
					return TRUE;
			}
			else
			{
				if ($array[$key] === $value)
					return TRUE;
			}
		}
		
		return FALSE;
	}

}