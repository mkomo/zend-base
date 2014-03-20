<?php
function setupApplication(){
	$config = parse_ini_file(realpath(dirname(__FILE__) . '/../config/config.ini'), true);
	if ($config["zend_path"]){
		set_include_path(implode(PATH_SEPARATOR, array(
		    $config["zend_path"],
		    get_include_path(),
		)));
	}
	
	// Define path to application directory
	defined('APPLICATION_PATH')
		|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
	
	// Define application environment
	defined('APPLICATION_ENV')
		|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
	
	// Ensure library/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
	)));
	
	// Add custom config to application config
	require_once 'Zend/Config/Ini.php';
	$application_ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
	$application_config = $application_ini->toArray();
	$application_config['resources']['db']['params'] = $config['db'];
	
	/** Zend_Application */
	require_once 'Zend/Application.php';
	
	// Create application, bootstrap, and run
	$application = new Zend_Application(
		APPLICATION_ENV,
		$application_config
	);
	return $application;
}

if (!debug_backtrace()) {
	setupApplication()->bootstrap()->run();
}