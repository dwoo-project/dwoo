<?php
namespace Ref;

/**
 * Generates the output in plain text format
 *
 */
class RTextFormatter extends RFormatter {

	protected

		/**
		 * Actual output
		 *
		 * @var  string
		 */
		$out        = '',

		/**
		 * Tracks current nesting level
		 *
		 * @var  int
		 */
		$level      = 0,

		/**
		 * Current indenting level
		 *
		 * @var  int
		 */
		$indent     = 0,

		$lastIdx    = 0,
		$lastLineSt = 0,
		$levelPad   = array(0);



	public function flush(){
		print $this->out;
		$this->out   = '';
		$this->cache = array();
	}

	public function sep($label = ' '){
		$this->out .= $label;
	}

	public function text($type, $text = null, $meta = null, $uri = null){

		if(!is_array($type))
			$type = (array)$type;

		if($text === null)
			$text = $type[0];

		if(in_array('special', $type, true)){
			$text = strtr($text, array(
									  "\r" => '\r',     // carriage return
									  "\t" => '\t',     // horizontal tab
									  "\n" => '\n',     // linefeed (new line)
									  "\v" => '\v',     // vertical tab
									  "\e" => '\e',     // escape
									  "\f" => '\f',     // form feed
									  "\0" => '\0',
								 ));

			$this->out .= $text;
			return;
		}

		$formatMap = array(
			'string'   => '%3$s "%2$s"',
			'integer'  => 'int(%2$s)',
			'double'   => 'double(%2$s)',
			'true'     => 'bool(%2$s)',
			'false'    => 'bool(%2$s)',
			'key'      => '[%2$s]',
		);

		if(!is_string($meta))
			$meta = '';

		$this->out .= isset($formatMap[$type[0]]) ? sprintf($formatMap[$type[0]], $type[0], $text, $meta) : $text;
	}

	public function startContain($type, $label = false){

		if(!is_array($type))
			$type = (array)$type;

		if($label)
			$this->out .= "\n" . str_repeat(' ', $this->indent + $this->levelPad[$this->level]) . "┗ {$type[0]} ~ ";
	}

	public function emptyGroup($prefix = ''){
		$this->out .= "({$prefix})";
	}

	public function startGroup($prefix = ''){

		$maxDepth = ref::config('maxDepth');

		if(($maxDepth > 0) && (($this->level + 1) > $maxDepth)){
			$this->emptyGroup('...');
			return false;
		}

		$this->level++;
		$this->out .= '(';

		$this->indent += $this->levelPad[$this->level - 1];
		return true;
	}

	public function endGroup(){
		$this->out .= "\n" . str_repeat(' ', $this->indent) . ')';
		$this->indent -= $this->levelPad[$this->level - 1];
		$this->level--;
	}

	public function sectionTitle($title){
		$pad = str_repeat(' ', $this->indent + 2);
		$this->out .= sprintf("\n\n%s%s\n%s%s", $pad, $title, $pad, str_repeat('-', strlen($title)));
	}

	public function startRow(){
		$this->out .= "\n  " . str_repeat(' ', $this->indent);
		$this->lastLineSt = strlen($this->out);
	}

	public function endRow(){
	}

	public function colDiv($padLen = null){
		$padLen = ($padLen !== null) ? $padLen + 1 : 1;
		$this->out .= str_repeat(' ', $padLen);

		$this->lastIdx = strlen($this->out);
		$this->levelPad[$this->level] = $this->lastIdx - $this->lastLineSt + 2;
	}

	public function bubbles(array $items){

		if(!$items){
			$this->out .= '  ';
			return;
		}

		$this->out .= '<';

		foreach($items as $item)
			$this->out .= $item[0];

		$this->out .= '>';
	}

	public function endExp(){
		$this->out .= "\n" . str_repeat('=', strlen($this->out)) . "\n";
	}

	public function startRoot(){
		$this->out .= "\n\n";
	}

	public function endRoot(){
		$this->out .= "\n";
	}

}