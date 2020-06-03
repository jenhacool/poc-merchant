(function($) {
    $(document).ready(function () {
        $('#billing_state').select2();
        $('#billing_city').select2();
        $('#billing_address_2').select2();

        $('body #billing_state').on('change select2:select select2-selecting', function () {
            $('#billing_city option').val('');

            var state = $(this).val();

            if( ! state ) {
                return false;
            }

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: poc_merchant_checkout_data.ajax_url,
                data: {
                    action: 'poc_merchant_load_address',
                    matp: state
                },
                context: this,
                beforeSend: function () {
                    $('#billing_city, #billing_address_2').prop('disabled', true);
                },
                success: function (response) {
                    $('#billing_city, #billing_address_2').html('').select2();
                    $('#billing_city').append(new Option('', ''));
                    $.each(response.data, function(index, item) {
                        $('#billing_city').append(new Option(item.name, item.maqh));
                    });
                    $('#billing_city, #billing_address_2').prop('disabled', false);
                }
            });
        });

        $("#billing_city").on("change select2-selecting", function () {
            var city = $(this).val();

            if( ! city ) {
                return false;
            }

            $.ajax({
                type: "post",
                dataType: "json",
                url: poc_merchant_checkout_data.ajax_url,
                data: {
                    action: "poc_merchant_load_address",
                    maqh: city
                },
                context: this,
                beforeSend: function () {
                    $('#billing_address_2').prop('disabled', true);
                },
                success: function (response) {
                    $("#billing_address_2").html("").select2();
                    $('#billing_address_2').append(new Option('', ''));
                    $.each(response.data, function(index, item) {
                        $('#billing_address_2').append(new Option(item.name, item.xaid));
                    });
                    $('#billing_address_2').prop('disabled', false);
                },
            });
        });

        $(document).on('change', '#poc-merchant-checkout-modify input, #poc-merchant-checkout-modify select', function() {
            $.ajax({
                type: "post",
                dataType: "json",
                url: poc_merchant_checkout_data.ajax_url,
                data: {
                    action: 'poc_merchant_update_cart_on_checkout',
                    form_data: $('form.checkout').serialize()
                },
                context: this,
                beforeSend: function () {
                    $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                },
                success: function (response) {
                    $(document.body).trigger('update_checkout');
                },
            });
        });

        $(document).on('click', '#poc-merchant-checkout-modify .poc-merchant-checkout-edit', function(e) {
            e.preventDefault();
            $(this).toggle();
            $('#poc-merchant-checkout-modify-form').toggle();
            $('#poc-merchant-checkout-modify .poc-merchant-checkout-cancel').show();
        });

        $(document).on('click', '#poc-merchant-checkout-modify .poc-merchant-checkout-cancel', function(e) {
            e.preventDefault();
            $(this).toggle();
            $('#poc-merchant-checkout-modify-form').toggle();
            $('#poc-merchant-checkout-modify .poc-merchant-checkout-edit').show();
        });
    });
})(jQuery);