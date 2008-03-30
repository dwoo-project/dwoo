<?php

/**
 * TOCOM
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooProcessor_smarty_compat extends DwooProcessor
{
	public function process($input)
	{
		list($l, $r) = $this->compiler->getDelimiters();

		$rl = preg_quote($l);
		$rr = preg_quote($r);
		$sectionParam = '(?:(name|loop|start|step|max|show)\s*=\s*(\S+))?\s*';
		$input = preg_replace_callback('{'.$rl.'section '.str_repeat($sectionParam, 6).$rr.'(.+?)(?:'.$rl.'sectionelse'.$rr.'(.+?))?'.$rl.'/section'.$rr.'}is', array($this, 'convertSection'), $input);
		$input = str_replace('$smarty.section.', '$smarty.for.', $input);

		$smarty = array
		(
			$l.'ldelim'.$r,
			$l.'rdelim'.$r,
			$l.'$smarty.ldelim'.$r,
			$l.'$smarty.rdelim'.$r,
			'$smarty.',
			'{php}',
			'{/php}',
		);

		$dwoo = array
		(
			'\\'.$l,
			$r,
			'\\'.$l,
			$r,
			'$dwoo.',
			'<?php ',
			' ?>',
		);
	    return str_replace($smarty, $dwoo, $input);
	}

	protected function convertSection(array $matches)
	{
		$params = array();
		$index = 1;
		while(!empty($matches[$index]) && $index < 13)
		{
			$params[$matches[$index]] = $matches[$index+1];
			$index += 2;
		}
		$params['content'] = $matches[13];
		if(isset($matches[14]) && !empty($matches[14]))
			$params['altcontent'] = $matches[14];

		if(empty($params['name']))
			$this->compiler->triggerError('Missing parameter <em>name</em> for section tag');
		$name = $params['name'];

		if(isset($params['loop']))
			$loops = $params['loop'];

		if(isset($params['max']))
			$max = $params['max'];

		if(isset($params['start']))
			$start = $params['start'];

		if(isset($params['step']))
			$step = $params['step'];

		if (!isset($loops))
            $loops = null;

        if (!isset($max) || $max < 0)
        {
        	if(is_numeric($loops))
            	$max = $loops;
            else
            	$max = 'null';
        }

        if (!isset($step))
            $step = 1;
        if (!isset($start))
            $start = $loops - 1;
        elseif(!is_numeric($loops))
           	$start = 0;

		list($l, $r) = $this->compiler->getDelimiters();

		if(is_numeric($loops))
		{
			if(isset($params['start']) && isset($params['loop']) && !isset($params['max']))
				$output = $l.'for '.$name.' '.$start.' '.($loops-$step).' '.$step.$r;
			else
				$output = $l.'for '.$name.' '.$start.' '.($start+floor($step*$max+($step>0?-1:1))).' '.$step.$r;
		}
		else
			$output = $l.'for '.$name.' '.$loops.' '.($start+floor($max/$step)).' '.$step.' '.$start.$r;

		$output .= str_replace('['.trim($name, '"\'').']', '[$'.trim($name, '"\'').']', $params['content']);

		if(isset($params['altcontent']))
			$output .= $l.'forelse'.$r.$params['altcontent'];

		$output .= $l.'/for'.$r;

		return $output;
	}
}

?>