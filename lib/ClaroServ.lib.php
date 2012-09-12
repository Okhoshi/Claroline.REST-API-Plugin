<?php
class ClaroWeb {
	    // Singleton instance
    private static $instance = false; // this class is a singleton

	static function getInstance(){
        if ( ! self::$instance )
        {
            self::$instance = new self;
        }

        return self::$instance;
	}
	
	function getUserData() {
		$userData = claro_get_current_user_data();
		unset($userData['authSource'],
			  $userData['creatorId'],
			  $userData['lastLogin'],
			  $userData['mail'],
			  $userData['officialEmail'],
			  $userData['phone'],
			  $userData['picture']);
		$userData['platformName'] = get_conf('siteName', "Claroline");
		$userData['institutionName'] = get_conf('institution_name',"");
		$userData['platformTextAuth'] = trim(strip_tags(claro_text_zone::get_content("textzone_top.authenticated")));
		$userData['platformTextAnonym'] = trim(strip_tags(claro_text_zone::get_content("textzone_top.anonymous")));
		return $userData;
	}

	function getCourseList(){
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$courseList = array();
		$notifiedCourses = $claroNotification->getNotifiedCourses($date,claro_get_current_user_id());
		foreach (get_user_course_list(claro_get_current_user_id()) as $course){
			$course_data = claro_get_course_data($course['sysCode']);
			$course['officialEmail'] = $course_data['email'];
			$course['notified'] = in_array($course['sysCode'],$notifiedCourses);
			$courseList[] = $course;
		}
		return $courseList;
	}
	
	function getCourseToolList(){
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$course = array();
		$course['sysCode'] = claro_get_current_course_id();
		$course['isAnn'] = false;
		$course['annNotif'] = false;
		$course['isDnL'] = false;
		$course['dnlNotif'] = false;
		$notifiedTools = $claroNotification->getNotifiedTools(claro_get_current_course_id(),$date,claro_get_current_user_id());
		foreach(claro_get_course_tool_list($course['sysCode'],claro_get_current_user_profile_id_in_course(claro_get_current_course_id())) as $tool){ 
			switch($tool['label']){
				case 'CLANN':
					$course['isAnn'] = ($tool['visibility'] || claro_is_allowed_to_edit());
					$course['annNotif'] = in_array($tool['tool_id'],$notifiedTools);
					break;
				case 'CLDOC':
					$course['isDnL'] = ($tool['visibility'] || claro_is_allowed_to_edit());
					$course['dnlNotif'] = in_array($tool['tool_id'],$notifiedTools);
					break;
				default:
					break;
			}
		}
		return $course;
	}
	
	function getDocList($cid, $curDirPath = '', $recursive = true){
		/* READ CURRENT DIRECTORY CONTENT
		= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
		$claroline = Claroline::getInstance();
		$exSearch = false;
		$groupContext     = FALSE;
		$courseContext    = TRUE;
		$dbTable = get_module_course_tbl(array('document'),$cid);
		$dbTable = $dbTable['document'];
		$docToolId = get_course_tool_id('CLDOC');
		$groupId = claro_get_current_group_id();
		$date = $claroline->notification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		
	
		if(!defined('A_DIRECTORY')){
			define('A_DIRECTORY', 1);
		}
		if(!defined('A_FILE')){
			define('A_FILE',      2);
		}
			
		$baseWorkDir = get_path('coursesRepositorySys').claro_get_course_path($cid).'/document';
	
		/*----------------------------------------------------------------------------
		 LOAD FILES AND DIRECTORIES INTO ARRAYS
		----------------------------------------------------------------------------*/
	
		$searchPattern   = '';
		$searchRecursive = false;
		$searchBasePath  = $baseWorkDir.$curDirPath;
		$searchExcludeList = array();
	
		$searchBasePath = secure_file_path( $searchBasePath);
	
		if (false === ($filePathList = claro_search_file( search_string_to_pcre($searchPattern),$searchBasePath,$searchRecursive,'ALL',$searchExcludeList)))
		{
			switch (claro_failure::get_last_failure())
			{
				case 'BASE_DIR_DONT_EXIST' :
					pushClaroMessage($searchBasePath . ' : call to an unexisting directory in groups');
					break;
				default :
					pushClaroMessage('Search failed');
					break;
			}
			$filePathList=array();
		}
	
		for ($i =0; $i < count($filePathList); $i++ )
		{
			$filePathList[$i] = str_replace($baseWorkDir, '', $filePathList[$i]);
		}
	
		if ($exSearch && $courseContext)
		{
			$sql = "SELECT path FROM `".$dbTable."`
			WHERE comment LIKE '%".claro_sql_escape($searchPattern)."%'";
	
			$dbSearchResult = claro_sql_query_fetch_all_cols($sql);
	
			$filePathList = array_unique( array_merge($filePathList, $dbSearchResult['path']) );
		}
	
		$fileList = array();
	
		if ( count($filePathList) > 0 )
		{
			/*--------------------------------------------------------------------------
			 SEARCHING FILES & DIRECTORIES INFOS ON THE DB
			------------------------------------------------------------------------*/
	
			/*
			 * Search infos in the DB about the current directory the user is in
			*/
	
			if ($courseContext)
			{
				$sql = "SELECT `path`, `visibility`, `comment`
				FROM `".$dbTable."`
				WHERE path IN ('".implode("', '", array_map('claro_sql_escape', $filePathList) )."')";
	
				$xtraAttributeList = claro_sql_query_fetch_all_cols($sql);
			}
			else
			{
				$xtraAttributeList = array('path' => array(), 'visibility'=> array(), 'comment' => array() );
			}
	
			foreach($filePathList as $thisFile)
			{
				$fileAttributeList['cours']['sysCode'] = $cid;
				$fileAttributeList['path'] = $thisFile;
				$tmp = explode('/',$thisFile);
	
				if( is_dir($baseWorkDir.$thisFile) )
				{
					$fileAttributeList['name'] = $tmp[count($tmp) -1];
					$fileAttributeList['isFolder'] = true;
					$fileAttributeList['type'] = A_DIRECTORY;
					$fileAttributeList['size'] = false;
					$fileAttributeList['date'] = date('Y-m-d',time());
					$fileAttributeList['extension'] = "";
					$fileAttributeList['url'] = null;
				}
				elseif( is_file($baseWorkDir.$thisFile) )
				{
					$fileAttributeList['name'] = implode('.',explode('.',$tmp[count($tmp)-1],-1));
					$fileAttributeList['type'] = A_FILE;
					$fileAttributeList['isFolder'] = false;
					$fileAttributeList['size'] = claro_get_file_size($baseWorkDir.$thisFile);
					$fileAttributeList['date'] = date('Y-m-d',filemtime($baseWorkDir.$thisFile));
					$fileAttributeList['extension'] = get_file_extension($baseWorkDir.$thisFile);
					$fileAttributeList['url'] = $_SERVER['SERVER_NAME'] . claro_get_file_download_url($thisFile);
				}
	
				$xtraAttributeKey = array_search($thisFile, $xtraAttributeList['path']);
	
				if ($xtraAttributeKey !== false)
				{
					$fileAttributeList['description'] = $xtraAttributeList['comment'   ][$xtraAttributeKey];
					$fileAttributeList['visibility' ] = ($xtraAttributeList['visibility'][$xtraAttributeKey] == 'v');
	
					unset( $xtraAttributeList['path'][$xtraAttributeKey] );
				}
				else
				{
					$fileAttributeList['description'] = null;
					$fileAttributeList['visibility' ] = true;
				}
				$fileAttributeList['notified'] = $claroline->notification->isANotifiedDocument($cid,$date,claro_get_current_user_id(),$groupId, $docToolId, $fileAttributeList, false);
	
				$fileList[] = $fileAttributeList;
			} // end foreach $filePathList
		}
		if($recursive){
			foreach ($fileList as $thisFile){
				if($thisFile['type'] == A_DIRECTORY){
					$new_list = ClaroWeb::getDocList($cid, $thisFile['path'],true);
					$fileList = array_merge($fileList,$new_list);
				}
			}
		}
		
		return $fileList;
	}
	
	function getAnnounceList($cid){
		From::Module('CLANN')->uses('announcement.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$annList = array();
		foreach ( announcement_get_item_list(array('course'=>$cid)) as $announce ) {
			$announce['notified'] = $claroNotification->isANotifiedRessource($cid,
				$date,
				claro_get_current_user_id(),
				claro_get_current_group_id(),
				get_tool_id_from_module_label('CLANN'),
				$announce['id'],
				false);
			$announce['visibility'] = ($announce['visibility'] != 'HIDE');
			$announce['cours']['sysCode'] = $cid;
			$announce['date'] = $announce['time'];
			$announce['ressourceId'] = $announce['id'];
			$announce['content'] = trim(strip_tags($announce['content']));
			unset($announce['id']);
			if(claro_is_allowed_to_edit() || $announce['visibility'])
				$annList[] = $announce;
		}
		return $annList;
	}
	
	function getSingleAnnounce($cid, $resourceId){
		$claroNotification = Claroline::getInstance()->notification;
		From::Module('CLANN')->uses('announcement.lib');
		$announce = announcement_get_item($resourceId,$cid);
		$announce['visibility'] = ($announce['visibility'] != 'HIDE');
		$announce['content'] = trim(strip_tags($announce['content']));
		$announce['cours']['sysCode'] = $cid;
		$announce['ressourceId'] = $announce['id'];
		$announce['date'] = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$announce['notified'] = $claroNotification->isANotifiedRessource($cid,
				$date,
				claro_get_current_user_id(),
				claro_get_current_group_id(),
				get_tool_id_from_module_label('CLANN'),
				$announce['id'],
				false);
		unset($announce['id']);
		return (claro_is_allowed_to_edit() || $announce['visibility'])?$announce:null;
	}
	
	function getUpdates(){
		$claroNotification = Claroline::getInstance()->notification;
		$gid = 0;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$result = array();
		foreach($claroNotification->getNotifiedCourses($date, claro_get_current_user_id()) as $cid){
			foreach ( $claroNotification->getNotifiedTools($cid, $date, claro_get_current_user_id()) as $tid ) {
				$result[$cid][get_module_label_from_tool_id($tid)] = $claroNotification->getNotifiedRessources($cid,$date,claro_get_current_user_id(),$gid,$tid);
			}
		}
		return $result;
	}
}
?>