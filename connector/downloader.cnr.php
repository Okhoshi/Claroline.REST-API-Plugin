<?php
/**
 * Web Service Controller - Documents Downloader
 *
 * @version     MOBILE 1 $Revision: 8 $ - Claroline 1.11
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */


class MOBILE_Downloader extends Claro_Generic_Module_Downloader
{
	protected $cid;
	protected $uid;
	
	public function __construct( $moduleLabel, $cid, $uid )
	{
		$this->cid = $cid;
		$this->uid = $uid;
		$this->moduleLabel = $moduleLabel;
	}
	
	protected function isDocumentDownloadableInCourse( $requestedUrl )
	{
		if (claro_is_in_a_group())
		{
			$groupContext  = true;
			$courseContext = false;
			$is_allowedToEdit = claro_is_group_member() ||  claro_is_group_tutor() || claro_is_course_manager();
		}
		else
		{
			$groupContext  = false;
			$courseContext = true;
			$courseUserData = claro_get_course_user_privilege($this->cid, $this->uid);
			$is_allowedToEdit = $courseUserData['is_courseAdmin'];
		}

		if ($courseContext)
		{
			$courseTblList = get_module_course_tbl(array('document'), $this->cid);
			$tbl_document =  $courseTblList['document'];

			if ( strtoupper(substr(PHP_OS, 0, 3)) == "WIN" )
			{
				$modifier = '';
			}
			else
			{
				$modifier = 'BINARY ';
			}

			$sql = "SELECT visibility
			FROM `{$tbl_document}`
			WHERE {$modifier} path = '".claro_sql_escape($requestedUrl)."'";

			$docVisibilityStatus = claro_sql_query_get_single_value($sql);

			if ( ( ! is_null($docVisibilityStatus) ) // hidden document can only be viewed by course manager
					&& $docVisibilityStatus == 'i'
					&& ( ! $is_allowedToEdit ) )
			{
				return false;
			}
			else

			{
				return true;
			}
		}
		else
		{
			// ????
		}
	}

	public function getFilePath( $requestedUrl )
	{
		if ( ! is_null($this->cid) )
		{
			$courseInfo = claro_get_course_data($this->cid);
			$coursePath = $courseInfo['path'];
			
			if (claro_is_in_a_group() && claro_is_group_allowed())
			{
				$intermediatePath = get_path('coursesRepositorySys') . $coursePath . '/group/'.claro_get_current_group_data('directory');
			}
			else
			{
				$intermediatePath = get_path('coursesRepositorySys') . $coursePath . '/document';
			}
		}
		else
		{
			$intermediatePath = rtrim( str_replace( '\\', '/', get_path('rootSys') ), '/' ) . '/platform/document';
		}

		if ( get_conf('secureDocumentDownload') && $GLOBALS['is_Apache'] )
		{
			// pretty url
			$path = realpath( $intermediatePath . '/' . $requestedUrl);
		}
		else
		{
			// TODO check if we can remove rawurldecode
			$path = $intermediatePath
			. implode ( '/',
					array_map('rawurldecode', explode('/',$requestedUrl)));
		}

		return $path;
	}

	public function isAllowedToDownload( $requestedUrl )
	{
		if ( ! $this->isModuleAllowed() )
		{
			return false;
		}

		if ( ! is_null($this->cid) )
		{
			$courseUserPrivilege = claro_get_course_user_privilege( $this->cid, $this->uid );
			var_dump($courseUserPrivilege);
			if ( ! $courseUserPrivilege['is_courseMember'] )
			{
				pushClaroMessage('course not allowed', 'debug');
				return false;
			}
			else
			{

				if ( claro_is_in_a_group() )
				{
					if ( !claro_is_group_allowed() )
					{
						pushClaroMessage('group not allowed', 'debug');
						return false;
					}
					else
					{
						return true;
					}
				}
				else
				{
					return $this->isDocumentDownloadableInCourse($requestedUrl);
				}
			}
		}
		else
		{
			return false;
		}
	}
}
