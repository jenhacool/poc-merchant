jQuery(document).ready(function( $ ) {
    var $container = $('.woocommerce.widget_product_search');

    $container.append('<div class="poc-merchant-search-result"></div>');

    var $searchInput = $container.find('.search-field'),
        $searchResult = $container.find('.poc-merchant-search-result');

    $searchInput.prop('autocomplete', 'off');

    var timeout = null;

    $(window).click(function(e) {
        if(!$(e.target).is('.woocommerce.widget_product_search .search-field')) {
            $searchResult.hide();
        }
    });

    $searchInput.on('focus', function(e) {
        e.stopPropagation();
        if($searchResult.find('.poc-merchant-search-result-item').length > 0) {
            $searchResult.show();
        }
    });

    $searchInput.on('keyup', function(e) {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            $.ajax({
                url: poc_merchant_ajax_search_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'poc_merchant_product_search',
                    s: $(e.currentTarget).val()
                },
                beforeSend: function() {
                    $searchResult.empty();
                    $searchResult.hide();
                    $searchInput.prop('disabled', true);
                },
                success: function (response) {
                    var products = response.data;
                    if(products.length === 0) {
                        $searchResult.append('<span class="empty">Không tìm thấy sản phẩm</span>');
                    } else {
                        for (i = 0; i < products.length; i++) {
                            var html = $(
                                '<a href="' + products[i]['permalink'] + '" class="poc-merchant-search-result-item"><div class="item-image" style="background-image: url(' + products[i].thumbnail + ')"></div><div class="item-info"><h5>' + products[i].title + '</h5><span class="price">' + products[i].price + '</span></div></a>'
                            )
                            $searchResult.append(html);
                        }
                    }
                    $searchResult.show();
                    $searchInput.prop('disabled', false);
                }
            })
        }, 600);
    });
});