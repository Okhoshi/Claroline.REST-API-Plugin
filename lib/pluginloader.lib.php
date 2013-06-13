<?php
/**
 * Web Service Controller - Plugin Loader
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

class PluginLoader
{
	
	private $pluginsPath;
	
	/**
	 * Instanciates the Plugin Loader with $path as plugins directory
	 * @param string $path relative path to the plugins directory
	 */
	public function __construct( $path )
	{
		$this->pluginsPath = $path;
	}
	
	/**
	 * Checks the Web Service Controller for the requested module is installed, and then instanciates it.
	 * @param string $requestedLabel the module label which must be instantiated
	 * @return WebServiceController object|boolean a new instance of the Web Service Controller requested if found, else false
	 */
	public function load( $requestedLabel )
	{
		$installed = $this->loadInstalledPlugins();
		$upperLabel = strtoupper($requestedLabel);
		if(array_key_exists($upperLabel, $installed))
		{
			require_once $this->pluginsPath . $installed[$upperLabel];
			$module = $upperLabel . 'WebServiceController';
			return new $module;
		} else {
			return $this->load( 'GENERIC' );
		}
	}
	
	private function loadInstalledPlugins()
	{
		$installed = array();
		foreach ( new DirectoryIterator($this->pluginsPath) as $pluginLibs )
		{
			if ( !$pluginLibs->isDot() )
			{
				$moduleName = strtoupper(str_replace("webservicecontroller.lib.php", "",$pluginLibs->getFilename()));
				$installed[$moduleName] = $pluginLibs->getFilename();
			}
		}
		return $installed;
	}
}