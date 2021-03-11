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
<div class="panel">
    <div class="row">
        <div class="col-md-12">
            {if empty($wi_spent.coupons)}
                <div class="alert alert-info">{l s='There is no coupon yet' mod='wi_spent'}</div>
            {else}
            <div style="padding-top:25px">
                <table class="table table-bordered">
                    <thead class="with-filters">
                    <tr class="column-headers">
                        <th>
                            <a href="{$wi_spent.link|escape:'htmlall':'UTF-8'}&sort=id_custome&mode={if $wi_spent.mode == 'desc'}asc{else}desc{/if}" class="pagination-header{if $wi_spent.sort == "id_customer"} active{/if}">{l s='ID Customer' mod='wi_spent'}</a>
                        </th>                    
                        <th>
                            <a href="{$wi_spent.link|escape:'htmlall':'UTF-8'}&sort=name&mode={if $wi_spent.mode == 'desc'}asc{else}desc{/if}" class="pagination-header{if $wi_spent.sort == "name"} active{/if}">{l s='Name' mod='wi_spent'}</a>
                        </th>
                        <th>
                            <a class="pagination-header">{l s='Email' mod='wi_spent'}</a>
                        </th>
                        <th>
                            <a class="pagination-header">{l s='Coupon' mod='wi_spent'}</a>
                        </th>
                        <th>
                            <a href="{$wi_spent.link|escape:'htmlall':'UTF-8'}&sort=date_add&mode={if $wi_spent.mode == 'desc'}asc{else}desc{/if}" class="pagination-header{if $wi_spent.sort == "date_add"} active{/if}">{l s='Date' mod='wi_spent'}</a>
                        </th>
                        <th></th>
                    </tr>
                    <form method="post">
                        <tr>
                            <th><input type="text" class="form-control" name="filters[id_customer]"></th>
                            <th><input type="text" class="form-control" name="filters[name]"></th>
                            <th><input type="text" class="form-control" name="filters[email]"></th>
                            <th><input type="text" class="form-control" name="filters[code]"></th>
                            <th></th>
                            <th><button type="submit" class="btn btn-primary">{l s='Search' mod='wi_spent'}</button></th>
                        </tr>
                    </form>
                    </thead>
                    <tbody>
                    {foreach $wi_spent.coupons as $coupon}
                        <tr>
                            <td>{$coupon.id_customer|intval}</td>
                            <td>{$coupon.name|escape:'htmlall':'UTF-8'}</td>
                            <td>{$coupon.email|escape:'htmlall':'UTF-8'}</td>
                            <td>{$coupon.code|escape:'htmlall':'UTF-8'}</td>
                            <td>{$coupon.date_add|escape:'htmlall':'UTF-8'}</td>
                            <td></td>
                        </tr>
                    {/foreach}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" align="right">
                                <ul class="pagination">
                                    <li class="page-item">
                                        <a>
                                            {l s='Showing' mod='wi_spent'} <strong>{$wi_spent.coupons|count}</strong> {l s='of' mod='wi_spent'} <strong>{$wi_spent.total|intval}</strong>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        {*
                                        <a style="padding:0px">
                                            <select class="btn btn-mini aliexpress-page-switch" style="height:29px">
                                                {for $i=1 to $wi_spent.pages}
                                                    <option value="{$i|intval}" {if $i == $wi_spent.page}selected="selected"{/if}>{$i|intval}</option>
                                                {/for}
                                            </select>
                                        </a>
                                        *}
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link aliexpress-page-prev" aria-label="Previous" href="{$wi_spent.link|escape:'htmlall':'UTF-8'}&page={if 1 > $wi_spent.page-1}1{else}{$wi_spent.page-1}{/if}">
                                            <span aria-hidden="true">&laquo;</span>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li class="page-item aliexpress-page" data-page="{$wi_spent.page|intval}" data-pages="{$wi_spent.pages|intval}">
                                        <a><strong>{$wi_spent.page|intval}</strong></a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link aliexpress-page-next" aria-label="Next" href="{$wi_spent.link|escape:'htmlall':'UTF-8'}&page={if $wi_spent.pages+1 > $wi_spent.pages}{$wi_spent.page}{else}{$wi_spent.page+1|intval}{/if}">
                                            <span aria-hidden="true">&raquo;</span>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {/if}
        </div>
    </div>
</div>
