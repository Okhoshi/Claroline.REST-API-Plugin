<?php
	/**
	 * Web Services Provider Administration Page
	 *
	 * @version     MOBILE 1 $Revision: 1 $ - Claroline 1.11
	 * @copyright   2001-2012 Universite Catholique de Louvain (UCL)
	 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
	 * @package     MOBILE
	 * @author      Quentin Devos <q.devos@student.uclouvain.be>
	 */

	$tlabelReq = 'MOBILE';
	
	$pageTitle = array('mainTitle' => 'Web Services Provider Administration', 'subTitle' => 'Web Services Libraries');
	
	require_once '../../claroline/inc/claro_init_global.inc.php';

	FromKernel::uses('file.lib', 'fileUpload.lib', 'thirdparty/pclzip/pclzip.lib');
	
	From::Module($tlabelReq)->uses('Admin.lib');

	if(!claro_is_platform_admin()){
		claro_die('Not Allowed');
	}
	
	$tableName = get_module_main_tbl(array('mobile_libs'));
	$tableName = $tableName['mobile_libs'];
	
	$allowedCommandList = array(
		'AddForm',
		'AddLib',
		'DeleteLib'
	);

	$cmd = isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $allowedCommandList)
		? $_REQUEST['cmd']
		: '';
		
	$libFile = isset($_REQUEST['libFile'])
		? $_REQUEST['libFile']
		: '';
		
	$dialogBox = new DialogBox();

	if(!empty($cmd)){
		switch( $cmd ){
			case 'AddForm':
			{
				$form = new ModuleTemplate( $tlabelReq , 'form.tpl.php' );
				$dialogBox->form( $form->render() );
			} break;
            case 'DeleteLib':
            {
                if ( empty( $libFile ) )
                {
                    $errorMsg = get_lang('Missing Lib filename');
                }
                elseif ( ! deleteLibrary( $libFile ) )
                {
                    $errorMsg = get_lang('Impossible to delete Lib');
                }
                else
                {
                    $successMsg = get_lang('Lib deleted');
					$sql = "DELETE FROM `" . $tableName . "` WHERE `lib_file` LIKE '" . $libFile . "';";
					Claroline::getDatabase()->exec($sql);
                }
            } break;
            case 'AddLib':
            {
                if ( ! treat_uploaded_file( $_FILES['libFile']
											, LIB_DIRECTORY
											, ''
											, 10000000
											, 'unzip'
											, true))
                {
                    $errorMsg = claro_failure::get_last_failure();
                }
                else
                {    
				 $file = explode('.',$_FILES['libFile']['name'],-2);
				 $file = $file[0] . '.addlib.php';
				 
					if ( file_exists( LIB_DIRECTORY . '/' . $file ) )
					{
						include LIB_DIRECTORY . '/' . $file ;
					}
					else
					{
						claro_failure::set_failure('FILE_NOT_FOUND');
					}
					echo "('".$libName."','".$libFuncs."','".$libFile."')";
					if(isset($libFile) && !empty($libFile)){
						$sql = "REPLACE INTO `".$tableName."` (`lib_name`, `functions`, `lib_file`) VALUES ('".$libName."','".$libFuncs."','".$libFile."');";
						Claroline::getDatabase()->exec($sql);
						deleteLibrary( $file );
						$successMsg = get_lang( 'File added' );
					} else {
						$errorMsg = get_lang('Installation failed');
					}
                }
            } break;
		}
	}
	
	$sql = "SELECT lib_name, lib_file FROM `".$tableName."`;";
	$libList = claro_sql_query_fetch_all_rows($sql);
	
	if( isset( $successMsg ) )
    {
        $dialogBox->success( $successMsg );
    }
    
    if( isset( $errorMsg ) )
    {
        $dialogBox->error( $errorMsg );
    }
	
	$template = new ModuleTemplate( $tlabelReq , 'admin.tpl.php' );
    $template->assign( 'libList' , $libList );
    
    ClaroBreadCrumbs::getInstance()->append( $pageTitle[ 'mainTitle' ] , $_SERVER[ 'PHP_SELF' ] );
    ClaroBreadCrumbs::getInstance()->append( $pageTitle[ 'subTitle' ] );
    Claroline::getInstance()->display->body->appendContent( claro_html_tool_title( $pageTitle )
                                                          . $dialogBox->render()
                                                          . $template->render() );
														  
	echo Claroline::getInstance()->display->render();
?>