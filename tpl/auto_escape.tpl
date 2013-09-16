{$user="<a href=\"javascript:jsAttack()\">EvilTroll</a>"}
{$user} {* => no escaping, if you didn't filter your data in your php code, this is potentially harmful user input *}

{auto_escape on}
{$user} {* here any injected html is escaped so it's safe *}
{/auto_escape}