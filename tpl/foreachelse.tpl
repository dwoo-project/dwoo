{$arr=array()}
{foreach item=con from=$arr}
	<li>{$con}</li>
{foreachelse}
	No items were found in the search
{/foreach}