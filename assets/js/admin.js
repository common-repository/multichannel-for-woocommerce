jQuery(document).ready(function($) {
    var mcwcShowModal = function(title, content) {
        $('#mcwc_tb_title').html(title);
        $('#mcwc_tb_content').html(content);
        tb_show('Multichannel for WooCommerce', '#TB_inline?width=420&amp;height=180&amp;inlineId=mcwc_tb&amp;modal=true');
    }

    var mcwcDeleteShipment = function() {
         var shipment_id = $(this).closest('li').attr('rel');
         if (confirm('Are you sure you wish to delete this tracking number? This action cannot be undone.')) {
             var data = {
                 'action': 'mcwc_delete_shipment',
                 'security': $('#mcwc_delete_shipment_nonce').val(),
                 'shipment': {
                     'order_id': $('#mcwc_shipment_order_id').val(),
                     'shipment_id': shipment_id
                 }
             };
             jQuery.post(ajaxurl, data, function(response) {
                 var responseDecoded = JSON.parse(response);
                 if (responseDecoded['success']) {
                     $('#mcwc_shipments li[rel="' + shipment_id + '"]').remove();
                 } else {
                     alert('Error');
                 }
             }).fail(function() {
                 alert('Error');
             });
         }
         return false;
    }

    $('#mcwc_connect').click(function() {
        var originalButtonText = $('#mcwc_connect').html();
        var i = 0;
        var buttonAnimationInterval = setInterval(function() {
            i = ++i % 4;
            $('#mcwc_connect').html('Processing' + Array(i+1).join('.'));
        }, 500);
        $('#mcwc_connect').prop('disabled', true);

        var data = {
            'action': 'mcwc_initialize_connection',
            'security': $('#mcwc_connect_nonce').val()
        };
        jQuery.post(ajaxurl, data, function(response) {
            var responseDecoded = JSON.parse(response);
            if (responseDecoded['success']) {
                window.location.search += '&registered=1';
            } else {
                if (typeof responseDecoded['message'] === 'string') {
                    mcwcShowModal('Multichannel for WooCommerce', responseDecoded['message']);
                } else {
                    mcwcShowModal('Multichannel for WooCommerce', 'Connection with GeekSeller server could not be established. Please try again later.');
                }
                $('#mcwc_connect').html(originalButtonText);
                clearInterval(buttonAnimationInterval);
                $('#mcwc_connect').prop('disabled', false);
            }
        }).fail(function() {
            mcwcShowModal('Multichannel for WooCommerce', 'Connection with GeekSeller server could not be established. Please try again later.');
            $('#mcwc_connect').html(originalButtonText);
            clearInterval(buttonAnimationInterval);
            $('#mcwc_connect').prop('disabled', false);
        });
    });

    $('#mcwc_manage_integrations').click(function() {
        var originalButtonText = $('#mcwc_manage_integrations').html();
        var i = 0;
        var buttonAnimationInterval = setInterval(function() {
            i = ++i % 4;
            $('#mcwc_manage_integrations').html('Loading' + Array(i+1).join('.'));
        }, 500);
        $('#mcwc_manage_integrations').prop('disabled', true);

        var data = {
            'action': 'mcwc_manage_integrations',
            'security': $('#mcwc_manage_integrations_nonce').val()
        };
        jQuery.post(ajaxurl, data, function(response) {
            var responseDecoded = JSON.parse(response);
            if (responseDecoded['success']) {
                window.location = responseDecoded['url'];
            } else {
                if (typeof responseDecoded['message'] === 'string') {
                    mcwcShowModal('Multichannel for WooCommerce', responseDecoded['message']);
                } else {
                    mcwcShowModal('Multichannel for WooCommerce', 'Connection with GeekSeller server could not be established. Please try again later.');
                }
                $('#mcwc_manage_integrations').html(originalButtonText);
                clearInterval(buttonAnimationInterval);
                $('#mcwc_manage_integrations').prop('disabled', false);
            }
        }).fail(function() {
            mcwcShowModal('Multichannel for WooCommerce', 'Connection with GeekSeller server could not be established. Please try again later.');
            $('#mcwc_manage_integrations').html(originalButtonText);
            clearInterval(buttonAnimationInterval);
            $('#mcwc_manage_integrations').prop('disabled', false);
        });
    });

    $('#mcwc_chat_link').click(function() {
        $('#mcwc_manage_integrations').click();
        return false;
    });

    $('#mcwc_show_shipment_form').click(function() {
        $('#mcwc_shipment_form').show();
        $('#mcwc_show_shipment_form').hide();
    });

    $('#mcwc_shipment_carrier').change(function() {
        if ($('#mcwc_shipment_carrier').val() == 'other') {
            $('#mcwc_shipment_carrier_other').show(function(){
                $('#mcwc_shipment_carrier_other').focus();
            });
            $('#mcwc_tracking_url_field').show();
        } else {
            $('#mcwc_shipment_carrier_other').hide();
            $('#mcwc_tracking_url_field').hide();
        }
    });

    $('#mcwc_shipment_method').change(function() {
        if ($('#mcwc_shipment_method').val() == 'other') {
            $('#mcwc_shipment_method_other').show(function(){
                $('#mcwc_shipment_method_other').focus();
            });
        } else {
            $('#mcwc_shipment_method_other').hide();
        }
    });

    $('#mcwc_save_shipment').click(function() {
        var data = {
            'action': 'mcwc_save_shipment',
            'security': $('#mcwc_save_shipment_nonce').val(),
            'shipment': {
                'order_id': $('#mcwc_shipment_order_id').val(),
                'carrier': $('#mcwc_shipment_carrier').val(),
                'method': $('#mcwc_shipment_method').val(),
                'tracking_number': $('#mcwc_tracking_number').val(),
                'tracking_url': $('#mcwc_tracking_url').val(),
                'shipment_date': $('#mcwc_shipment_date').val(),
                'shipment_hour': $('#mcwc_shipment_hour').val(),
                'shipment_minute': $('#mcwc_shipment_minute').val(),
                'set_order_completed': $('#mcwc_shipment_set_order_completed').val(),
            }
        };
        if (data.shipment.carrier == 'other') {
            data.shipment.carrier = $('#mcwc_shipment_carrier_other').val();
        }
        if (data.shipment.method == 'other') {
            data.shipment.method = $('#mcwc_shipment_method_other').val();
        }
        jQuery.post(ajaxurl, data, function(response) {
            var responseDecoded = JSON.parse(response);
            if (responseDecoded['success']) {
                $('#mcwc_shipments').append(responseDecoded['shipment_html']);
                $('.mcwc_delete_shipment').click(mcwcDeleteShipment);
                if (data.shipment.set_order_completed) {
                    $('#order_status').val('wc-completed');
                    $('#order_status').trigger('change');
                }
            }
            $('#mcwc_shipment_form').hide();
            $('#mcwc_show_shipment_form').show();
        }).fail(function() {
            alert('Error');
        });
    });

    $('.mcwc_delete_shipment').click(mcwcDeleteShipment);
});
