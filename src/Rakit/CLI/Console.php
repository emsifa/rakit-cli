<?php namespace Rakit\CLI;

class Console {

	protected $fg_colors = array(
		'black' => '0;30',
		'dark_gray' => '1;30',
		'blue' => '0;34',
		'light_blue' => '1;34',
		'green' => '0;32',
		'light_green' => '1;32',
		'cyan' => '0;36',
		'light_cyan' => '1;36',
		'red' => '0;31',
		'light_red' => '1;31',
		'purple' => '0;35',
		'light_purple' => '1;35',
		'brown' => '0;33',
		'yellow' => '1;33',
		'light_gray' => '0;37',
		'white' => '1;37'
	);
	
	protected $bg_colors = array(
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'yellow' => '43',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'light_gray' => '47'
	);

	public function write($str, $fg_color = NULL, $bg_color = NULL)
	{	
		if($this->isWindows()) {
			printf($str);
		} else {
			$colored_string = "";
 
			// Check if given foreground color found
			if (isset($this->fg_colors[$fg_color])) {
				$colored_string .= "\033[" . $this->fg_colors[$fg_color] . "m";
			}
			
			// Check if given background color found
			if (isset($this->bg_colors[$bg_color])) {
				$colored_string .= "\033[" . $this->bg_colors[$bg_color] . "m";
			}
 
			// Add string and end coloring
			$colored_string .=  $str . "\033[0m";
 
			printf($colored_string);
		}
	}
	
	public function writeln($msg = "", $fg_color = NULL, $bg_color = NULL)
	{
		$this->write($msg, $fg_color, $bg_color);
		printf("\n");
	}
	
	public function ask($question, $default = NULL, $fg_color = null, $bg_color = null)
	{
		$this->write("\n".$question, $fg_color, $bg_color);
		$answer = $this->prompt();
		
		return empty($answer)? $default : $answer;
	}

	public function prompt()
	{
		return trim(fgets(STDIN));
	}

	public function error($msg)
	{
		$this->write($msg, "red");
		$this->write("\n");
		exit();
	}

	public function table(array $columns, array $values)
	{
		$col_spr = " | ";
		$col_start = "| ";
		$col_end = " |";

		$row_spr = "-";

		$table_width = strlen($col_start)+strlen($col_end)+(strlen($col_spr) * (count($columns)-1));
		$last_column = null;

		$default_col_settings = array(
			'label' => NULL,
			'width' => 16,
			'fg_color' => NULL,
			'bg_color' => NULL,
			'align' => 'left'
		);

		$row_header = array();

		foreach($columns as $colname => $col) {
			$col = array_merge($default_col_settings, $col);
			if(!$col['label']) $col['label'] = strtoupper($colname);
			$columns[$colname] = $col;
			$table_width += $col['width'];

			$last_column = $colname;

			$row_header[$colname] = $col['label'];
		}

		$csl = $this;

		$row_separator = function($table_width, $csl, $row_spr) {
			$csl->write(str_repeat($row_spr, $table_width)."\n");
		};

		$row = function(array $row_value, $columns, $csl, $col_start, $col_end, $col_spr, $last_column) {
			$csl->write($col_start);
			foreach($columns as $colname => $col) {
				switch ($col['align']) {
					case 'right': $align = STR_PAD_LEFT; break;
					case 'center': $align = STR_PAD_BOTH; break;
					default: $align = STR_PAD_RIGHT;
				}

				$value = trim($row_value[$colname]);
				if(strlen($value) > $col['width']) {
					$value = substr($value, 0, $col['width'] - 3).'...';
				}
				$text = str_pad($value, $col['width'], " ", $align);
				$csl->write($text, $col['fg_color'], $col['bg_color']);

				if($colname != $last_column) {
					$csl->write($col_spr);
				}	
			}
			$csl->write($col_end);
		};

		$row_separator($table_width, $csl, $row_spr);
		$row($row_header, $columns, $csl, $col_start, $col_end, $col_spr, $last_column);
		$this->write("\n");
		$row_separator($table_width, $csl, $row_spr);

		foreach($values as $row_value) {
			$row($row_value, $columns, $csl, $col_start, $col_end, $col_spr, $last_column);
			$this->write("\n");
		}

		$row_separator($table_width, $csl, $row_spr);
	}
	
	public function setArguments(array $args)
	{
		$this->arguments = $args;
	}
	
	public function argument($arg_name, $default = NULL)
	{
		if(is_null($this->arguments[$arg_name])) {
			return $default;
		} else {
			return $this->arguments[$arg_name];
		}
	}

	public function arguments()
	{
		return $this->arguments;
	}

	public function options()
	{
		return $this->options;
	}
	
	public function setOptions(array $options)
	{	
		$this->options = $options;
	}
	
	public function option($opt_name, $default = NULL)
	{
		if(is_null($this->options[$opt_name])) {
			return $default;
		} else {
			return $this->options[$opt_name];
		}
	}
	
	protected function isWindows()
	{
		return preg_match("/^WIN/i", PHP_OS);
	}
	
}