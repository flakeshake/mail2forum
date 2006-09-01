<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 *   
 *   Adapted from the original file "functions_insert_post.php" by neclectic, 
 *   see details below:
 *
 *
 *			/************************************************************************************************************************
 *			                             insert_post
 *			                         -------------------
 *			   Author         :   netclectic - Adrian Cockburn - adrian@netclectic.com
 *			                          thanks to wineknow for the suggestion of adding the current_time parameter
 *			   Version        :   1.0.7
 *			   Created 		    : 	Monday, Sept 23, 2002
 *			   Last Updated   :   Friday, July 11, 2003
 *			
 *			   Description    :   This functions is used to insert a post into your phpbb forums. 
 *			                      It handles all the related bits like updating post counts, 
 *			                      indexing search words, etc.
 *			                      The post is inserted for a specific user, so you will have to 
 *			                      already have a user setup which you want to use with it.
 *			
 *			                      If you're using the POST method to input data then you should call stripslashes on
 *			                      your subject and message before calling insert_post - see test_insert_post for example.
 *			
 *			   Parameters     :   $message            - the message that will form the body of the post
 *			                      $subject            - the subject of the post
 *			                      $forum_id           - the forum the post is to be added to
 *			                      $user_id            - the id of the user for the post
 *			                      $user_name          - the username of the user for the post
 *			                      $user_attach_sig    - should the user's signature be attached to the post
 *			
 *			   Options Params :   $topic_id           - if topic_id is passed then the post will be 
 *			                                              added as a reply to this topic
 *			                      $topic_type         - defaults to POST_NORMAL, can also be
 *			                                              POST_STICKY, POST_ANNOUNCE or POST_GLOBAL_ANNOUNCE
 *			                      $do_notification    - should users be notified of new posts (only valid for replies)
 *			                      $notify_user        - should the 'posting' user be signed up for notifications of this topic
 *			                      $current_time       - should the current time be used, if not then you should supply a posting time
 *			                      $error_die_function - can be used to supply a custom error function.
 *			                      $html_on = false    - should html be allowed (parsed) in the post text.
 *			                      $bbcode_on = true   - should bbcode be allowed (parsed) in the post text.
 *			                      $smilies_on = true  - should smilies be allowed (parsed) in the post text.
 *			
 *			   Returns        :   If the function succeeds without an error it will return an array containing
 *			                      the post id and the topic id of the new post. Any error along the way will result in either
 *			                      the normal phpbb message_die function being called or a custom die function determined
 *			                      by the $error_die_function parameter.
 *			/************************************************************************************************************************
 *
 *
 *
 **************************************************************************************/

class m2f_phpBB_insert_post
{

	var $_loaded = FALSE;
	
	var $phpbb_root_path;
	
	/** 
	* Constructor
	*
	* @param string $this->phpbb_root_path full path to the phpBB installation
	* @access public 
	*/
	function m2f_phpBB_insert_post($phpbb_root_path)
	{
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/** 
	*	Test root path and start loading files and variables
	* 
	* @return mixed true on success, or error object
	* @access public 
	*/
	function load()
	{
		if (!defined('IN_PHPBB')) define('IN_PHPBB', TRUE);

		if (!is_readable($this->phpbb_root_path . 'extension.inc')) return m2f::raise_error('Can\'t read phpBB extension.inc file at path "' . $this->phpbb_root_path . 'extension.inc".', __LINE__, __FILE__);
		include($this->phpbb_root_path . 'extension.inc');		

		if (!isset($phpEx)) return m2f::raise_error('Invalid phpBB extension.inc file at path "' .$this->phpbb_root_path . 'extension.inc".', __LINE__, __FILE__);
		$this->phpEx = $phpEx;
		
		if (!is_readable($this->phpbb_root_path . 'config.' . $this->phpEx)) return m2f::raise_error('Can\'t read phpBB config file at path "' . $this->phpbb_root_path . 'config.' . $this->phpEx . '".', __LINE__, __FILE__);
		
		$config = file_get_contents($this->phpbb_root_path . 'config.' . $this->phpEx);
		if (!preg_match('#(\$.*?)define#sm', $config, $matches) || !isset($matches[1]) || (FALSE === eval($matches[1])))
		{
			return m2f::raise_error('phpBB config file is invalid.', __LINE__, __FILE__);
		}

		foreach (array('dbms', 'dbhost', 'dbuser', 'dbpasswd', 'dbname', 'table_prefix') as $var)
		{
			if (empty($$var)) return m2f::raise_error('Value "' . $var . '" is not defined in phpBB config file.', __LINE__, __FILE__);
		}

		if (m2f::is_error($error = $this->_connect_db($dbms, $dbhost, $dbuser, $dbpasswd, $dbname))) return $error;
		
		$this->table_prefix = $table_prefix;
		
//		
//		$userdata = array('user_id' => -1);
//		
//		$global_vars = array('db', 'board_config', 'user_ip', 'unhtml_specialchars_match', 'unhtml_specialchars_replace', 
//													'html_entities_match', 'html_entities_replace', 'phpbb_root_path', 'lang', 'phpEx', 'userdata');
//													
//		foreach ($global_vars as $var)
//		{
//			if (!isset($$var)) return m2f::raise_error('phpBB error: the variable "' . $var . '" must be defined.', __LINE__, __FILE__);
//			$GLOBALS[$var] =& $$var;
//		}
//
//		

		$this->_loaded = TRUE;
		return TRUE;
	}

	

	/** 
	* If the database is down, phpBB will throw an error. We must try and avoid this, so we use our own adodb to check the connection first.
	*  
	* @param string $this->phpbb_root_path full path to the phpBB installation
	* @param string $this->phpEx php file extension
	* @return mixed true on success, or error object
	* @access private 
	*/
	function _connect_db($dbms, $dbhost, $dbuser, $dbpasswd, $dbname)
	{
		switch($dbms)
		{
			case 'mysql':
			case 'mysql4':
				$type = 'mysql';
				break;
			case 'postgres':
				$type = 'postgres';
				break;
			case 'mssql':
				$type = 'mssql';
				break;
			case 'msaccess':
				$type = 'access';
				break;
			case 'mssql-odbc':
				$type = 'odbc_mssql';
				break;
		}

		if (empty($type)) return m2f::raise_error('Database type "' . $dbms . '" is not valid in phpBB config file.', __LINE__, __FILE__);

		$this->conn = adoNewConnection($type);
		
		$er = error_reporting(1); // have to do this cos of adodb weirdness - by default it generates a WARNING error when the SQL connect fails
		if ($this->conn->connect($dbhost, $dbuser, $dbpasswd, $dbname) === FALSE) 
		{
			$error = ADODB_Pear_Error();
			return m2f::raise_error('Cannot connect to the database with settings in config.php. Error returned was: "' . $error->message . '".', __LINE__, __FILE__);
		}
		error_reporting($er);
		
		return TRUE;
	}

	/** 
	* Check forum_id is valid
	*  
	* @param int forum_id
	* @return TRUE or error object
	* @access public 
	*/
	function check_forum_id($forum_id)
	{
		$sql = 'SELECT * FROM ' . FORUMS_TABLE . " WHERE forum_id = '$forum_id'";
		if ( !$this->conn->sql_query($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB check_forum_id function: "' . $sql . '".', __LINE__, __FILE__);
		}
		if (!$this->conn->sql_numrows())
		{
			return m2f::raise_error('Forum id ' . $forum_id . ' does not exist.', __LINE__, __FILE__);
		}
		
		return TRUE;
	}


	/** 
	* Insert the post to the phpBB database
	*  
	* @param 
	* @return 
	* @access public 
	*/
	function insert($message, $subject, $forum_id, $user_id, $username, $topic_id, $user_attach_sig = TRUE, $current_time = NULL)
	{
		// get necessary variables into global scope before including phpBB files
		global 	$board_config,
						$html_entities_match, $html_entities_replace, $unhtml_specialchars_match, $unhtml_specialchars_replace;
		
		// include the faked phpBB functions file
		include_once('phpbb_functions.php');

		// include remaining phpBB files
		$table_prefix = $this->table_prefix;
		include_once($this->phpbb_root_path . 'includes/constants.' . $this->phpEx);
		
		include_once($this->phpbb_root_path . 'includes/bbcode.' . $this->phpEx);
		include_once($this->phpbb_root_path . 'includes/functions_post.' . $this->phpEx);
		include_once($this->phpbb_root_path . 'includes/functions_search.' . $this->phpEx);

		// variables which can be added to function declaration if necessary
		$topic_type = POST_NORMAL;
		$do_notification = FALSE;
		$notify_user = FALSE;
		$error_die_function = '';
		$html_on = 0;
		$bbcode_on = 1;
		$smilies_on = 1;
		$topic_vote = 0; 
		$poll_title = '';
		$poll_options = '';
		$poll_length = '';
		
		$mode = is_null($topic_id) ? 'newtopic' : 'reply';

		// board config
		$sql = "SELECT * FROM " . CONFIG_TABLE;
		if (FALSE === ($result = $this->conn->execute($sql)))
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		$board_config = $result->GetAssoc();


		// Borrowed from phpBB - common.php
		$client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );
		$user_ip = encode_ip($client_ip);

		$bbcode_uid = ($bbcode_on) ? make_bbcode_uid() : ''; 
		$current_time = (!$current_time) ? time() : $current_time;

		foreach (array('message', 'subject', 'username') as $var)
		{
			$$var = addslashes(unprepare_message(trim($$var)));
			$$var = prepare_message($$var, $html_on, $bbcode_on, $smilies_on, $bbcode_uid);
		}


		$this->conn->StartTrans();

		// if this is a new topic then insert the topic details
		if ($mode == 'newtopic')
		{
			$mode = 'newtopic'; 
			$sql = "INSERT INTO " . TOPICS_TABLE . " 
								(topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote) 
							VALUES 
								('$subject', " . $user_id . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)";
			if ( !$this->conn->execute($sql) )
			{
				return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
			}
			$topic_id = $this->conn->Insert_ID();
		}
	
		// insert the post details using the topic id
		$sql = "INSERT INTO " . POSTS_TABLE . " 
							(topic_id, forum_id, poster_id, post_username, post_time, poster_ip, enable_bbcode, enable_html, enable_smilies, enable_sig) 
						VALUES 
							($topic_id, $forum_id, " . $user_id . ", '$username', $current_time, '$user_ip', $bbcode_on, $html_on, $smilies_on, $user_attach_sig)";
		if ( !$this->conn->execute($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		$post_id = $this->conn->Insert_ID();
		
		// insert the actual post text for our new post
		$sql = "INSERT INTO " . POSTS_TEXT_TABLE . " 
							(post_id, post_subject, bbcode_uid, post_text) 
						VALUES 
							($post_id, '$subject', '$bbcode_uid', '$message')";
		if ( !$this->conn->execute($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		
		// update the post counts etc.
		$newpostsql = ($mode == 'newtopic') ? ',forum_topics = forum_topics + 1' : '';
		$sql = "UPDATE " . FORUMS_TABLE . " SET 
							forum_posts = forum_posts + 1,
							forum_last_post_id = $post_id
							$newpostsql 
						WHERE forum_id = $forum_id";
		if ( !$this->conn->execute($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		
		// update the first / last post ids for the topic
		$first_post_sql = ( $mode == 'newtopic' ) ? ", topic_first_post_id = $post_id  " : ' , topic_replies=topic_replies+1'; 
		$sql = "UPDATE " . TOPICS_TABLE . " SET 
					topic_last_post_id = $post_id 
					$first_post_sql
				WHERE topic_id = $topic_id";
		if ( !$this->conn->execute($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		
		// update the user's post count and commit the transaction
		$sql = "UPDATE " . USERS_TABLE . " SET 
					user_posts = user_posts + 1
				WHERE user_id = $user_id";
		if ( !$this->conn->execute($sql) )
		{
			return m2f::raise_error('Bad SQL statement in phpBB insert_post function: "' . $sql . '".', __LINE__, __FILE__);
		}
		
		$this->conn->CompleteTrans();


//
//		// add the search words for our new post
//		switch ($board_config['version'])
//		{
//			case '.0.0' : 
//			case '.0.1' : 
//			case '.0.2' : 
//			case '.0.3' : 
//				add_search_words($post_id, stripslashes($message), stripslashes($subject));
//				break;
//			
//			default :
//				add_search_words('', $post_id, stripslashes($message), stripslashes($subject));
//				break;
//		}
		
/*
		// do we need to do user notification
		if ( ($mode == 'reply') && $do_notification )
		{
			$post_data = array();
			user_notification($mode, $post_data, $subject, $forum_id, $topic_id, $post_id, $notify_user);
		}
*/		


		// if all is well then return the id of our new post
		return array('post_id' => $post_id, 'topic_id' => $topic_id);
	}

}




?>