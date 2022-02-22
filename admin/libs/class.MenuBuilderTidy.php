<?php
/**
 * Extends menu class and builds menu with readable HTML output.
 *
 * @author   Corey Worrell
 * @homepage http://coreyworrell.com
 * @version  1.0
 */

class MenuBuilderTidy extends MenuBuilder {

	public function __construct(MenuBuilder $menu = null, $only_list_li = false)
	{
		
		$this->only_list_li = $only_list_li;
		if ( ! empty( $menu ) )
		{
			$this->items = $menu->items;
			$this->current = $menu->current;
			$this->attrs = $menu->attrs;
		}
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
		$menu = '';
		if( !$this->only_list_li || $sub_menu )
		{
			$menu = '<ul'. self::attributes($attrs) .'>'."\n".str_repeat("\t", $i - 1);
		}
		foreach ($items as $key => $item)
		{
			$has_children = isset($item['children']);
			
			$class = isset( $item['class'] ) ? array( $item['class'] ) : array();
			
			$has_children ? $class[] = 'parent' : null;
			
			if ( ! empty($current))
			{
				if ($current_class = self::current($current, $item))
				{
					$class[] = $current_class;
				}
			}
			
			$classes = ! empty($class) ? self::attributes(array(
				'class' => implode( ' ', $class ) )
			) : null;

			$menu .= str_repeat("\t", $i).'<li'.$classes.'><a href="'.$item['url'].'"' 
			. ( !empty( $item['title'] ) ? ' title="'.$item['title'].'"' : '' ) 
			. ( !empty( $item['target'] ) && $item['target'] == '_blank' ? ' target="_blank"' : '' ) .'>' 
			. $item['text'] . '</a>' . ( $has_children ? "\n" . str_repeat( "\t", $i + $i ) : null);
			$menu .= $has_children && $item['children']->items !== null ? $this->render( $item['children']->attrs, $current, $item['children']->items, true ) : null;
			
			$menu .= ($has_children ? str_repeat("\t", $i) : null).'</li>'."\n".str_repeat("\t", $i - 1);
		}
		if( !$this->only_list_li || $sub_menu )
		{
			$menu .= str_repeat("\t", $i - 1).'</ul>'."\n".(($i - 2) >= 0 ? str_repeat("\t", $i - 2) : null);
		}
		
		
		$i--;
		
		return $menu;
	}

}