<?php
/**
 * Web Service Controller - CLDOC plugin
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

class CLDOCWebServiceController
{

	/**
	 * Returns the documents contained into args['curDirPath']
	 * @param array $args array of parameters, can contain :
	 * - (boolean) recursive : if true, return the content of the requested directory and its subdirectories, if any. Default = true
	 * - (String) curDirPath : returns the content of the directory specified by this path. Default = '' (root)
	 * @throws InvalidArgumentException if $cid is missing
	 * @webservice{/module/MOBILE/CLDOC/getResourceList/cidReq/[?recursive=BOOL&curDirPath='']}
	 * @ws_arg{Method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @ws_arg{recursive,[Optionnal: if true\, return the content of the requested directory and its subdirectories\, if any. Default = true]}
	 * @ws_arg{curDirPath,[Optionnal: returns the content of the directory specified by this path. Default = '' (root)]}
	 * @return array of document object
	 */
	function getResourcesList( $args )
	{
		$recursive = isset($args['recursive'])
		?$args['recursive']
		:true
		;
		$curDirPath = isset($args['curDirPath'])?$args['curDirPath']:'';
		$cid = claro_get_current_course_id();
		if ( is_null($cid) )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		elseif ( !claro_is_course_allowed )
		{
			throw new RuntimeException('Not allowed', 403);
		}

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


		if ( !defined('A_DIRECTORY') )
		{
			define('A_DIRECTORY', 1);
		}
		if ( !defined('A_FILE') )
		{
			define('A_FILE', 2);
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
				$fileAttributeList['resourceId'] = $thisFile;
				$tmp = explode('/',$thisFile);

				if( is_dir($baseWorkDir.$thisFile) )
				{
					$fileYear = date('n', time()) < 8
								?date('Y',time()) - 1
								:date('Y',time());
				
					$fileAttributeList['title'] = $tmp[count($tmp) -1];
					$fileAttributeList['isFolder'] = true;
					$fileAttributeList['type'] = A_DIRECTORY;
					$fileAttributeList['size'] = 0;
					$fileAttributeList['date'] = $fileYear . '-09-20';
					$fileAttributeList['extension'] = "";
					$fileAttributeList['url'] = null;
				}
				elseif( is_file($baseWorkDir.$thisFile) )
				{
					$fileAttributeList['title'] = implode('.',explode('.',$tmp[count($tmp)-1],-1));
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
				$notified = $claroline->notification->isANotifiedDocument($cid,$date,claro_get_current_user_id(),$groupId, $docToolId, $fileAttributeList, false);
				
				$fileAttributeList['notifiedDate'] = $notified
													?$date
													:$fileAttributeList['date'];
													
				$d = new DateTime($date);
				$d->sub(new DateInterval('P1D'));
				$fileAttributeList['seenDate'] = $d->format('Y-m-d');
				
				if ($fileAttributeList['visibility'] || claro_is_allowed_to_edit()) {
					$fileList[] = $fileAttributeList;
				}
			} // end foreach $filePathList
		}
		if ( $recursive )
		{
			foreach ( $fileList as $thisFile )
			{
				if ( $thisFile['type'] == A_DIRECTORY )
				{
					$args = array('curDirPath' => $thisFile['path'],'recursive' => true);
					$new_list = $this->getResourcesList($args);
					$fileList = array_merge($fileList,$new_list);
				}
			}
		}

		return $fileList;
	}

	function getSingleResource( $args )
	{
		$tlabelReq = 'MOBILE';
		
		$thisFile = isset( $args['resID'] )
		?$args['resID']
		:null
		;

		$cid = claro_get_current_course_id();

		if ( is_null($cid) || is_null($thisFile) )
		{
			throw new InvalidArgumentException('Missing cid or resourceId argument!');
		}

		if ( claro_is_course_allowed() )
		{
			/* INITIALISATION
			 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
			$tableName = get_module_main_tbl(array('mobile_tokens'));
			$tableName = $tableName['mobile_tokens'];
			$limit = 5;

			$baseWorkDir = get_path('coursesRepositorySys').claro_get_course_path($cid).'/document';

			if( is_dir($baseWorkDir.$thisFile) || is_file($baseWorkDir.$thisFile) )
			{
				
				if( is_dir($baseWorkDir.$thisFile) )
				{
					if ( ( $is_allowedToEdit || get_conf('cldoc_allowNonManagersToDownloadFolder', true) )
							|| ( get_conf('cldoc_allowNonManagersToDownloadFolder', true)
									&& get_conf( 'cldoc_allowAnonymousToDownloadFolder', true ) )
					)
					{
						/*
						 * PREPARE THE FILE COLLECTION
						*/

						if (! $is_allowedToEdit )
						{
							// Build an exclude file list to prevent simple user
							// to see document contained in "invisible" directories
							$searchExcludeList = getInvisibleDocumentList($baseWorkDir);
						}
						else
						{
							$searchExcludeList = array();
						}

						$filePathList = claro_search_file(search_string_to_pcre(''),
								$baseWorkDir . $thisFile,
								true,
								'FILE',
								$searchExcludeList);

						/*
						 * BUILD THE ZIP ARCHIVE
						*/

						require_once get_path('incRepositorySys') . '/lib/thirdparty/pclzip/pclzip.lib.php';

						// Build archive in tmp course folder

						$downloadArchivePath = get_conf('cldoc_customTmpPath', '');

						if ( empty($downloadArchivePath) )
						{
							$downloadArchivePath = get_path('coursesRepositorySys') . claro_get_course_path() . '/tmp/zip';
							$downloadArchiveFile = $downloadArchivePath . '/' . uniqid('') . '.zip';
						}
						else
						{
							$downloadArchiveFile = rtrim( $downloadArchivePath, '/' )
							. '/' . claro_get_current_course_id()
							. '_CLDOC_' . uniqid('') . '.zip';
						}

						if ( ! is_dir( $downloadArchivePath ) )
						{
							mkdir( $downloadArchivePath, CLARO_FILE_PERMISSIONS, true );
						}

						$downloadArchive     = new PclZip($downloadArchiveFile);

						$downloadArchive->add($filePathList,
								PCLZIP_OPT_REMOVE_PATH,
								$baseWorkDir . $thisFile);

						if ( file_exists($downloadArchiveFile) )
						{
							$pathInfo = $downloadArchiveFile;
						}
						else
						{
							throw new RuntimeException('Internal Server Error', 500);
						}
					}
					else
					{
						throw new RuntimeException('Not allowed', 403);
					}
				}
				elseif ( is_file($baseWorkDir.$thisFile) )
				{
					require_once get_path('incRepositorySys') . '/lib/file/downloader.lib.php';

					Claroline::getInstance()->notification->addListener( 'download', 'trackInCourse' );

					$connectorPath = secure_file_path(get_module_path( $tlabelReq ) . '/connector/downloader.cnr.php');
					require_once $connectorPath;
					$className = $tlabelReq.'_Downloader';
					$downloader = new $className( $tlabelReq, $cid, claro_get_current_user_id() );
					
					if ( $downloader && $downloader->isAllowedToDownload( $thisFile ) )
					{
						$pathInfo = $downloader->getFilePath( $thisFile );

						$pathInfo = secure_file_path( $pathInfo );
						
						// Check if path exists in course folder
						if ( ! file_exists($pathInfo) || is_dir($pathInfo) )
						{
							throw new RuntimeException('Resource not found', 404);
						}
					}
					else
					{
						throw new RuntimeException('Not allowed', 403);
					}
				}
				for ($result = $try = 0; $try < $limit && $result < 1; $try++)
				{
					/* Create token and register into the db. Retry until the registration complete or fail $limit times.
					 = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */
					$token = bin2hex(openssl_random_pseudo_bytes(15));
					$sql = 'REPLACE INTO `' . $tableName . '` (`userId`, `token`, `requestedPath`, `requestTime`, `wasFolder`, `canRetry`) '
						 .	   'VALUES (\'' . claro_get_current_user_id() . '\', \'' . $token . '\', \'' . claro_sql_escape($pathInfo) . '\', NOW(), \'' . (is_dir($baseWorkDir.$thisFile)? 1 : 0) . '\' , \'' . ($args['platform'] == 'WP'? 1 : 0) . '\');';

					$result = Claroline::getDatabase()->exec($sql);
				}
					
				$response['token'] = $try == $limit
									 ?null
									 :$token
									 ;
				return $response;
			}
			else
			{
				throw new RuntimeException('Resource not found', 404);
			}
		}
		else
		{
			throw new RuntimeException('Not allowed', 403);
		}
	}
}
