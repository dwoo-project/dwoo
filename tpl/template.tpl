{function menu data tick="-" indent=""}
{foreach $data entry}
	{$indent}{$tick} {$entry.name}<br />

	{if $entry.children}
	{* recursive calls are allowed which makes subtemplates especially good to output trees *}
		{menu $entry.children $tick cat("&nbsp;&nbsp;", $indent)}
	{/if}
{/foreach}
{/function}

{menu $menuTree ">"}