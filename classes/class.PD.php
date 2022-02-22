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

final class PD extends PDO
{
    
    public $error = array();
    
    public $q = array();
    
    public $query_count = 0;
    
    public $bind;
	
	 public $lastQuery;
    
    const _QUOTE_TABLE = 1, _QUOTE_COL = 2, _QUOTE_BOTH = 3, _QUOTE_NONE = 0;
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    private static $instance = false;
   
    /**
     * PD class constructor
     * @param null
     * 
     * Użycie wartości liczbowej zamiast stałych spowodowane błedem Undefined Constant
     * Using a numerical value instead of permanent caused mistake I made Undefined Constant
     */
    public function __construct($install = false)
    {
        
        $dns     = sprintf('mysql:host=%1$s;port=%2$s;dbname=%3$s', DB_HOST, DB_PORT, DB_NAME);
        $options = array(
            12 => true,
            PDO::ATTR_PERSISTENT => false,
            3 => 2,
            //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            1002 => "SET NAMES utf8",
            //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            1005 => true
            //PDO::MYSQL_ATTR_FOUND_ROWS => true
        );
        
        try {
            
			parent::__construct($dns, DB_USER, DB_PSSWD, $options);
            parent::setAttribute(1002, true);
            self::$instance = $this;
            
			return self::$instance;
        }
        catch (PDOException $e) {
            
            $this->error[] = $e->getMessage();
            if ( function_exists('ips_log') )
			{
                ips_log( $this->error );
            }
            
			if ($install)
			{
                return false;
            }
			
			return $this->__construct();
            
			die( 'MySQL DB Error' );
        }
        
    }
    
    /**
     * Pobieranie instancji klasy PD
     * Downloading instance PD
     * Obiekt zostanie utworzony przy pierwszym wywołaniu tej metody
     * lub zostanie zwrócony obekt już istniejący
     * The object is created the first time you call this method and will be returned Obekt existing
     */
    public static function getInstance()
    {
        if (!self::$instance)
		{
            self::$instance = new PD();
        }
        
        return self::$instance;
    }

    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    private function filter( $table, $info )
    {
        
        $sql = 'DESCRIBE ' . $table . ';';
        
        if ( ( $list = $this->PDQuery($sql) ) !== false )
		{
            $fields = array();
            foreach ( $list as $record )
			{
                $fields[] = $record['Field'];
            }
            return array_values( array_intersect( $fields, array_keys( $info ) ) );
        }
        
        return array();
    }
    
    /**
     * Setting the data type
     * @param $bind - data to bind sql query
     */
    private function safe($bind)
    {
        if ( !is_array( $bind ) )
		{
            return !empty( $bind ) ? array(
                $bind
            ) : array();
        }
        return $bind;
    }
	
    /**
     * Prefix each table
     * @param $table - table
     */
	public function prefix( $table )
	{
		if( is_array( $table ) )
		{
			foreach( $table as $name => $prefix )
			{
				$table[$name] = ( in_int( $name ) ? db_prefix( $name ) : db_prefix( $name, $prefix ) );
			}
			return implode( ',', $table );
		}
		else
		{
			return db_prefix( $table );
		}
	}

    /**
     * Counting records in the database
     * @param $table - name of table to get data
     * @param $conditions - sql condition
     * @param $inner - inner join with sql condition
     */
    public function cnt( $table, $conditions = null )
    {
        
        $sql = $this->PDQuery( "SELECT COUNT(*) AS `counted` FROM " . $this->prefix( $table ) . $this->buildWhere( $conditions ) );
        
        if ( isset( $sql[0]['counted'] ) )
		{
            return intval( $sql[0]['counted'] );
        }
        
        return false;
    }
    
    /**
     * Delete rows of the table or several tables provided in the $table
     * @param $table - name(s) of table to delete content
     * @param $conditions - sql condition
     */
    public function delete( $table, $conditions, $limit = false )
    {
        
        $conditions = $this->buildWhere( $conditions );
        
        if ( !is_array( $table ) )
		{
            $table = array(
                $table
            );
        }
        
        foreach ( $table as $key => $t )
		{
            $table[$key] = 'DELETE FROM ' . $this->prefix( $t ) . $conditions . ( $limit ? ' LIMIT ' . $limit : '' );
        }
        
        return $this->PDQuery( implode(';', $table ) );
    }
    /**
     * Pobieranie wiersza( zestawu wierszy) uproszczone do minimum dla lepszej wydajności
     * Download row (set of rows) simplified to a minimum for better performance
     * 
     * @param $table - the name of the table from which data will be collected
     * @param $conditions - query condition
     * @param $limit - download limit lines
     * @param $fields - table field
     * 
     */
    public function select( $table, $conditions = null, $limit = null, $fields = null, $orderBy = null )
    {
		$query = $this->from( $table )->fields( ( empty( $fields ) ? '*' : $fields ) )->orderBy( $orderBy )->setWhere( $conditions )->get( $limit );
		
        if ( $limit == 1 && count( $query ) == 1 )
		{
            return $query[0];
        }
        
        return $query;
    }

    
    /**
     * Adding data to a sql table
     * @param $table - name of table to insert data
     * @param $info - array of data to insert
     */
    public function insert($table, $info)
    {
        $table = $this->prefix( $table );
		
        $fields = $this->filter( $table, $info );
        
        $bind = array();
        
        foreach ( $fields as $field_name )
		{
            $bind[':' . $field_name] = $info[$field_name];
        }
        
        return $this->PDQuery('INSERT INTO ' . $table . ' (' . implode($fields, ', ') . ') VALUES (:' . implode( $fields, ', :' ) . ');', $bind );
    }
	
	/**
	 * Insert into table with update duplicate
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function insertOn( $table, array $fileds )
	{
		$query = 'INSERT INTO ' . $this->prefix( $table ) . ' ( ' . implode( ', ', array_keys( $fileds ) ).' ) VALUES (' . implode( ', ', $fileds ).') ON DUPLICATE KEY UPDATE ';
		
		array_walk( $fileds, function( &$v, $k ){
			$v = '`' . $k . '` = ' . $v;
		});

		$query .= implode( ', ', $fileds ) . ';';
		
		return $this->PDQuery( $query );
	}
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function update( $table, $info, $conditions = '', $limit = 1 )
    {
        
        if ( is_array( $table ) )
		{
            foreach ( $table as $i => $tab )
			{
                $this->update( $tab, $info, $conditions, $limit );
            }
            return true;
        }
        $bind = array();
		
		$table = $this->prefix( $table );
		
        $fields = $this->filter( $table, $info );
        
        $filtered = array();
       
		foreach ( $fields as $key => $field )
		{
            $filtered[$field] = $field . ' = :update_' . $field;
        }
        
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $filtered ) . $this->buildWhere( $conditions ) . $this->limitResults( $limit );
        
        foreach ( $filtered as $field_name => $field_bind )
		{
            $bind[':update_' . $field_name] = $info[$field_name];
        }
        
        return $this->PDQuery( $sql, $bind );
    }
    
    /**
     * Adding data to a sql table
     * @param $table - name of table to insert data
     * @param $info - array of data to insert
     */
    public function insertUpdate( $table, $info )
    {
        $table = $this->prefix( $table );
		
        $fields = $this->filter( $table, $info );
        
        $bind = array();
        
        foreach ( $fields as $key => $field_name )
		{
            $bind[':' . $field_name] = $info[$field_name];
            $info[$field_name]       = $field_name . ' = :' . $field_name;
        }
        
        return $this->PDQuery('INSERT INTO ' . $table . ' (' . implode( $fields, ', ' ) . ') VALUES (:' . implode( $fields, ', :') . ') ON DUPLICATE KEY UPDATE ' . implode( $info, ', ' ) . '', $bind );
    }

    
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function increase( $table, $fields, $conditions = '' )
    {
        $table = $this->prefix( $table );
		
        foreach ( $fields as $column => $val )
		{
            $fields[$column] = '`' . $column . '` = `' . $column . '` + ' . $val;
        }
        
        return $this->PDQuery('UPDATE `' . $table . '` SET ' . implode(', ', $fields ) . $this->buildWhere( $conditions ) );
        
    }

    /**
     * Select mor random elements from table
     *
     * @param 
     * 
     * @return 
     */
    public function optRand($table, $conditions = null, $limit = 1)
    {
        $table = $this->prefix( $table );
		
		$conditions = $this->buildWhere( $conditions );
        
       /*  $sql = "SELECT  *
		FROM    (
			SELECT  @cnt := COUNT(*) + 1,
					@lim := " . $limit . "
			FROM	" . $table . " " . $conditions . "
			) vars
		STRAIGHT_JOIN
			(
			SELECT  r.*,
					@lim := @lim - 1
			FROM    " . $table . " AS r 
				" . (!empty($conditions) ? $conditions . ' AND' : 'WHERE ') . " (@cnt := @cnt - 1)
					AND RAND(" . rand(1, 100000) . ") < @lim / @cnt
			) AS i"; */
        
		$sql = $this->PDQuery('SELECT *
			FROM ' . $table . ' AS tmp_1 JOIN
			   (SELECT CEIL(RAND() *
					( SELECT MAX(id) FROM ' . $table . ' ' . ( !empty( $conditions ) ? $conditions : '' ) . ' )) AS max_field )
				AS tmp_2
		' . ( !empty( $conditions ) ? $conditions . ' AND' : 'WHERE' ) . ' tmp_1.id >= tmp_2.max_field
		 ORDER BY tmp_1.id ASC
		 LIMIT ' . $limit . ';');
        
        if ( count($sql) == 1 && $limit == 1 )
		{
            return $sql[0];
        }
        
        return $sql;
    }
	/**
     * Table truncate
     * @param $table
     */
    public function truncate( $table )
    {
		return $this->PDQuery('TRUNCATE table ' . $this->prefix( $table ) . ';');
	}
	/**
     * query execution
     * @param $query - query to execute in database
     * @param $bind - data to bind with sql query
     */
	public function query( $query, $bind = '' )
    {
		return $this->PDQuery( $query, $bind );
	}
    public function PDQuery( $query, $bind = '' )
    {
        
        if (IPS_DEBUG) {
            $this->startTimer();
        }
        
        $this->lastQuery = trim( $query );
        
        $this->bind = $this->safe($bind);
		
        try {
            
            $stmt = $this->prepare( $this->lastQuery );
           
		   if ( $stmt->execute( $this->bind ) !== false )
			{
                if ( IPS_DEBUG )
				{
                    $this->query_count++;
                    $this->queries[$this->query_count] = array(
                        'count' => $this->query_count,
                        'sql' => $this->lastQuery
                    );
                }
                
                
                //file_put_contents( ABS_PATH . '/zapytania.txt', $this->query_count . $this->lastQuery . "\n", FILE_APPEND | LOCK_EX);
                //$stmt->debugDumpParams();
                
                if (preg_match('/^(select|describe|pragma|show)/i', $this->lastQuery))
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                elseif (preg_match('/^(delete|update)/i', $this->lastQuery))
                    $result = $stmt->rowCount();
                elseif (preg_match('/^(insert)/i', $this->lastQuery))
                    $result = $this->lastInsertId();
                else
                    $result = true; //TODO
            
				if ( IPS_DEBUG )
				{
                    $this->queries[$this->query_count]['time']  = $this->stopTimer();
                    $this->queries[$this->query_count]['class'] = $this->callingClass();
                }
                
				unset($stmt);
                
                return $result;
            }
        }
        catch (PDOException $e) {
            
			$info          = debug_backtrace();

            if ( isset( $info[2]['file'] ) )
			{
                $_SERVER['QUERY_IPS'] = 'In file: ' . $info[2]['file'] . ' , line: ' . $info[2]['line'];
            }
			$this->error[] = array(
                'message' => $e->getMessage(),
				'file' => ips_backtrace(),
                'query' => $this->lastQuery
            );
            
            ips_log( $e->getMessage() . "\n" . $this->lastQuery . (isset($_SERVER['HTTP_REFERER']) ? "\n" . $_SERVER['HTTP_REFERER'] : '') . (isset($_SERVER['SCRIPT_FILENAME']) ? "\n" . $_SERVER['SCRIPT_FILENAME'] : '') . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '') . (isset($_SERVER['QUERY_IPS']) ? "\n" . $_SERVER['QUERY_IPS'] : ''));
            
            return false;
        }
    }
   
   

    
    
   /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function reset()
    {
		$this->q         = array(
			'sql'		 => 'SELECT',
			'from'		 => array(),
			'use'		 => array(),
			'conditions' => array(),
			'join'		 => '',
			'clause'	 => '',
			'fields'	 => array(),
		);
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function from( $tables )
    {
		$this->reset();
		
		if( !is_array( $tables ) )
		{
			$tables = array(
				$tables
			);
		}

		foreach( $tables as $k => $v )
		{
			$this->q['from'][] = !is_numeric( $k ) ? db_prefix( $k, $v ) : db_prefix( $v );
		}
        
		$this->q['from'] = implode( ',', $this->q['from'] );
        
		return $this;
    }
	/**
     * This method allows you to concatenate joins for the final SQL statement.
     *
     * @uses $db->join('table1', 'field1 <> field2', 'LEFT')
     *
     * @param string $table The name of the table.
     * @param string $joinType 'LEFT', 'INNER' etc.
     *
     * @return db
     */
	public function join( $table , $joinType = 'LEFT'  )
	{
        
		$this->q['join'] .= ' ' . strtoupper( trim( $joinType) ) . ' JOIN ' . db_prefix( $table ) . " ON ";
		
		return $this;
    }
	/**
     * This method allows you to concatenate joins for the final SQL statement.
     *
     *
     * @return db
     */
	public function on( $key , $on = false  )
	{
		if( is_array( $key ) )
		{
			$joins = array();
			
			foreach( $key as $k => $o )
			{
				$joins[] = $k . ' = ' . $o;
			}
			
			$this->q['join'] .= implode( ' AND ', $joins );
		}
		else
		{
			$this->q['join'] .= $key . ' = ' . $on;
		}
		
		return $this;
    }
	public function useIndex( $index = 'PRIMARY' )
	{
		$this->q['use'][] = ' USE INDEX( ' . $index . ' ) ';
		return $this;
	}
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function where( $field, $value = false, $operator = '=' )
    {
		$this->q['conditions'][] = array( 'and', $field, $operator, $value );
        return $this;
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function orWhere( $field, $value = false, $operator = '=' )
    {
		$this->q['conditions'][] = array( 'or', $field, $operator, $value );
        return $this;
    }
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function brackets( $bracket, $type = '', $value = false )
    {
        $this->q['conditions'][] = array( ( !empty( $this->q['conditions'] ) ? $type . ' ' : '' ) . $bracket, false, 'bracket', $value );
        return $this;
    }
	
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function fields( $fields )
    {
        $this->q['fields'][] = is_array( $fields ) ? implode( ',', $fields ) : $fields ;
        return $this;
    }
    
	/**
     * 
     *
     * @param 
     * 
     * @return 
     */
    private function buildFields( $fields )
    {
        
        if( empty( $fields ) )
		{
            return '*';
        }
        
        if( !is_array( $fields ) )
		{
            return $fields;
        }
        
        return implode( ',', $fields );
    }
    
    
	/**
	 * ORDER|SORT|GROUP BY
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function clause( $clause, $type = 'order' )
    {
		$this->q['clause'] .= ( strlen( key( $clause ) ) > 0  ? ' ' . strtoupper( $type ) . ' BY ' . key( $clause ) . ' ' . current( $clause ) : '');
		return $this;
    }
	
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function sortBy( $sort, $direction = 'DESC')
    {
        return $this->clause( ( is_array( $sort ) ? $sort : array( $sort => $direction )), 'sort' );
	}
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function orderBy( $order, $direction = 'DESC')
    {
        return $this->clause( ( is_array( $order ) ? $order : array( $order => $direction ) ), 'order' );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function groupBy( $fields )
    {
        return $this->clause( array( $fields => '' ), 'group' );
    }


    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function limit( $limit )
    {
        $this->q['limit'] = $this->limitResults( $limit );
        return $this;
    }
	
	/**
     * Alias
     *
     * @param 
     * 
     * @return 
     */
    public function setWhere( $conditions )
    {
		$this->q['conditions'] = $this->buildWhere( $conditions, '' );
		return $this;
	}
	/**
     * 
     *
     * @param 
     * 
     * @return 
     */
    private function buildWhere( $conditions, $where = ' WHERE' )
    {
        
        if( is_array( $conditions ) )
		{
			foreach ( $conditions as $field => $value )
			{
				if( is_array( $value ) )
				{
					if( count( $value ) < 2 )
					{
						throw new Exception( 'To few args in query' );
					}
					$conditions[$field] = array( ( isset( $value[2] ) ? $value[2] : 'and' ), $field, $value[1], $value[0] );
				}
				else
				{
					$conditions[$field] = array( 'and', $field, '=', $value );
				}

			}
		}
		return $this->getWhere( $conditions, $where );
    }
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getWhere( $conditions, $where = ' WHERE' )
    {
        if ( empty( $conditions ) )
		{
			return;
		}

		if ( !is_array( $conditions ) )
		{
			return $where . ' ' . $conditions;
		}
		
        
		$conditions = array_values( $conditions );
		
		// Remove first AND/OR concatenator
		$conditions[0][0] = '';

		foreach ( $conditions as $key => &$condition )
		{
			// Reference for brackets
			list ( $concat, $field, $operator, $value ) = $condition;
			
			/* : - user for multiple conditions on same field ex: id IN ( 1,2 ) AND id: != 2 */
			$where .= ' ' . strtoupper ( $concat ) . ' ' . str_replace( ':', '', $field );

			$operator = strtoupper ( trim( $operator ) );
			
			if( $value !== '' )
			{
				switch ( $operator )
				{
					case 'BRACKET':
						if( isset( $conditions[$key+1] ) & empty( $value ) )
						{
							// Remove AND/OR concatenator in brackets
							$conditions[$key+1][0] = '';
						}
					break;
					case 'NOT IN':
					case 'IN':
						
						$where .= ' ' .  $operator . ' ( ' ;
						
						if( is_array( $value ) )
						{
							$where .= "'" . implode( "','", $value ) . "'";
						}
						else
						{
							$where .= $this->enclose( $value );
						}
						
						$where .= ' )';
						
					break;
					default:
						switch ( $operator )
						{
							case 'LIKE':
								$value = '%' . $value . '%';
							break;
							case 'L-LIKE':
								$value = '%' . $value ;
								$operator = 'LIKE';
							break;
							case 'R-LIKE':
								$value = $value . '%';
								$operator = 'LIKE';
							break;
						}
						$where .= ' ' . $operator . ' ' . $this->enclose( $value );
					break;
				}
			}
		}
		
		return $where;
    }
   
    /**
     * Replace part o query builder
     *
     * @param $part
	 * @param $replace
     * 
     * @return 
     */
	public function replaceQuery( $part, $replace )
	{
		if( isset( $this->q[ $part ] ) && gettype( $this->q[ $part ] ) == gettype( $replace ) )
		{
			$this->q[ $part ] = $replace;
		}
		return $this;
	}
    /**
     * 
     *
     * @param 
     * 
     * @return 
     */
    public function limitResults( $limitResults )
    {
        return !empty( $limitResults ) ? ' LIMIT ' . ( is_array( $limitResults ) ? implode( ',', $limitResults ) : $limitResults ) : ';';
    }
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function enclose( $value )
    {
		if( is_numeric( $value ) || strpos( $value, 'SELECT ' ) !== false )
		{
			return $value;
		}
		
		if( strtolower( substr( $value, 0, 6 ) ) == 'field:' )
		{
			return substr( $value, 6, strlen( $value ) );
		}
		
		if( strlen( $value ) > 100 )
		{
			ips_log( $value );
			ips_log( ips_backtrace() );
		}
		/* 
		if( strpos( $value, '.' ) !== false )
		{
			$t = strstr( $value, '.', true );
			if( strpos( $this->q['from'], ' ' . $t . ',' ) !== false || substr( $value, - ( strlen( $t ) ) ) == $t )
			{
				return $value;
			};
			
		}
		 */
		return "'" . $value . "'";
	}	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function union( $tables, $fields )
    {
		$query = $this->from( '%table%' )->fields( $fields )->getQuery();
		
		foreach( $tables as $key => $t )
		{
			$t = is_array( $t ) ? db_prefix( key( $t ), current( $t ) ) : db_prefix( $t );
			$tables[$key] = str_replace( '%table%', $t, $query );
		}
		
		return implode( 'UNION ',  $tables );
    }
	
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function getQuery()
    {
		return $this->q['sql'] . ' ' 
		. $this->buildFields( $this->q['fields'] )
		. ' FROM ' . $this->q['from'] 
		. implode('', $this->q['use'] )
		. $this->q['join'] 
		. $this->getWhere( has_value( 'conditions', $this->q ) ) 
		. ' ' 
		. has_value('group', $this->q ) 
		. has_value( 'clause', $this->q ) 
		. ' ' 
		. has_value('limit', $this->q);
    }
	
	/**
	 * Delete row from table
	 *
	 * @param null
	 * 
	 * @return mixed
	 */
	public function remove()
    {
		$this->q['sql'] = 'DELETE';
		$this->q['fields'] = ' ';

		return $this->PDQuery( $this->getQuery() );
    }
	/**
	 * Get all/limit rows
	 *
	 * @param int/array $limit
	 * 
	 * @return mixed
	 */
	public function get( $limit = null )
    {
		if( $limit )
		{
			$this->q['limit'] = $this->limitResults($limit);
		}
		return $this->PDQuery( $this->getQuery() );
    }
	/**
	 * Get one row
	 *
	 * @param null
	 * 
	 * @return mixed
	 */
	public function getOne()
    {
        $q = $this->PDQuery( $this->limit(1)->getQuery() );
		
		if( isset( $q[0] ) )
		{
			return $q[0];
		}
		
		return $q;
    }

	/**
	 * Repair table
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function repair( $table )
	{
		return $this->PDQuery( 'REPAIR TABLE ' . $this->prefix( $table ) . ';' );
	}
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function explain($string)
    {
        if ( preg_match( "#^\s*select#i", $string ) )
		{
            $query = 'EXPLAIN ' . $string;
            $stmt  = $this->prepare($query);
            $stmt->execute(array());
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($result)) {
                return count($result) == 1 ? $result[0] : $result;
            }
        }
        
        return false;
    }
    
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function __toString()
    {
        $this->debug();
    }
    /**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
	public function debug( $return_data = false )
    {
        $queries = array();
		$queries_time = 0;

		if ( !empty( $this->queries ) )
		{
            foreach ($this->queries as $query => $error)
			{
                $queries[round((1000000 * $error['time']), 10)] = $error;
                $queries_time += $error['time'];
            }
            
            krsort($queries);
        }
        
        if ( $return_data )
		{
            return array(
                'query_count' => $this->query_count,
                'queries' => $queries,
                'queries_time' => $queries_time,
                'error' => $this->error
            );
        }
        
        if ( !empty( $this->error ) )
		{
            foreach ($this->error as $error => $val)
			{
                echo '<div style="color: red; margin: 0px auto;">';
                if ( is_array( $val ) )
				{
                    foreach ($val as $msg) {
                        echo $msg . '<br />';
                    }
                } else {
                    echo $val . '<br />';
                }
                echo '</div><br />';
            }
        }
        
        echo '<pre>Zapytań SQL: ' . $this->query_count . '</pre>';
        
        if (!empty($this->queries)) {
            foreach ($queries as $query => $error) {
                echo '<div style="color: 000; margin: 0px auto;">
				Zapytanie: <b style="color: red">' . $error['sql'] . '</b><br />' . sprintf("Czas: %.4f s.", abs($error['time'])) . ' <br />
				Gdzie ' . (isset($error['class']) ? $error['class'] : 'brak') . ' 
				</div>
				<br /><br />';
            }
            echo 'Czas całkowity zapytań do bazy: ' . $queries_time . '<br /><br />';
        }
    }
    
    /**
     * Start timera.
     * @return true
     */
    public function startTimer()
    {
        $mctime          = explode(' ', microtime());
        $this->timeStart = $mctime[1] + $mctime[0];
        return true;
    }
    /**
     * Stop timera.
     * @return int w millisekundach
     */
    public function stopTimer()
    {
        $mctime = explode(' ', microtime());
        $end    = $mctime[1] + $mctime[0];
        return ($end - $this->timeStart);
    }
    /**
     * Debug: turn the function call PD.
     * @return string name
     */
    //
    function callingClass()
    {
        $trace     = array_reverse(debug_backtrace());
        $call_info = array();
        
        foreach ($trace as $called) {
            if (isset($called['class']) && __CLASS__ == $called['class'])
                continue;
            $call_info[] = isset($called['class']) ? "{$called['class']}->{$called['function']}" : $called['function'];
            //.'('
            //.( is_array($called['args'] ) && $called['args'] !== null ? ( isset($called['args'][0]) ? serialize( $called['args'][0] ) : serialize($called['args']) ) : $called['args'] )
            //.')';
            
        }
        if (isset($called['file'])) {
            // $call_info[] = "<br />W pliku: ". str_replace( ABS_PATH, '', $call['file'] );
        }
        return array(
            'files' => implode(' -> ', array_unique(array_map('basename', array_column($trace, 'file')))),
            'functions' => join(', ', $call_info)
        );
    }
	/**
	 * TO DO COMMENT
	 *
	 * @param 
	 * 
	 * @return 
	 */
    public function lastQuery()
    {
        return $this->lastQuery;
    }
}
?>