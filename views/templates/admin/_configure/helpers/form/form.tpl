{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'fastcopy_productlist'}

        {include file='../../../../../../asanifastcopy.tpl'}

    {else}
        {$smarty.block.parent}
    {/if}
{/block}