<div class="row">
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                {l s='Main Category' mod='asanifastcopy'}
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th>
                        <input type="checkbox" name="checkme" id="checkme" class="noborder" onclick="checkAll(this.form)" />
                    </th>
                    <th>{l s='Image' mod='asanifastcopy'}</th>
                    <th>{l s='Name' mod='asanifastcopy'}</th>
                    <th>{l s='Reference' mod='asanifastcopy'}</th>
                    <th>{l s='Default Category' mod='asanifastcopy'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $input.products as $key => $product}
                    <tr class="product_row" {if $key%2}class="alt_row"{/if}>
                        <td>
                            <input type="checkbox" class="cmsBox" name="prod[{$product['id_product']}]" id="prod_{$product['id_product']}" value="1"  />
                        </td>
                        <td>
                            <img src="{$product['image']}" />
                        </td>
                        <td>
                            {$product['name']}
                        </td>
                        <td>
                            {$product['reference']}
                        </td>
                        <td>
                            {if $product['id_category_default'] eq $input.category_primary}
                                <div >{l s='Yes' mod='asanifastcopy'}</div>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                <script>
                    $(".product_row").click(function(){

                        var checkbox = $(this).find("input");
                        if($(checkbox).is(":checked")) $(checkbox).prop("checked",false);
                        else $(checkbox).prop("checked",true);
                    });

                </script>


                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="panel">
            <div class="panel-heading">
                {l s='Target Category' mod='asanifastcopy'}
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th>{l s='Name' mod='asanifastcopy'}</th>
                    <th>{l s='Reference' mod='asanifastcopy'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $input.products_sec as $key => $product}
                    <tr {if $key%2}class="alt_row"{/if}>
                        <td>
                            {$product['name']}
                        </td>
                        <td>
                            {$product['reference']}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>