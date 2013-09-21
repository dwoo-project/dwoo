<?php
namespace Ref;

/**
 * Formatter abstraction
 */
abstract class RFormatter{

	/**
	 * Flush output and send contents to the output device
	 */
	abstract public function flush();

	/**
	 * Generate a base entity
	 *
	 * @param  string|array $type
	 * @param  string|null $text
	 * @param  string|array|null $meta
	 * @param  string|null $uri
	 */
	abstract public function text($type, $text = null, $meta = null, $uri = null);

	/**
	 * Generate container start token
	 *
	 * @param  string|array $type
	 * @param  string|bool $label
	 */
	public function startContain($type, $label = false){}

	/**
	 * Generate container ending token
	 */
	public function endContain(){}

	/**
	 * Generate empty group token
	 *
	 * @param  string $prefix
	 */
	public function emptyGroup($prefix = ''){}

	/**
	 * Generate group start token
	 *
	 * This method must return boolean TRUE on success, false otherwise (eg. max depth reached).
	 * The evaluator will skip this group on FALSE
	 *
	 * @param   string $prefix
	 * @return  bool
	 */
	public function startGroup($prefix = ''){}

	/**
	 * Generate group ending token
	 */
	public function endGroup(){}

	/**
	 * Generate section title
	 *
	 * @param  string $title
	 */
	public function sectionTitle($title){}

	/**
	 * Generate row start token
	 */
	public function startRow(){}

	/**
	 * Generate row ending token
	 */
	public function endRow(){}

	/**
	 * Column divider (cell delimiter)
	 *
	 * @param  int $padLen
	 */
	public function colDiv($padLen = null){}

	/**
	 * Generate modifier tokens
	 *
	 * @param  array $items
	 */
	public function bubbles(array $items){}

	/**
	 * Input expression start
	 */
	public function startExp(){}

	/**
	 * Input expression end
	 */
	public function endExp(){}

	/**
	 * Root starting token
	 */
	public function startRoot(){}

	/**
	 * Root ending token
	 */
	public function endRoot(){}

	/**
	 * Separator token
	 *
	 * @param  string $label
	 */
	public function sep($label = ' '){}

	/**
	 * Resolve cache request
	 *
	 * If the ID is not present in the cache, then a new cache entry is created
	 * for the given ID, and string offsets are captured until cacheLock is called
	 *
	 * This method must return TRUE if the ID exists in the cache, and append the cached item
	 * to the output, FALSE otherwise.
	 *
	 * @param   string $id
	 * @return  bool
	 */
	public function didCache($id){
		return false;
	}

	/**
	 * Ends cache capturing for the given ID
	 *
	 * @param  string $id
	 */
	public function cacheLock($id){}

}