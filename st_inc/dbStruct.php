<?php
/**
TODO: make it work even without ';' delimiters or at least warn about that
TODO: better parse error reporting
TODO: accept empty datetime value and 0000-00-00 00:00:00 are equal, similar with date and time, also enum('0','1') [default 0], what's with floats?(float(10,2) NOT NULL default '0.00'); text,mediumtext,etc;
TODO: option to add database name with dot before the table names
TODO: add option "order does matter"
DONE: breaks table definition on commas and brackets, not newlines
DONE: handles `database`.`table` in CREATE TABLE string (but does not add database to result sql for a while - and if it
should? as same tables struct in 2 DBs compared is also a case)
DONE: handles double (and more) spaces in CREATE TABLE string
DONE: add filter option (fields: MODIFY, ADD, DROP, tables: CREATE, DROP)
DONE: make it work also with comments
DONE: move all options to $this->config
*/
/**
* The class provides ability to compare 2 database structure dumps and compile a set of sql statements to update
* one database to make it structure identical to another.
*
* @author Kirill Gerasimenko <ger.kirill@gmail.com>
*
* The input for the script could be taken from the phpMyAdmin structure dump, or provided by some custom code
* that uses 'SHOW CREATE TABLE' query to get database structure table by table.
* The output is either array of sql statements suitable for executions right from php or a string where the
* statements are placed each at new line and delimited with ';' - suitable for execution from phpMyAdmin SQL
* page.
* The resulting sql may contain queries that aim to:
* Create missing table (CREATE TABLE query)
* Delete table which should not longer exist (DROP TABLE query)
* Update, drop or add table field or index definition (ALTER TABLE query)
*
* Some features:
* - AUTO_INCREMENT value is ommited during the comparison and in resulting CREATE TABLE sql
* - fields with definitions like "(var)char (255) NOT NULL default ''" and "(var)char (255) NOT NULL" are treated
*   as equal, the same for (big|tiny)int NOT NULL default 0;
* - IF NOT EXISTS is automatically added to the resulting sql CREATE TABLE statement
* - fields updating queries always come before key modification ones for each table
* Not implemented:
* - The class even does not try to insert or re-order fields in the same order as in the original table.
*   Does order matter?
* IMPORTANT!!! Class will not handle a case when the field was renamed. It will generate 2 queries - one to drop
* the column with the old name and one to create column with the new name, so if there is a data in the dropped
* column, it will be lost.
* Usage example:
  $updater = new dbStructUpdater();
  $res = $updater->getUpdates($struct1, $struct2);
  -----
  $res == array (
  	[0]=>"ALTER TABLE `b` MODIFY `name` varchar(255) NOT NULL",
  	...
  )
*/
class dbStructUpdater
{
	var $sourceStruct = '';//structure dump of the reference database
	var $destStruct = '';//structure dump of database to update
	var $config = array();//updater configuration

	/**
	* Constructor
	* @access public
	*/
	function dbStructUpdater()
	{
		$this->init();
	}

	function init()
	{
		//table operations: create, drop; field operations: add, remove, modify
		$this->config['updateTypes'] = 'create, drop, add, remove, modify';
		//ignores default part in cases like (var)char NOT NULL default '' upon the	comparison
		$this->config['varcharDefaultIgnore'] = true;
		//the same for int NOT NULL default 0
		$this->config['intDefaultIgnore'] = true;
		//ignores table autoincrement field value, also remove AUTO_INCREMENT value from the create query if exists
		$this->config['ignoreIncrement'] = true;
		//add 'IF NOT EXIST' to each CREATE TABLE query
		$this->config['forceIfNotExists'] = true;
		//remove 'IF NOT EXIST' if already exists CREATE TABLE dump
		$this->config['ingoreIfNotExists'] = false;
	}

	/**
	* merges current updater config with the given one
	* @param assoc_array $config new configuration values
	*/
	function setConfig($config=array())
	{
		if (is_array($config))
		{
			$this->config = array_merge($this->config, $config);
		}
	}

	/**
	* Returns array of update SQL with default options, $source, $dest - database structures
	* @access public
	* @param string $source structure dump of database to update
	* @param string $dest structure dump of the reference database
	* @param bool $asString if true - result will be a string, otherwise - array
	* @return array|string update sql statements - in array or string (separated with ';')
	*/
	function getUpdates($source, $dest, $asString=false)
	{
		$result = $asString?'':array();
		$compRes = $this->compare($source, $dest);
		if (empty($compRes))
		{
			return $result;
		}
		$compRes = $this->filterDiffs($compRes);
		if (empty($compRes))
		{
			return $result;
		}
		$result = $this->getDiffSql($compRes);
		if ($asString)
		{
			$result = implode(";\r\n", $result).';';
		}
		return $result;
	}

	/**
	* Filters comparison result and lefts only sync actions allowed by 'updateTypes' option
	*/
	function filterDiffs($compRes)
	{
		$result = array();
		if (is_array($this->config['updateTypes']))
		{
			$updateActions = $this->config['updateTypes'];
		}
		else
		{
			$updateActions = array_map('trim', explode(',', $this->config['updateTypes']));
		}
		$allowedActions = array('create', 'drop', 'add', 'remove', 'modify');
		$updateActions = array_intersect($updateActions, $allowedActions);
		foreach($compRes as $table=>$info)
		{
			if ($info['sourceOrphan'])
			{
				if (in_array('create', $updateActions))
				{
					$result[$table] = $info;
				}
			}
			elseif ($info['destOrphan'])
			{
				if (in_array('drop', $updateActions))
				{
					$result[$table] = $info;
				}
			}
			elseif($info['differs'])
			{
				$resultInfo = $info;
				unset($resultInfo['differs']);
				foreach ($info['differs'] as $diff)
				{
					if (empty($diff['dest']) && in_array('add', $updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
					elseif (empty($diff['source']) && in_array('remove', $updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
					elseif(in_array('modify', $updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
				}
				if (!empty($resultInfo['differs']))
				{
					$result[$table] = $resultInfo;
				}
			}
		}
		return $result;
	}
	
	/**
	* Gets structured general info about the databases diff :
	* array(sourceOrphans=>array(...), destOrphans=>array(...), different=>array(...))
	*/
	function getDiffInfo($compRes)
	{		
		if (!is_array($compRes))
		{
			return false;
		}
		$result = array('sourceOrphans'=>array(), 'destOrphans'=>array(), 'different'=>array());
		foreach($compRes as $table=>$info)
		{
			if ($info['sourceOrphan'])
			{
				$result['sourceOrphans'][] = $table;
			}
			elseif ($info['destOrphan'])
			{
				$result['destOrphans'][] = $table;
			}
			else
			{
				$result['different'][] = $table;
			}
		}
		return $result;
	}

	/**
	* Makes comparison of the given database structures, support some options
	* @access private
	* @param string $source and $dest are strings - database tables structures
	* @return array
	* - table (array)
	*		- destOrphan (boolean)
	*		- sourceOrphan (boolean)
	*		- differs (array) OR (boolean) false if no diffs
	*			- [0](array)
	*				- source (string) structure definition line in the out-of-date table
	*				- dest (string) structure definition line in the reference table
	*			- [1](array) ...
	*/
	function compare($source, $dest)
	{
		$this->sourceStruct = $source;
		$this->destStruct = $dest;

		$result = array();
		$destTabNames = $this->getTableList($this->destStruct);
		$sourceTabNames = $this->getTableList($this->sourceStruct);

		$common = array_intersect($destTabNames, $sourceTabNames);
		$destOrphans = array_diff($destTabNames, $common);
		$sourceOrphans = array_diff($sourceTabNames, $common);
		$all = array_unique(array_merge($destTabNames, $sourceTabNames));
		sort($all);
		foreach ($all as $tab)
		{
			$info = array('destOrphan'=>false, 'sourceOrphan'=>false, 'differs'=>false);
			if(in_array($tab, $destOrphans))
			{
				$info['destOrphan'] = true;
			}
			elseif (in_array($tab, $sourceOrphans))
			{
				$info['sourceOrphan'] = true;
			}
			else
			{				
				$destSql = $this->getTabSql($this->destStruct, $tab, true);
				$sourceSql = $this->getTabSql($this->sourceStruct, $tab, true);
				$diffs = $this->compareSql($sourceSql, $destSql);				
				if ($diffs===false)
				{
					trigger_error('[WARNING] error parsing definition of table "'.$tab.'" - skipped');
					continue;
				}
				elseif (!empty($diffs))//not empty array
				{
					$info['differs'] = $diffs;					
				}				
				else continue;//empty array
			}
			$result[$tab] = $info;
		}
		return $result;
	}

	/**
	* Retrieves list of table names from the database structure dump
	* @access private
	* @param string $struct database structure listing
	*/
	function getTableList($struct)
	{
		$result = array();
		if (preg_match_all('/CREATE(?:\s*TEMPORARY)?\s*TABLE\s*(?:IF NOT EXISTS\s*)?(?:`?(\w+)`?\.)?`?(\w+)`?/i', $struct, $m))
		{
			foreach($m[2] as $match)//m[1] is a database name if any
			{
				$result[] = $match;
			}
		}
		return $result;
	}

	/**
	* Retrieves table structure definition from the database structure dump
	* @access private
	* @param string $struct database structure listing
	* @param string $tab table name
	* @param bool $removeDatabase - either to remove database name in "CREATE TABLE database.tab"-like declarations
	* @return string table structure definition
	*/
	function getTabSql($struct, $tab, $removeDatabase=true)
	{
		$result = '';
		/* create table should be single line in this case*/
		//1 - part before database, 2-database name, 3 - part after database
		if (preg_match('/(CREATE(?:\s*TEMPORARY)?\s*TABLE\s*(?:IF NOT EXISTS\s*)?)(?:`?(\w+)`?\.)?(`?('.$tab.')`?(\W|$))/i', $struct, $m, PREG_OFFSET_CAPTURE))		
		{
			$tableDef = $m[0][0];
			$start = $m[0][1];
			$database = $m[2][0];
			$offset = $start+strlen($m[0][0]);
			$end = $this->getDelimPos($struct, $offset);
			if ($end === false)
			{
				$result = substr($struct, $start);
			}
			else
			{
				$result = substr($struct, $start, $end-$start);//already without ';'
			}
		}
		$result = trim($result);
		if ($database && $removeDatabase)
		{
			$result = str_replace($tableDef, $m[1][0].$m[3][0], $result);
		}		
		return $result;
	}
	
	/**
	* Splits table sql into indexed array
	* 
	*/
	function splitTabSql($sql)
	{
		$result = array();
		//find opening bracket, get the prefix along with it
		$openBracketPos = $this->getDelimPos($sql, 0, '(');
		if ($openBracketPos===false)
		{
			trigger_error('[WARNING] can not find opening bracket in table definition');
			return false;
		}
		$prefix = substr($sql, 0, $openBracketPos+1);//prefix can not be empty, so do not check it, just trim
		$result[] = trim($prefix);
		$body = substr($sql, strlen($prefix));//fields, indexes and part after closing bracket
		//split by commas, get part by part
		while(($commaPos = $this->getDelimPos($body, 0, ',', true))!==false)
		{
			$part = trim(substr($body, 0, $commaPos+1));//read another part and shorten $body
			if ($part)
			{
				$result[] = $part;
			}
			$body = substr($body, $commaPos+1);
		}
		//here we have last field (or index) definition + part after closing bracket (ENGINE, ect)
		$closeBracketPos = $this->getDelimRpos($body, 0, ')');
		if ($closeBracketPos===false)
		{
			trigger_error('[WARNING] can not find closing bracket in table definition');
			return false;
		}
		//get last field / index definition before closing bracket
		$part = substr($body, 0, $closeBracketPos);
		$result[] = trim($part);
		//get the suffix part along with the closing bracket
		$suffix = substr($body, $closeBracketPos);
		$suffix = trim($suffix);
		if ($suffix)
		{
			$result[] = $suffix;
		}
		return $result;
	}

	/**
	* returns array of fields or keys definitions that differs in the given tables structure
	* @access private
	* @param sring $sourceSql table structure
	* @param sring $destSql right table structure
	* supports some $options
	* @return array
	* 	- [0]
	* 		- source (string) out-of-date table field definition
	* 		- dest (string) reference table field definition
	* 	- [1]...
	*/
	function compareSql($sourceSql, $destSql)//$sourceSql, $destSql
	{
		$result = array();		
		//split with comma delimiter, not line breaks
		$sourceParts =  $this->splitTabSql($sourceSql);
		if ($sourceParts===false)//error parsing sql
		{
			trigger_error('[WARNING] error parsing source sql');
			return false;
		}
		$destParts = $this->splitTabSql($destSql);
		if ($destParts===false)
		{
			trigger_error('[WARNING] error parsing destination sql');
			return false;
		}
		$sourcePartsIndexed = array();
		$destPartsIndexed = array();
		foreach($sourceParts as $line)
		{			
			$lineInfo = $this->processLine($line);
			if (!$lineInfo) continue;
			$sourcePartsIndexed[$lineInfo['key']] = $lineInfo['line'];
		}
		foreach($destParts as $line)
		{			
			$lineInfo = $this->processLine($line);
			if (!$lineInfo) continue;
			$destPartsIndexed[$lineInfo['key']] = $lineInfo['line'];
		}
		$sourceKeys = array_keys($sourcePartsIndexed);
		$destKeys = array_keys($destPartsIndexed);
		$all = array_unique(array_merge($sourceKeys, $destKeys));
		sort($all);//fields first, then indexes - because fields are prefixed with '!'

		foreach ($all as $key)
		{
			$info = array('source'=>'', 'dest'=>'');
			$inSource= in_array($key, $sourceKeys);
			$inDest= in_array($key, $destKeys);
			$sourceOrphan = $inSource && !$inDest;
			$destOrphan = $inDest && !$inSource;
			$different =  $inSource && $inDest && 
			strcasecmp($this->normalizeString($destPartsIndexed[$key]), $this->normalizeString($sourcePartsIndexed[$key]));
			if ($sourceOrphan)
			{
				$info['source'] = $sourcePartsIndexed[$key];
			}
			elseif ($destOrphan)
			{
				$info['dest'] = $destPartsIndexed[$key];
			}
			elseif ($different)
			{
				$info['source'] = $sourcePartsIndexed[$key];
				$info['dest'] = $destPartsIndexed[$key];
			}
			else continue;
			$result[] = $info;
		}
		return $result;
	}

	/**
	* Transforms table structure defnition line into key=>value pair where the key is a string that uniquely
	* defines field or key desribed
	* @access private
	* @param string $line field definition string
	* @return array array with single key=>value pair as described in the description
	* implements some options
	*/
	function processLine($line)
	{
		$options = $this->config;
		$result = array('key'=>'', 'line'=>'');
		$line = rtrim(trim($line), ',');
		if (preg_match('/^(CREATE\s+TABLE)|(\) ENGINE=)/i', $line))//first or last table definition line
		{
			return false;
		}
		//if (preg_match('/^(PRIMARY KEY)|(((UNIQUE )|(FULLTEXT ))?KEY `?\w+`?)/i', $line, $m))//key definition
		if (preg_match('/^(PRIMARY\s+KEY)|(((UNIQUE\s+)|(FULLTEXT\s+))?KEY\s+`?\w+`?)/i', $line, $m))//key definition
		{
			$key = $m[0];
		}
		elseif (preg_match('/^`?\w+`?/i', $line, $m))//field definition
		{
			$key = '!'.$m[0];//to make sure fields will be synchronised before the keys
		}
		else
		{
			return false;//line has no valuable info (empty or comment)
		}
		//$key = str_replace('`', '', $key);
		if (!empty($options['varcharDefaultIgnore']))
		{
			$line = preg_replace("/(var)?char\(([0-9]+)\)\s+NOT\s+NULL\s+default\s+''/i", '$1char($2) NOT NULL', $line);
		}
		if (!empty($options['intDefaultIgnore']))
		{
			$line = preg_replace("/((?:big)|(?:tiny))?int\(([0-9]+)\)\s+NOT\s+NULL\s+default\s+'0'/i", '$1int($2) NOT NULL', $line);
		}
		if (!empty($options['ignoreIncrement']))
		{
			$line = preg_replace("/ AUTO_INCREMENT=[0-9]+/i", '', $line);
		}
		$result['key'] = $this->normalizeString($key);
		$result['line']= $line;
		return $result;
	}

	/**
	* Takes an output of compare() method to generate the set of sql needed to update source table to make it
	* look as a destination one
	* @access private
	* @param array $diff compare() method output
	* @return array list of sql statements
	* supports query generation options
	*/
	function getDiffSql($diff)//maybe add option to ommit or force 'IF NOT EXISTS', skip autoincrement
	{
		$options = $this->config;
		$sqls = array();
		if (!is_array($diff) || empty($diff))
		{
			return $sqls;
		}
		foreach($diff as $tab=>$info)
		{
			if ($info['sourceOrphan'])//delete it
			{
				$sqls[] = 'DROP TABLE `'.$tab.'`';
			}
			elseif ($info['destOrphan'])//create destination table in source
			{
				$database = '';
				$destSql = $this->getTabSql($this->destStruct, $tab, $database);
				if (!empty($options['ignoreIncrement']))
				{
					$destSql = preg_replace("/\s*AUTO_INCREMENT=[0-9]+/i", '', $destSql);
				}
				if (!empty($options['ingoreIfNotExists']))
				{
					$destSql = preg_replace("/IF NOT EXISTS\s*/i", '', $destSql);
				}
				if (!empty($options['forceIfNotExists']))
				{
					$destSql = preg_replace('/(CREATE(?:\s*TEMPORARY)?\s*TABLE\s*)(?:IF\sNOT\sEXISTS\s*)?(`?\w+`?)/i', '$1IF NOT EXISTS $2', $destSql);
				}
				$sqls[] = $destSql;
			}
			else
			{
				foreach($info['differs'] as $finfo)
				{
					$inDest = !empty($finfo['dest']);
					$inSource = !empty($finfo['source']);
					if ($inSource && !$inDest)
					{
						$sql = $finfo['source'];
						$action = 'drop';
					}
					elseif ($inDest && !$inSource)
					{
						$sql = $finfo['dest'];
						$action = 'add';
					}
					else
					{
						$sql = $finfo['dest'];
						$action = 'modify';
					}
					$sql = $this->getActionSql($action, $tab, $sql);
					$sqls[] = $sql;
				}
			}
		}
		return $sqls;
	}

	/**
	* Compiles update sql
	* @access private
	* @param string $action - 'drop', 'add' or 'modify'
	* @param string $tab table name
	* @param string $sql definition of the element to change
	* @return string update sql
	*/
	function getActionSql($action, $tab, $sql)
	{
		$result = 'ALTER TABLE `'.$tab.'` ';
		$action = strtolower($action);
		$keyField = '`?\w`?(?:\(\d+\))?';//matches `name`(10)
		$keyFieldList = '(?:'.$keyField.'(?:,\s?)?)+';//matches `name`(10),`desc`(255)
		if (preg_match('/((?:PRIMARY )|(?:UNIQUE )|(?:FULLTEXT ))?KEY `?(\w+)?`?\s(\('.$keyFieldList.'\))/i', $sql, $m))
		{   //key and index operations
			$type = strtolower(trim($m[1]));
			$name = trim($m[2]);
			$fields = trim($m[3]);
			switch($action)
			{
				case 'drop':
					if ($type=='primary')
					{
						$result.= 'DROP PRIMARY KEY';
					}
					else
					{
						$result.= 'DROP INDEX `'.$name.'`';
					}
				break;
				case 'add':
					if ($type=='primary')
					{
						$result.= 'ADD PRIMARY KEY '.$fields;
					}
					elseif ($type=='')
					{
						$result.= 'ADD INDEX `'.$name.'` '.$fields;
					}
					else
					{
						$result .='ADD '.strtoupper($type).' `'.$name.'` '.$fields;//fulltext or unique
					}
				break;
				case 'modify':
					if ($type=='primary')
					{
						$result.='DROP PRIMARY KEY, ADD PRIMARY KEY '.$fields;
					}
					elseif ($type=='')
					{
						$result.='DROP INDEX `'.$name.'`, ADD INDEX `'.$name.'` '.$fields;
					}
					else
					{
						$result.='DROP INDEX `'.$name.'`, ADD '.strtoupper($type).' `'.$name.'` '.$fields;//fulltext or unique
					}
				break;

			}
		}
		else //fields operations
		{
			$sql = rtrim(trim($sql), ',');
			$result.= strtoupper($action);
			if ($action=='drop')
			{
				$spacePos = strpos($sql, ' ');
				$result.= ' '.substr($sql, 0, $spacePos);
			}
			else
			{
				$result.= ' '.$sql;
			}
		}
		return $result;
	}

	/**
	* Searches for the position of the next delimiter which is not inside string literal like 'this ; ' or
	* like "this ; ".
	*
	* Handles escaped \" and \'. Also handles sql comments.
	* Actualy it is regex-based Finit State Machine (FSN)
	*/
	function getDelimPos($string, $offset=0, $delim=';', $skipInBrackets=false)
	{
		$stack = array();
		$rbs = '\\\\';	//reg - escaped backslash
		$regPrefix = "(?<!$rbs)(?:$rbs{2})*";
		$reg = $regPrefix.'("|\')|(/\\*)|(\\*/)|(-- )|(\r\n|\r|\n)|';
		if ($skipInBrackets) 
		{
			$reg.='(\(|\))|';
		}
		else 
		{
			$reg.='()';
		}
		$reg .= '('.preg_quote($delim).')';
		while (preg_match('%'.$reg.'%', $string, $m, PREG_OFFSET_CAPTURE, $offset))
		{
			$offset = $m[0][1]+strlen($m[0][0]);
			if (end($stack)=='/*')
			{
				if (!empty($m[3][0]))
				{
					array_pop($stack);
				}
				continue;//here we could also simplify regexp
			}
			if (end($stack)=='-- ')
			{
				if (!empty($m[5][0]))
				{
					array_pop($stack);
				}
				continue;//here we could also simplify regexp
			}

			if (!empty($m[7][0]))// ';' found
			{
				if (empty($stack))
				{
					return $m[7][1];
				}
				else
				{
					//var_dump($stack, substr($string, $offset-strlen($m[0][0])));
				}
			}
			if (!empty($m[6][0]))// '(' or ')' found
			{
				if (empty($stack) && $m[6][0]=='(')
				{
					array_push($stack, $m[6][0]);
				}
				elseif($m[6][0]==')' && end($stack)=='(')
				{
					array_pop($stack);
				}
			}
			elseif (!empty($m[1][0]))// ' or " found
			{
				if (end($stack)==$m[1][0])
				{
					array_pop($stack);
				}
				else
				{
					array_push($stack, $m[1][0]);
				}
			}
			elseif (!empty($m[2][0])) // opening comment / *
			{
				array_push($stack, $m[2][0]);
			}
			elseif (!empty($m[4][0])) // opening comment --
			{
				array_push($stack, $m[4][0]);
			}
		}
		return false;
	}
	
	/**
	* works the same as getDelimPos except returns position of the first occurence of the delimiter starting from
	* the end of the string
	*/
	function getDelimRpos($string, $offset=0, $delim=';', $skipInBrackets=false)
	{
		$pos = $this->getDelimPos($string, $offset, $delim, $skipInBrackets);
		if ($pos===false)
		{
			return false;
		}
		do
		{
			$newPos=$this->getDelimPos($string, $pos+1, $delim, $skipInBrackets);
			if ($newPos !== false)
			{
				$pos = $newPos;
			}
		}
		while($newPos!==false);
		return $pos;
	}

	/**
	 * Converts string to lowercase and replaces repeated spaces with the single one -
	 * to be used for the comparison purposes only
	 * @param string $str string to normaize
	 */
	function normalizeString($str)
	{
		$str = strtolower($str);
		$str = preg_replace('/\s+/', ' ', $str);
		return $str;
	}
}
?>
