{foreach $array val}
	$array is not empty so we display it's values : {$val}
	{else}
	if this shows, it means that $array is empty or doesn't exist.
{/foreach}