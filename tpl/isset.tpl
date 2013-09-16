{if isset($foo)}SET{else}not set or null{/if}
{$foo=1}
{if isset($foo)}SET{else}not set or null{/if}
{$bar=null}
{if isset($bar)}SET{else}not set or null{/if}