<?php
/**
 * Web Service Controller - Plugin Loader
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

class PluginLoader {
	
	private $tlabelReq = 'MOBILE';
	
	/**
	 * Checks the Web Service Controller for the requested module is installed, and then instanciates it.
	 * @param string $requestedLabel the module label which must be instantiated
	 * @return WebServiceController object|boolean a new instance of the Web Service Controller requested if found, else false
	 */
	public function load($requestedLabel){
		$installed = $this->loadInstalledPlugins();
		if(array_key_exists(strtolower($requestedLabel), $installed)){
			From::Module($this->tlabelReq)->uses('/plugins/' . $installed[strtolower($requestedLabel)]);
			$module = strtoupper($requestedLabel) . 'WebServiceController';
			return new $module;
		} else {
			return false;
		}
	}
	
	private function loadInstalledPlugins(){
		$installed = array();
		foreach (new DirectoryIterator('./lib/plugins') as $pluginLibs){
			if(!$pluginLibs->isDot()){
				$moduleName = strtolower(str_replace("webservicecontroller.lib.php", "",$pluginLibs->getFilename()));
				$installed[$moduleName] = $pluginLibs->getFilename();
			}
		}
		return $installed;
	}
}