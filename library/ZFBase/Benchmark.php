<?php
class ZFBase_Benchmark {
	// Hold an instance of the class
	private static $instance;
	
	private $timers = array();

	// A private constructor; prevents direct creation of object
	private function __construct()
	{		
	}

	/**
	 * @return ZFBase_Benchmark
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function startTimer($name){
		$this->timers[$name] = array('start'=>microtime(true));
	}
	
	public function stopTimer($name){
		$this->timers[$name]['end'] = microtime(true);
	}
	
	public static function hasTimers(){
		return (sizeof(self::getInstance()->timers) > 0) ? true : false;
	}
	
	public function __toString(){
		return implode('\n',array_map(array("self","getTimerString"),array_keys($this->timers)));
	}
	
	public function getTimerString($name){
		return "$name: " . (self::getInstance()->timers[$name]['end'] - $this->timers[$name]['start']);
	}
	
	// Prevent users to clone the instance
	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
}