<?php
define("CONFIG_PATH", realpath(dirname(__FILE__) . '/config/config.ini'));

try {
	$config = parse_ini_file(CONFIG_PATH, true);
	$config = updateConfig($config);
	write_file(array_to_ini($config), CONFIG_PATH);
	
	require_once 'public/index.php';
	$application = setupApplication();
	$bootstrap = $application->getBootstrap();
	$bootstrap->bootstrap('db');
	$dbAdapter = $bootstrap->getResource('db');
	
	createDb($dbAdapter);
	addAdmin(promptPassword("admin password"));
} catch (Exception $e) {
	echo 'AN ERROR HAS OCCURED:' . PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
	echo $e->getTraceAsString() . PHP_EOL;
	return false;
}

function prompt($output, $default = "", $quiet = false){
	echo $output . ($default == "" ? ": " : "[$default]: ");
	if ($quiet) system('stty -echo');
	$input = trim(fgets(STDIN));
	if ($quiet) system('stty echo');
	if ($quiet) echo PHP_EOL;
	return $input != "" ? $input : $default;
}

function promptPassword($output){
	$password = prompt($output, "", true);
	$confirm_password = prompt("confirm ".$output, "", true);
	if ($password == $confirm_password){
		return $password;
	} else {
		echo "Sorry, passwords do not match.".PHP_EOL;
		return promptPassword($output);
	}
}

function updateConfig($config){
	foreach ($config as $name=>$value){
		if (is_array($value)){
			echo "[$name]" . PHP_EOL;
			$config[$name] = updateConfig($value);
		} else {
			$config[$name] = prompt($name, $value);
		}
	}
	return $config;
}

function createDb($dbAdapter){
	$schemaSql = file_get_contents(dirname(__FILE__) . '/config/create.sql');
	// use the connection directly to load sql in batches
	$dbAdapter->getConnection()->exec($schemaSql);
	$dbAdapter->closeConnection();
	echo 'Database Created' . PHP_EOL;
}

function addAdmin($password){
	$userTable = new Base_Model_DbTable_User();
	$userData = array(
		'username'=>'admin',
		'password'=>$password
	);
	$roleTable = new Base_Model_DbTable_Role();
	$roleUserTable = new Base_Model_DbTable_RoleUser();
	
	$userId = $userTable->insert($userData);
	$roleId = $roleTable->addRole(new Base_Model_Role(array('name'=>'admin')));
	$roleUserTable->grantRole($userId,$roleId);
	$adminRole = new Base_Model_Role(array(
		"id"=>$roleId,
		"name"=>"admin"
	));
	guardAdminResources($adminRole);
	echo "Admin user, roles and permissions created." . PHP_EOL;
}

function guardAdminResources($adminRole){
	$resourceTable = new Base_Model_DbTable_Resource();
	$roleResourceTable = new Base_Model_DbTable_RoleResource();
	$resource = $resourceTable->getResource(array(
		'module'=>'default',
		'controller'=>'admin'
	), true);
	$roleResourceTable->requireRole($adminRole,$resource);
}

function write_file($content, $path) {
	if (!$handle = fopen($path, 'w')) return false;
	if (!fwrite($handle, $content)) return false;
	fclose($handle);
	return true;
}
function array_to_ini($arr, $hasSections = true){
	
	$ini = '';
	foreach ($arr as $key=>$value){
		if (is_array($value)){
			if ($hasSections){
				$ini .= "[$key]".PHP_EOL;
				$ini .= array_to_ini($value).PHP_EOL;
			} else {
				foreach ($value as $nestedKey=>$nestedValue){
					$ini .= "$key.$nestedKey = \"$nestedValue\"".PHP_EOL;
				}
			}
		} else {
			$ini .= "$key = \"$value\"".PHP_EOL;
		}
	}
	return $ini;
}

return true;
