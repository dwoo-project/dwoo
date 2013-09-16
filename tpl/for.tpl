{for i 0 5} {$i} {/for}

{for i 0 5 2} {$i} {/for}

{$arr=array("Bob","John","Jim")}
{for i $arr}
	{$i} -> {$arr.$i} {* or $arr[$i] *}
{/for}