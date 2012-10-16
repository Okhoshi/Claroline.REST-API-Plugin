<?php
	/**
	 * Web Services Provider Administration Library
	 *
	 * @version     MOBILE 1 $Revision: 1 $ - Claroline 1.11
	 * @copyright   2001-2012 Universite Catholique de Louvain (UCL)
	 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
	 * @package     MOBILE
	 * @author      Quentin Devos <q.devos@student.uclouvain.be>
	 */
	
    define ( 'LIB_DIRECTORY', dirname(__FILE__));
	
    static function deleteLibrary( $file )
    {
        FromKernel::uses('fileManage.lib');
		
        if ( file_exists( LIB_DIRECTORY . '/' . $file ) )
        {
            return claro_delete_file( LIB_DIRECTORY . '/' . $file );
        }
        else
        {
            return claro_failure::set_failure('FILE_NOT_FOUND');
        }
    }
?>