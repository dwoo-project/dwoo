{$arr=array()}
{for i $arr}
	{$i} -> {$arr.$i} {* or $arr[$i] *}
{else}
	nothing to display
{/for}