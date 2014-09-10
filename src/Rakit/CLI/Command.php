<?php namespace Rakit\CLI;

class Command {
	
	protected $name;
	protected $description = "";
	protected $action;
	protected $arguments = array();
	protected $options = array();
	protected $console;

	const OPT_STRING = 'string';
	const OPT_NUMBER = 'number';
	const OPT_BOOLEAN = 'boolean';
	const OPT_ARRAY = 'array';
	
	public function __construct($name, $action, $description, array $args = array(), array $options = array())
	{
		$this->name = $name;
		$this->action = $action;
		$this->description = $description;
		
		foreach($args as $arg) {
			$this->argument($arg[0], $arg[1]);
		}
		
		foreach($options as $opt) {
			$this->option($opt[0], $opt[1], $opt[2]);
		}
	}

	public function getName()
	{
		return $this->name;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getAction()
	{
		return $this->action;
	}

	public function run(array $argv)
	{	
		$this->console = new Console();
		list($args, $options) = $this->parseArgv($argv);

		$this->console->setArguments($args);
		$this->console->setOptions($options);		

		$action = $this->action;

		if($action instanceof \Closure) {
			return $action($this->console);
		} elseif (is_callable($action)) {
			return call_user_func($action, $this->console);
		} elseif(is_string($action)) {
			list($class, $method) = explode("@", $action);
			if(class_exists($class)) {
				$cmd = new $class();
				if(method_exists($method, $cmd)) {
					$cmd->$method($this->console);
				} else {
					$this->console->error("Undefined method '$method' in class $class");
				}				
			} else {
				$this->console->error("Undefined method '$method' in class $class");
			}
		}
	}
	
	public function validateOptions(array $options)
	{

	}
	
	public function addArgument($arg_name, $required = false)
	{
		$this->arguments[] = array(
			'name' => $arg_name,
			'required' => $required
		);
		return $this;
	}
	
	public function addOption($opt_name, $opt_type = null, $required = false)
	{
		if(is_null($opt_type)) {
			$opt_type = static::OPT_STRING;
		}
		$this->options[$opt_name] = array(
			'type' => $opt_type, 
			'required' => $required
		);
		return $this;
	}
	
	protected function parseArgv(array $argv)
	{
		$inputed_args = array();
		$inputed_options = array();
		$args = array();
		$options = array();
		
		//extract argv into inputed_args and inputed_options
		foreach($argv as $arg) {
			if($this->isOption($arg)) {
				list($opt_name, $opt_val) = $this->parseOption($arg);
				$inputed_options[$opt_name] = $opt_val;
			} else {
				$inputed_args[] = $arg;
			}
		}
		
		if(count($diff = (array_diff_key($inputed_options, $this->options))) > 0) {
			$this->console->write("#ERROR : ");
			$this->console->error("Undefined option '".key($diff)."'");
		}
		
		//set args value
		foreach($this->arguments as $i => $arg_setting) {
			if($arg_setting['required'] AND !array_key_exists($i, $inputed_args)) {
				$this->console->write("#ERROR : ");
				$this->console->error("Argument '".$arg_setting['name']."' is required");
			} else {
				$args[$arg_setting['name']] = $inputed_args[$i];
			}
		}
		
		foreach($this->options as $opt_name => $opt_setting) {			
			if(!array_key_exists($opt_name, $inputed_options) AND $opt_setting['required']) {
				$this->console->write("#ERROR : ");
				$this->console->error("Option '".$opt_name."' is required");
			} elseif($opt_setting['type'] != 'none' AND $opt_setting['required'] AND empty($inputed_options[$opt_name])) {
				$this->console->write("#ERROR : ");
				$this->console->error("Option '".$opt_name."' is required, cannot be NULL");
			} else {
				if(!array_key_exists($opt_name, $inputed_options)) {
					$option_value = NULL;
				} else {
					$option_value = trim($inputed_options[$opt_name]);
				}
				
				$options[$opt_name] = $option_value;

				switch($opt_setting['type']) {
					case static::OPT_ARRAY:
						if(empty($option_value)) {
							$options[$opt_name] = array();
						} else {
							$options[$opt_name] = explode("|", $option_value);
						}
						
						break;
					case static::OPT_BOOLEAN:
						$true = array('true', '1', 'yes', 'on');
						$false = array('false', '0', 'no', 'off', null);

						if(in_array($option_value, $true) OR in_array($option_value, $false)) {
							$options[$opt_name] = in_array($option_value, $true);
						} else {
							$this->console->write("#ERROR : ");
							$this->console->error("Option '".$opt_name."' must be boolean(true|false, 0|1, on|off, yes|no)");
						}

						break;						
					case static::OPT_NUMBER:
						if(empty($option_value)) {
							$options[$opt_name] = 0;
						} elseif(!is_numeric($inputed_options[$opt_name])) {
							$this->console->write("#ERROR : ");
							$this->console->error("Option '".$opt_name."' must be numeric");
						} else {
							$options[$opt_name] = intval($inputed_options[$opt_name]);
						}
						break;
				}
			}			
		}
		
		return array($args, $options);
		
	}
	
	protected function isOption($arg)
	{
		return preg_match('/^\--(?<optname>\w+)(\=(?<optval>.*))?/i', $arg);	
	}
	
	protected function parseOption($arg)
	{
		preg_match('/^\--(?<optname>\w+)(\=(?<optval>.*))?/i', $arg, $match);
		if(empty($match['optval'])) $match['optval'] = NULL;
		
		return array($match['optname'], $match['optval']);
	}

	public function getData()
	{
		$action = $this->getAction();

		if(is_array($action)) {
			$action_string = get_class($action[0]).'->'.$action[1].'()';
		} elseif($action instanceof \Closure) {
			$action_string = "Closure()";
		}

		return array(
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'action' => $action_string
		);	
	}
	
}

?>