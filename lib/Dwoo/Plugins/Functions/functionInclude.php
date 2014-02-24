<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Exception;
use Dwoo\Plugin;
use Dwoo\Security\Exception as SecurityException;

/**
 * Inserts another template into the current one
 * <pre>
 *  * file : the resource name of the template
 *  * cache_time : cache length in seconds
 *  * cache_id : cache identifier for the included template
 *  * compile_id : compilation identifier for the included template
 *  * data : data to feed into the included template, it can be any array and will default to $_root (the current data)
 *  * assign : if set, the output of the included template will be saved in this variable instead of being output
 *  * rest : any additional parameter/value provided will be added to the data array
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2013-2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionInclude extends Plugin {

	public function process($file, $cache_time = null, $cache_id = null, $compile_id = null, $data = '_root', $assign = null, array $rest = array()) {
		if ($file === '') {
			return null;
		}

		if (preg_match('#^([a-z]{2,}):(.*)$#i', $file, $m)) {
			// resource:identifier given, extract them
			$resource   = $m[1];
			$identifier = $m[2];
		}
		else {
			// get the current template's resource
			$resource   = $this->core->getTemplate()->getResourceName();
			$identifier = $file;
		}

		$include = null;
		try {
			$include = $this->core->templateFactory($resource, $identifier, $cache_time, $cache_id, $compile_id);
		}
		catch (SecurityException $e) {
			$this->core->triggerError('Include : Security restriction : ' . $e->getMessage(), E_USER_WARNING);
		}
		catch (Exception $e) {
			$this->core->triggerError('Include : ' . $e->getMessage(), E_USER_WARNING);
		}

		if ($include === null) {
			$this->core->triggerError('Include : Resource "' . $resource . ':' . $identifier . '" not found.', E_USER_WARNING);
		}
		else if ($include === false) {
			$this->core->triggerError('Include : Resource "' . $resource . '" does not support includes.', E_USER_WARNING);
		}

		if (is_string($data)) {
			$vars = $this->core->readVar($data);
		}
		else {
			$vars = $data;
		}

		if (count($rest)) {
			$vars = $rest + $vars;
		}

		$clone = clone $this->core;
		$out   = $clone->get($include, $vars);

		if ($assign !== null) {
			$this->core->assignInScope($out, $assign);
		}

		foreach ($clone->getReturnValues() as $name => $value) {
			$this->core->assignInScope($value, $name);
		}

		if ($assign === null) {
			return $out;
		}
		return null;
	}
}
