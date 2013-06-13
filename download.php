<?php
/**
 * Web Service Controller - Resources Download Dispatcher
 *
 * @version     MOBILE 1 $Revision: 8 $ - Claroline 1.11
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

$tlabelReq = 'MOBILE';

require dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';

if ( !get_conf('activeWebService',true) )
{
	header('Service Unavailable',true,503);
	die();
}

if ( !( isset($_REQUEST['token']) ) || empty($_REQUEST['token']) )
{
	header('Missing Argument',true, 400);
	die();
}
elseif ( strlen($_REQUEST['token']) != 30 )
{
	header('Invalid Argument', true, 400);
	die();
}

$token = $_REQUEST['token'];
$tableName = get_module_main_tbl(array('mobile_tokens'));
$tableName = $tableName['mobile_tokens'];

$sql = 'SELECT * FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\' AND ADDTIME(`requestTime`,\'0 0:0:30\') > NOW()';
$result = Claroline::getDatabase()->query($sql);
if ( !$result->isEmpty() )
{
	$row = $result->fetch();
	$thisFile = $row['requestedPath'];
	$cid = $row['cid'];
	$uid = $row['userId'];
	$_course = claro_get_course_data( $cid );
	$uidData = claro_get_course_user_privilege( $cid, $uid );
	$is_allowedToEdit = $uidData['is_courseAdmin'];

	$baseWorkDir = get_path('coursesRepositorySys').claro_get_course_path($cid).'/document';

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

			$downloadArchiveName = get_conf('siteName');

			if ( isset($cid) )
			{
				$downloadArchiveName .= '.' . $_course['officialCode'];
			}

			if (claro_is_in_a_group())
			{
				$downloadArchiveName .= '.' . claro_get_current_group_data('name');
			}
				
			$bnFile = basename($thisFile);
			if (empty($thisFile))
			{
				$downloadArchiveName .= '.complete';
			}
			else
			{
				$downloadArchiveName .= '.' . $thisFile;
			}

			$downloadArchiveName .= '.zip';
			$downloadArchiveName = str_replace('/', '', $downloadArchiveName);

			if ( $downloadArchiveName == '.zip')
			{
				$downloadArchiveName = get_lang('Documents and Links') . '.zip';
			}

			$downloadArchive     = new PclZip($downloadArchiveFile);

			$downloadArchive->add($filePathList,
					PCLZIP_OPT_REMOVE_PATH,
					$baseWorkDir . $thisFile);

			if ( file_exists($downloadArchiveFile) )
			{
				/*
				 * SEND THE ZIP ARCHIVE FOR DOWNLOAD
				*/

				claro_send_file( $downloadArchiveFile, $downloadArchiveName );
				unlink($downloadArchiveFile);
				
				$sql = 'DELETE FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\'';
				Claroline::getDatabase()->exec($sql);
			}
			else
			{
				header('HTTP/1.1 500 Internal Server Error');
			}
		}
		else
		{
			header('HTTP/1.1 403 Forbidden');
		}
	}
	elseif ( is_file($baseWorkDir.$thisFile) )
	{
		require_once get_path('incRepositorySys') . '/lib/file/downloader.lib.php';

		$claroline->notification->addListener( 'download', 'trackInCourse' );
		$dialogBox = new DialogBox();

		$connectorPath = secure_file_path(get_module_path( $tlabelReq ) . '/connector/downloader.cnr.php');
		require_once $connectorPath;
		$className = $tlabelReq.'_Downloader';
		$downloader = new $className( $tlabelReq, $cid, $uid );

		$isDownloadable = true;

		if ( $downloader && $downloader->isAllowedToDownload( $thisFile ) )
		{
			$pathInfo = $downloader->getFilePath( $thisFile );

			// use slashes instead of backslashes in file path
			if (claro_debug_mode() )
			{
				pushClaroMessage('<p>File path : ' . $pathInfo . '</p>','pathInfo');
			}

			$pathInfo = secure_file_path( $pathInfo );

			// Check if path exists in course folder
			if ( ! file_exists($pathInfo) || is_dir($pathInfo) )
			{
				$isDownloadable = false ;

				$dialogBox->title( get_lang('Not found') );
				$dialogBox->error( get_lang('The requested file <strong>%file</strong> was not found on the platform.',
						array('%file' => basename($pathInfo) ) ) );
			}
		}
		else
		{
			$isDownloadable = false;

			pushClaroMessage('downloader said no!', 'debug');

			$dialogBox->title( get_lang('Not allowed') );
		}
		
		// Output section
		if ( $isDownloadable )
		{
			error_reporting(0);
			// end session to avoid lock
			session_write_close();

			$extension = get_file_extension($pathInfo);
			$mimeType = get_mime_on_ext($pathInfo);

			if( get_conf('useSendfile', true) && ( $mimeType != 'text/html' || $extension == 'url' ) )
			{
				if ( claro_send_file( $pathInfo )  !== false )
				{
					$claroline->notifier->event('download', array( 'data' => array('url' => $requestUrl) ) );
					
					//$sql = 'DELETE FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\'';
					//Claroline::getDatabase()->exec($sql);
				}
				else
				{
					header('HTTP/1.1 404 Not Found');
					claro_die( get_lang('File download failed : %failureMSg%',
					array( '%failureMsg%' => claro_failure::get_last_failure() ) ) );
				}
			}
			else
			{	
				if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
				{
					$rootSys =  str_replace( '//', '/', strtolower( str_replace('\\', '/', $rootSys) ) );
					$pathInfo = strtolower( str_replace('\\', '/', $pathInfo) );
				}

				$document_url = str_replace($rootSys,$urlAppend.'/',$pathInfo);

				// redirect to document
				claro_redirect($document_url);
				
				$sql = 'DELETE FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\'';
				Claroline::getDatabase()->exec($sql);
			}
		}
		else
		{
			header('HTTP/1.1 404 Not Found');
		}
	}
}
else
{
	header('HTTP/1.1 404 Not Found');
}

$sql = 'DELETE FROM `' . $tableName . '` WHERE ADDTIME(`requestTime`,\'0 0:0:30\') < NOW()';
Claroline::getDatabase()->exec($sql);