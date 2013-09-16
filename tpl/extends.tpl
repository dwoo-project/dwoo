<!-- FunctionExtends -->
{extends "base.tpl"}

<!-- BlockBlock -->
{block "content"}
	{$dwoo.parent}
	<br>
	I'm {$foo}, this is {$bar}!
{/block}