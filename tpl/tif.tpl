{$foo = "foo"}
{tif $foo == "bar" ? "true" : "false"} {* full syntax *}
{tif $foo ?: "false"} {* you can omit the true value, in which case the expression will be re-used as the true value *}
{tif $foo ? "true"} {* you can omit the false value, in which case nothing will be output if it's false *}
{tif $foo} {* you can omit both values, in that case $foo is printed if it evaluates to true, otherwise nothing is printed *}

{$foo = null}
{tif $foo == "bar" ? "true" : "false"}
{tif $foo ?: "false"}
{tif $foo ? "true"}
{tif $foo}