<?php 

class fileEdit{


	private $textarea_form;
	private $accepted_ext=array();
	private $textarea_style;
	private $folder;
	private $href_file;
	private $get_var;
	private $show_like;
	private $style;
	private $display_types = array("select", "link", "text_only", "list", "list_link");


	private function is_empty_dir($dir){
		if ($dh = @opendir($dir))
		{
			closedir($dh);
			return true;
		}
		else
		{
			return false;
		}
	}

	private function listFilesInDirectory( $folder ) {
		
		$it = new RecursiveDirectoryIterator( $folder );
		$tpl = '';
		foreach( $it as $file )
		{
			if( ! $it->isDir() )
			{
				$tpl .= '<option value="'.basename($file).'" >'.basename($file).'</option>';
			}
		}
		return $tpl;
	}
	public function showFiles( $folder ){

		$tpl = $this->listFilesInDirectory( $folder );
		
	return '
		<form method="get" action="" >
		<select name="editfiles" onchange="edycjaZmienPlik(this);">
		<option value="" disabled="disabled" selected="selected">Plik:</option>
		'.$tpl.'
		</select>
		</form>';
	
	}
	public function editFile( $file ){

		$found = $this->findFile( $file );

		if( $found )
		{
			$f = fopen($found, 'r');
			$content = fread($f, filesize($found));
			
			//$content = file_get_contents( $this->findFile( $found ) );
			return $content;
		}
	}
	public function saveFile( $file, $contents ){
		
		if( !empty($file) && !empty($contents) )
		{
			$file = $this->findFile( $file );
			if( $file )
			{
				$f = fopen($file, 'w+');
				if ( $f !== false )
				{
					
					
					if( substr( $contents, 0, 3 ) == "\xef\xbb\xbf" )
					{
						$contents = substr( $contents, 3 );
					}
					fwrite( $f, utf8_encode( stripslashes( str_replace('\"', '"', $contents) ) ) );
					fclose($f);
					return true;
				}
				
				/* if( file_put_contents( $file, $contents ) )
				{
					return true;
				} */
			}
		}
		return false;
	}
	public function findFile( $file ){
	
		if( file_exists( ABS_TPL_PATH . '/' . $file ) )
		{
			return ABS_TPL_PATH . '/' . $file;
		}
		elseif( file_exists( ABS_TPL_PATH . '/css/' . $file ) )
		{
			return ABS_TPL_PATH . '/css/' . $file;
		}
		elseif( file_exists( ABS_PATH . '/css/' . $file ) )
		{
			return ABS_PATH . '/css/' . $file;
		}
		return false;
	}
}
?>