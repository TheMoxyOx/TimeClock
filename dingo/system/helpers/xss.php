<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework XSS Cleaning Helper
 * - Requires the XML Helper to be loaded in order to work!
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class xss
{
	// Clean
	// ---------------------------------------------------------------------------
	static function clean($xml,$allowed=FALSE,$debug=FALSE)
	{
		if(!$tree = xml::parse("<wrapper>$xml</wrapper>"))
		{
			return FALSE;
		}
		
		if(!$allowed)
		{
			$allowed = array(
				'a'=>array('attributes'=>array('href'=>'URL','title'=>'/^([ \-_a-zA-Z0-9\.\/\!]+)$/')),
				'img'=>array('attributes'=>array('src'=>'URL','title'=>'ANY','alt'=>'ANY')),
				'b'=>array('transform'=>'strong'),
				'i'=>array('transform'=>'em'),
				'strong'=>array(),
				'em'=>array(),
				'p'=>array()
			);
		}
		
		if(is_array($tree))
		{
			$tree = xss::clean_childnodes($tree,$allowed);
			
			// If not in debug mode return XML
			if(!$debug)
			{
				return xml::build($tree);
			}
			
			// Otherwise, return element tree
			else
			{
				return $tree;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	
	// Clean Child Nodes
	// ---------------------------------------------------------------------------
	static function clean_childnodes($el,$allowed)
	{
		$tree = array();
		
		// Loop child nodes
		foreach($el as $i)
		{
			// Clean node
			$n = xss::clean_node($i,$allowed);
			
			// Only add nodes that are not empty
			if(!empty($n))
			{
				$tree[] = $n;
			}
		}
		
		return $tree;
	}
	
	
	// Clean Node
	// ---------------------------------------------------------------------------
	static function clean_node($node,$allowed)
	{
		// Text Nodes
		if($node['type'] == 'text')
		{
			$tree[] = $node;
		}
		
		// Regular Nodes
		elseif(isset($allowed[$node['name']]))
		{
			// If transformation node EX: <b> to <strong>
			if(isset($allowed[$node['name']]['transform']))
			{
				$node['name'] = $allowed[$node['name']]['transform'];
				$node = xss::clean_node($node,$allowed);
			}
			
			// If node can contain attributes
			if(isset($allowed[$node['name']]['attributes']))
			{
				// Clean attribute list
				$a = xss::clean_node_attr($node['attributes'],$allowed[$node['name']]['attributes']);
				
				if(!empty($a))
				{
					$node['attributes'] = $a;
				}
				else
				{
					unset($node['attributes']);
				}
			}
			
			// Otherwise, node cannot contain any attributes
			elseif(isset($node['attributes']))
			{
				unset($node['attributes']);
			}
		}
		
		// Disallowed Node
		else
		{
			$node = array();
		}
		
		return $node;
	}
	
	
	// Clean Node Attributes
	// ---------------------------------------------------------------------------
	static function clean_node_attr($attributes,$allowed)
	{
		$list = array();
	
		// Loop attribute list
		foreach($attributes as $attr=>$value)
		{
			// If valid attribute
			if(isset($allowed[$attr]))
			{
				// If attribute can have any value add it to the list
				if($allowed[$attr] == 'ANY')
				{
					$list[$attr] = $value;
				}
				
				// URL accepting only attributes. Will accept invalid URLs, but they will be safe
				// /^[a-zA-Z]+[:\/\/]{1}[ -_a-zA-Z0-9-\.]{2,}\.[ -_a-zA-Z0-9-\.]{2,}[ -=\~_a-zA-Z%&\?\/\.]?$/
				elseif($allowed[$attr] == 'URL' AND !preg_match('/[;\(\)\[\]\'"\\\]+/',$value))
				{
					$list[$attr] = $value;
				}
				
				// Otherwise, treat the $allowed array key value as a regular expression
				elseif(preg_match("{$allowed[$attr]}",$value))
				{
					$list[$attr] = $value;
				}
			}
		}
		
		return $list;
	}
}