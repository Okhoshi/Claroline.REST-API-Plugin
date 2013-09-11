<?php
/**
 * Web Service Controller - Generic library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class GenericWebServiceController
{

	/**
	 * Returns all the descriptions of a course.
	 * @throws InvalidArgumentException if the $cid in not provided.
	 * @webservice{/module/MOBILE/GEN/getResourcesList/cidReq}
	 * @ws_arg{method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of Descriptions object
	 */
	function getResourcesList( $args )
	{
		$module = isset( $args['module'] )
			?$args['module']
			:null
			;
		$cid = claro_get_current_course_id();
		if ( $cid == null || $module == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		
		$list = array();
		
		FromKernel::uses( 'core/linker.lib' );
		
		ResourceLinker::init();
		$locator = new ClarolineResourceLocator( $cid, $module, null, claro_get_current_group_id() );
		
		if (ResourceLinker::$Navigator->isNavigable( $locator ) )
		{
			$resourceList = ResourceLinker::$Navigator->getResourceList( $locator );
				
			foreach ( $resourceList as $lnk )
			{
				$inLocator = $lnk->getLocator();
				
				$item['title'] = $lnk->getName();
				$item['visibility'] = $lnk->isVisible();
				$item['url'] = str_replace(get_path('url'), "", get_path('rootWeb')) .ResourceLinker::$Resolver->resolve( $inLocator );
				
				if ( $inLocator->hasResourceId() )
				{
					$item['resourceId'] = $inLocator->getResourceId();
				}
				else
				{
					$item['resourceId'] = $item['url'];
				}
				
				if ( claro_is_allowed_to_edit() || $item['visibility'] )
				{
					$list[] = $item;
				}
			}
		}
		
		return $list;
	}

	/**
	 * Returns a single resquested decsription.
	 * @param array $args must contain 'resID' key with the resource identifier of the requested resource
	 * @throws InvalidArgumentException if one of the paramaters is missing
	 * @webservice{/module/MOBILE/CLANN/getSingleResource/cidReq/resId}
	 * @ws_arg{Method,getSingleResource}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @ws_arg{resID,Resource Id of requested resource}
	 * @return announce object (can be null if not visible for the current user)
	 *
	function getSingleResource( $args )
	{
		$resourceId = isset( $args['resID'] )
			?$args['resID']
			:null
			;
		$cid = claro_get_current_course_id();
		
		if ( $cid == null || $resourceId == null )
		{
			throw new InvalidArgumentException('Missing cid or resourceId argument!');
		}

		From::Module('CLXXX')->uses('');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());

		if ( $item = )
		{
			$notified = $claroNotification->isANotifiedRessource($cid,
					$date,
					claro_get_current_user_id(),
					claro_get_current_group_id(),
					get_tool_id_from_module_label('CLANN'),
					$item['id'],
					false);
		
			$item['visibility'] = ($item['visibility'] != 'HIDE');
			$item['resourceId'] = $item['id'];
			$item['notifiedDate'] = $notified
									?$date
									:null;
			unset($item['id']);
			
			return (claro_is_allowed_to_edit() || $item['visibility'])
				?$item
				:null
				;
		}
		else
		{
			throw new RuntimeException('Resource not found', 404);
		}
	}*/
}
