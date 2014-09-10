<?php namespace Rakit\CLI;

class App {
	
	protected $argv = array();
	
	protected $commands = array();

	protected $default_command;
	
	public function __construct(array $argv)
	{
		$this->argv = $argv;
		$this->command("lists", array($this, "commandShowLists"), "show list commands");
		$this->setDefaultCommand("lists");
	}
	
	public function command($cmd_name, $action, $description = "", array $args = array(), array $options = array())
	{		
		$this->commands[$cmd_name] = new Command($cmd_name, $action, $description, $args, $options);
		return $this->commands[$cmd_name];
	}

	public function setDefaultCommand($command)
	{
		$this->default_command = $command;
	}
	
	public function getRegisteredCommands()
	{
		return $this->commands;
	}
	
	public function run()
	{	
		$argv = $this->argv;
		$filename = array_shift($argv);
		$cmd_name = trim(array_shift($argv));
		
		if(empty($cmd_name)) {
			$cmd_name = $this->default_command;
		}

		if(array_key_exists($cmd_name, $this->commands)) {
			$this->commands[$cmd_name]->run($argv);
		} else {
			printf("Command $cmd_name not found");
		}
	}

	public function commandShowLists(Console $console)
	{
		$console->writeln("LIST COMMANDS");

		$commands = array();
		foreach($this->commands as $command) {
			$commands[] = $command->getData();
		}

		$console->table(array(
			'name' => array(
				'label' => 'COMMAND',
				'width' => 15,
				'fg_color' => 'blue',
				'align' => 'right'
			),
			'description' => array(
				'width' => 40
			),
			'action' => array(
				'width' => 25
			)
		), $commands);
	}
	
}

?>