<?php

/**
 * Loads sub-templates contained in an external file
 * <pre>
 *  * file : the resource name of the file to load
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 *
 * @link       http://dwoo.org/
 *
 * @version    1.2.3
 * @date       2016-10-15
 */
function Dwoo_Plugin_load_templates_compile(Dwoo_Compiler $compiler, $file)
{
    $file = substr($file, 1, -1);

    if ($file === '') {
        return '';
    }

    if (preg_match('#^([a-z]{2,}):(.*)$#i', $file, $m)) {
        // resource:identifier given, extract them
        $resource = $m[1];
        $identifier = $m[2];
    } else {
        // get the current template's resource
        $resource = $compiler->getDwoo()->getTemplate()->getResourceName();
        $identifier = $file;
    }

    $tpl = $compiler->getDwoo()->templateFactory($resource, $identifier);

    if ($tpl === null) {
        throw new Dwoo_Compilation_Exception($compiler, 'Load Templates : Resource "'.$resource.':'.$identifier.'" not found.');
    } elseif ($tpl === false) {
        throw new Dwoo_Compilation_Exception($compiler, 'Load Templates : Resource "'.$resource.'" does not support includes.');
    }

    $cmp = clone $compiler;
    $cmp->compile($compiler->getDwoo(), $tpl);
    $usedTemplates = array($tpl);
    foreach ($cmp->getTemplatePlugins() as $template=>$args) {
        if (isset($args['sourceTpl'])) {
            $sourceTpl = $args['sourceTpl'];
        } else {
            $sourceTpl = $tpl;
        }

        $compiler->addTemplatePlugin($template, $args['params'], $args['uuid'], $args['body'], $sourceTpl);

        if (!in_array($sourceTpl, $usedTemplates, true)) {
            $usedTemplates[] = $sourceTpl;
        }
    }
    foreach ($cmp->getUsedPlugins() as $plugin=>$type) {
        $compiler->addUsedPlugin($plugin, $type);
    }

    $out = '\'\';// checking for modification in '.$resource.':'.$identifier."\r\n";

    foreach ($usedTemplates AS $usedTemplate) {
        $modCheck = $usedTemplate->getIsModifiedCode();

        if ($modCheck) {
            $out .= 'if (!('.$modCheck.')) { ob_end_clean(); return false; }';
        } else {
            $usedTemplateResourceName = $usedTemplate->getResourceName();
            $usedTemplateResourceIdentifier = $usedTemplate->getResourceIdentifier();
            $out .= '
try {
	$tpl = $this->templateFactory("'.$usedTemplateResourceName.'", "'.$usedTemplateResourceIdentifier.'");
} catch (Dwoo_Exception $e) {
	$this->triggerError(\'Load Templates : Resource <em>'.$usedTemplateResourceName.'</em> was not added to Dwoo, can not extend <em>'.$usedTemplateResourceIdentifier.'</em>\', E_USER_WARNING);
}
if ($tpl === null)
	$this->triggerError(\'Load Templates : Resource "'.$usedTemplateResourceName.':'.$usedTemplateResourceIdentifier.'" was not found.\', E_USER_WARNING);
elseif ($tpl === false)
	$this->triggerError(\'Load Templates : Resource "'.$usedTemplateResourceName.'" does not support extends.\', E_USER_WARNING);
if ($tpl->getUid() != "'.$usedTemplate->getUid().'") { ob_end_clean(); return false; }';
        }
    }

    return $out;
}
