{**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 *}
<div class="wi-spent-card">
    <div class="wi-spent-card">
        <div class="wi-spent-text">
            {l s='Scratch and win' mod='wi_spent'} 
            <div class="clearfix"></div>
            <strong>{$wi_spent.amount|escape:'htmlall':'UTF-8'}€</strong>
        </div>
    </div>
    <div class="wi-spend-card">
        <div class="wi-spend-base">
            {$wi_spent.code|escape:'htmlall':'UTF-8'}
        </div>
        <canvas id="scratch" width="300" height="60"></canvas>
    </div>
</div>