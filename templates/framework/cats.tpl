{extends file="framework/bootstrap.tpl"}

{block name="content"}
<h2>{$item.title}</h2>
<p>{$item.body|markdown}</p>
<p>
<img src="{$item.links.file}">
</p>
{/block}
