<?php
/**
 * Web Service Controller - Resources Download Dispatcher
 *
 * @version     MOBILE 1 $Revision: 10 $ - Claroline 1.11
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
	$pathInfo = $row['requestedPath'];
	$uid = $row['userId'];
	$canRetry = $row['canRetry'];
	$wasFolder = $row['wasFolder'];

	$extension = get_file_extension($pathInfo);
	$mimeType = get_mime_on_ext($pathInfo);

	if ( $canRetry )
	{
		$sql = 'UPDATE `' . $tableName . '` SET `canRetry` = \'0\' WHERE token = \'' . claro_sql_escape($token) . '\'';
		Claroline::getDatabase()->exec($sql);
	}
	
	if ( get_conf('useSendfile', true) && ( $mimeType != 'text/html' || $extension == 'url' ) || $wasFolder )
	{
		if ( claro_send_file( $pathInfo )  !== false )
		{
			$claroline->notifier->event('download', array( 'data' => array('url' => $requestUrl) ) );
			
			if ( $wasFolder )
			{
				unlink($pathInfo);
			}
			
			if ( !$canRetry ) {
				$sql = 'DELETE FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\'';
				Claroline::getDatabase()->exec($sql);
			}
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

		$sql = 'DELETE FROM `' . $tableName . '` WHERE token = \'' . claro_sql_escape($token) . '\'';
		Claroline::getDatabase()->exec($sql);
		
		// redirect to document
		claro_redirect($document_url);
	}
}
else
{
	header('HTTP/1.1 404 Not Found');
}

//Clean left zip here
$sql = 'SELECT * FROM `' . $tableName . '` WHERE ADDTIME(`requestTime`,\'0 0:0:30\') < NOW() AND NOT `wasFolder` = \'0\'';
$result = Claroline::getDatabase()->query($sql);
while ( ($row = $result->fetch()) !== false )
{
	if ( is_file($row['requestedPath']) )
	{
		unlink($row['requestedPath']);
	}
}

$sql = 'DELETE FROM `' . $tableName . '` WHERE ADDTIME(`requestTime`,\'0 0:0:30\') < NOW()';
Claroline::getDatabase()->exec($sql);