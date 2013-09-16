{foreach $arr val implode=", "}
	{dump($val)}
	{$val.id} - {$val.name}
{/foreach}