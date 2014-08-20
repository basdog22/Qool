{include file="meta.tpl"}
{*Include the header file*}
{include file="header.tpl"}
{assign var="mode" value=""|is_nophp_include}

{if $mode}
{include file="`$theInclude`.tpl"}
{else}
{""|load_the_include}
{/if}


{*Include the footer file*}
{include file="footer.tpl"}