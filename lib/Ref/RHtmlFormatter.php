<?php
namespace Ref;

/**
 * Generates the output in HTML5 format
 *
 */
class RHtmlFormatter extends RFormatter {

	public

		/**
		 * Actual output
		 *
		 * @var  string
		 */
		$out = '',

		/**
		 * Tracks current nesting level
		 *
		 * @var  int
		 */
		$level = 0,

		/**
		 * Stores tooltip content for all entries
		 *
		 * To avoid having duplicate tooltip data in the HTML, we generate them once,
		 * and use References (the Q index) to pull data when required;
		 * this improves performance significantly
		 *
		 * @var  array
		 */
		$tips = array(),

		/**
		 * Used to cache output to speed up processing.
		 *
		 * Contains hashes as keys and string offsets as values.
		 * Cached objects will not be processed again in the same query
		 *
		 * @var  array
		 */
		$cache = array();

	protected static

		/**
		 * Instance counter
		 *
		 * @var  int
		 */
		$counter = 0,

		/**
		 * Tracks style/jscript inclusion state
		 *
		 * @var  bool
		 */
		$didAssets = false;

	public function flush() {
		print $this->out;
		$this->out   = '';
		$this->cache = array();
		$this->tips  = array();
	}

	public function didCache($id) {

		if (!isset($this->cache[$id])) {
			$this->cache[$id]   = array();
			$this->cache[$id][] = strlen($this->out);

			return false;
		}

		if (!isset($this->cache[$id][1])) {
			$this->cache[$id][0] = strlen($this->out);

			return false;
		}

		$this->out .= substr($this->out, $this->cache[$id][0], $this->cache[$id][1]);

		return true;
	}

	public function cacheLock($id) {
		$this->cache[$id][] = strlen($this->out) - $this->cache[$id][0];
	}

	public function sep($label = ' ') {
		$this->out .= $label !== ' ' ? '<i>' . static::escape($label) . '</i>' : $label;
	}

	public function text($type, $text = null, $meta = null, $uri = null) {

		if (!is_array($type)) {
			$type = (array)$type;
		}

		$tip  = '';
		$text = ($text !== null) ? static::escape($text) : static::escape($type[0]);

		if (in_array('special', $type)) {
			$text = strtr($text, array("\r" => '<i>\r</i>', // carriage return
									   "\t" => '<i>\t</i>', // horizontal tab
									   "\n" => '<i>\n</i>', // linefeed (new line)
									   "\v" => '<i>\v</i>', // vertical tab
									   "\e" => '<i>\e</i>', // escape
									   "\f" => '<i>\f</i>', // form feed
									   "\0" => '<i>\0</i>',
								 ));
		}

		// generate tooltip Reference (probably the slowest part of the code ;)
		if ($meta !== null) {
			$tipIdx = array_search($meta, $this->tips, true);

			if ($tipIdx === false)
				$tipIdx = array_push($this->tips, $meta) - 1;

			$tip = ' data-tip="' . $tipIdx . '"';
		}

		// wrap text in a link?
		if ($uri !== null)
			$text = '<a hRef="' . $uri . '" target="_blank">' . $text . '</a>';

		//$this->out .= ($type !== 'name') ? "<b data-{$type}{$tip}>{$text}</b>" : "<b{$tip}>{$text}</b>";

		$typeStr = '';
		foreach ($type as $part)
			$typeStr .= " data-{$part}";

		$this->out .= "<b{$typeStr}{$tip}>{$text}</b>";
	}

	public function startContain($type, $label = false) {

		if (!is_array($type))
			$type = (array)$type;

		if ($label)
			$this->out .= '<br>';

		$typeStr = '';
		foreach ($type as $part)
			$typeStr .= " data-{$part}";

		$this->out .= "<b{$typeStr}>";

		if ($label)
			$this->out .= "<b data-match>{$type[0]}</b>";
	}

	public function endContain() {
		$this->out .= '</b>';
	}

	public function emptyGroup($pRefix = '') {

		if ($pRefix !== '')
			$pRefix = '<b data-gLabel>' . static::escape($pRefix) . '</b>';

		$this->out .= "<i>(</i>{$pRefix}<i>)</i>";
	}

	public function startGroup($pRefix = '') {

		$maxDepth = Ref::config('maxDepth');

		if (($maxDepth > 0) && (($this->level + 1) > $maxDepth)) {
			$this->emptyGroup('...');

			return false;
		}

		$this->level++;

		$expLvl = Ref::config('expLvl');
		$exp    = ($expLvl < 0) || (($expLvl > 0) && ($this->level <= $expLvl)) ? ' data-exp' : '';

		if ($pRefix !== '')
			$pRefix = '<b data-gLabel>' . static::escape($pRefix) . '</b>';

		$this->out .= "<i>(</i>{$pRefix}<b data-toggle{$exp}></b><b data-group><b data-table>";

		return true;
	}

	public function endGroup() {
		$this->out .= '</b></b><i>)</i>';
		$this->level--;
	}

	public function sectionTitle($title) {
		$this->out .= "</b><b data-tHead>{$title}</b><b data-table>";
	}

	public function startRow() {
		$this->out .= '<b data-row><b data-cell>';
	}

	public function endRow() {
		$this->out .= '</b></b>';
	}

	public function colDiv($padLen = null) {
		$this->out .= '</b><b data-cell>';
	}

	public function bubbles(array $items) {

		if (!$items)
			return;

		$this->out .= '<b data-mod>';

		foreach ($items as $info)
			$this->out .= $this->text('mod-' . strtolower($info[1]), $info[0], $info[1]);

		$this->out .= '</b>';
	}

	public function startExp() {
		$this->out .= '<b data-input>';
	}

	public function endExp() {
		$this->out .= '</b><b data-output>';
	}

	public function startRoot() {
		$this->out .= '<!-- Ref#' . static::$counter++ . ' --><div>' . static::getAssets() . '<div class="Ref">';
	}

	public function endRoot() {
		$this->out .= '</b>';

		// process tooltips
		$tipHtml = '';
		foreach ($this->tips as $idx => $meta) {

			$tip = '';
			if (!is_array($meta))
				$meta = array('title' => $meta);

			$meta += array('title' => '', 'left' => '', 'description' => '', 'tags' => array(), 'sub' => array(),
			);

			$meta = static::escape($meta);
			$cols = array();

			if ($meta['left'])
				$cols[] = "<b data-cell data-varType>{$meta['left']}</b>";

			$title = $meta['title'] ? "<b data-title>{$meta['title']}</b>" : '';
			$desc  = $meta['description'] ? "<b data-desc>{$meta['description']}</b>" : '';
			$tags  = '';

			foreach ($meta['tags'] as $tag => $values) {
				foreach ($values as $value) {
					if ($tag === 'param') {
						$value[0] = "{$value[0]} {$value[1]}";
						unset($value[1]);
					}

					$value = is_array($value) ? implode('</b><b data-cell>', $value) : $value;
					$tags .= "<b data-row><b data-cell>@{$tag}</b><b data-cell>{$value}</b></b>";
				}
			}

			if ($tags)
				$tags = "<b data-table>{$tags}</b>";

			if ($title || $desc || $tags)
				$cols[] = "<b data-cell>{$title}{$desc}{$tags}</b>";

			if ($cols)
				$tip = '<b data-row>' . implode('', $cols) . '</b>';

			$sub = '';
			foreach ($meta['sub'] as $line)
				$sub .= '<b data-row><b data-cell>' . implode('</b><b data-cell>', $line) . '</b></b>';

			if ($sub)
				$tip .= "<b data-row><b data-cell data-sub><b data-table>{$sub}</b></b></b>";

			if ($tip)
				$this->out .= "<div>{$tip}</div>";
		}

		$this->out .= '</div></div><!-- /Ref#' . static::$counter . ' -->';
	}

	/**
	 * Get styles and javascript (only generated for the 1st call)
	 *
	 * @return  string
	 */
	public static function getAssets() {

		// first call? include styles and javascript
		if (!static::$didAssets) {
			$output = '';

			if (Ref::config('stylePath') !== false) {
				$output .= '<style>' . file_get_contents(str_replace('{:dir}', __DIR__, Ref::config('stylePath'))) . '</style>';
			}

			if (Ref::config('scriptPath') !== false) {
				$output .= '<script>' . file_get_contents(str_replace('{:dir}', __DIR__, Ref::config('scriptPath'))) . '</script>';
			}

			// normalize space and remove comments
			//$output = preg_replace('/\s+/', ' ', trim(ob_get_clean()));
			//$output = preg_replace('!/\*.*?\*/!s', '', $output);
			//$output = preg_replace('/\n\s*\n/', "\n", $output);

			static::$didAssets = true;

			return $output;
		}
	}

	/**
	 * Escapes variable for HTML output
	 *
	 * @param   string|array $var
	 *
	 * @return  string|array
	 */
	protected static function escape($var) {
		return is_array($var) ? array_map('static::escape', $var) : htmlspecialchars($var, ENT_QUOTES);
	}

}
