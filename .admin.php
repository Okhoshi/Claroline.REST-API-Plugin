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

require_once '../../claroline/inc/claro_init_global.inc.php';

if(!calro_is_platform_admin()){
	claro_die('Not Allowed');
}


?>