{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'hooks'}
        <div class="btn-group" role="group" aria-label="hooks">
            {assign var=pos value=1}
            {foreach $input.options as $hook => $desc key=i}
                <button type="button" class="btn btn-default btn-hook{if in_array($hook, $fields_value[$input.name])} active{/if}" data-target="{$input.name|escape:'htmlall':'UTF-8'}_{$pos|intval}">
                    {$desc|escape:'html':'UTF-8'}
                </button>
                {assign var=pos value=$pos+1}
            {/foreach}
        </div>
        <div style="visibility:hidden;height:0px">
        {assign var=pos value=1}
        {foreach $input.options as $hook => $desc key=i}
            <input type="checkbox" 
                name="{$input.name|escape:'htmlall':'UTF-8'}[]" 
                id="{$input.name|escape:'htmlall':'UTF-8'}_{$pos|intval}" 
                value="{$hook|escape:'html':'UTF-8'}" {if in_array($hook, $fields_value[$input.name])}checked="checked"{/if}
            >
            {assign var=pos value=$pos+1}
        {/foreach}
        </div>
        <script>
        window.addEventListener('load',function() {
            $(document).on('click', '.btn-hook', function() {
                var id = $(this).data('target');
                console.log(id);
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    $(this).addClass('btn-default');
                    $('#'+id).removeAttr('checked');
                } else {
                    $('#'+id).attr('checked', 'checked');
                    $(this).removeClass('btn-default');
                    $(this).addClass('active');
                }
            });
        });
        </script>
    {elseif $input.type == 'identifier'}
        <div class="alert alert-{if empty($fields_value[$input.name])}success{else}warning{/if}">
            {if empty($fields_value[$input.name])}
                {l s='You are creating a new text block.' mod='wi_welcome'}
            {else}
                {l s='Editing' mod='wi_welcome'} : #{$fields_value[$input.name]|intval}
            {/if}
        </div>
        <input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}" value="{$fields_value[$input.name]|intval}">
    {else}
        {$smarty.block.parent}{* HTML CONTENT *}
    {/if}
{/block}
