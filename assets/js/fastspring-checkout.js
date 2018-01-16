/* globals jQuery, woocommerce_fastspring_params, fastspring, wc_add_to_cart_params, wc_checkout_params */

  var checkoutForm = jQuery('form.checkout')
  // jQueryform = jQuery('fom.checkout')
  // Get AJAX Url
  function getAjaxURL (endpoint) {
    return woocommerce_fastspring_params.ajax_url.toString().replace('%%endpoint%%', 'wc_fastspring_' + endpoint)
  }
  // FS Popup close handler - redirect to receipt page if valid
  function fastspringPopupCloseHandler (data) { // eslint-disable-line no-unused-vars
    // data.id is the FS order ID - only returned on payment instead of just closing modal
    if (data && data.reference) {
      requestPaymentCompletionUrl(data || {}, function (err, res) {
        if (!err) {
          window.location = res.redirect_url
        }
      })
    }
  }

  // AJAX call to mark order as complete and get oder payment page for redirect
  function requestPaymentCompletionUrl (data, cb) { // eslint-disable-line no-unused-vars
    data.security = woocommerce_fastspring_params.nonce.receipt
    jQuery.ajax({
      type: 'POST',
      dataType: 'json',
      data: JSON.stringify(data),
      url: getAjaxURL('get_receipt'),
      success: function (response) {
        cb(null, response)
      },
      error: function (xhr, err, e) {
        cb(xhr.responseText)
      }
    })
  }
  // AJAX call to fetch secure payload
  function requestPayload (cb) { // eslint-disable-line no-unused-vars
    var data = {
      security: woocommerce_fastspring_params.nonce.receipt
    }
    jQuery.ajax({
      type: 'POST',
      dataType: 'json',
      data: JSON.stringify(data),
      url: getAjaxURL('get_payload'),
      success: function (response) {
        cb(null, response)
      },
      error: function (xhr, err, e) {
        cb(xhr.responseText)
      }
    })
  }

  function doSubmit () {
    checkoutForm.addClass('processing')
    var formData = checkoutForm.data()
    if (formData['blockUI.isBlocked'] !== 1) {
      // checkoutForm.block({
      //   message: null,
      //   overlayCSS: {
      //     background: '#000',
      //     opacity: 0.6
      //   }
      // })
    }

    jQuery.ajax({
      type: 'POST',
      url: wc_checkout_params.checkout_url,
      data: checkoutForm.serialize(),
      dataType: 'json',
      success: function (result) {
        try {
          if (result.result === 'success') {
            requestPayload(function (err, session) {
              if (!err) {
                fastspring.builder.secure(session.payload, session.key)
                fastspring.builder.checkout()
              } else {
                return submitError('<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>')
              }
            })
          } else if (result.result === 'failure') {
            throw new Error('Result failure')
          } else {
            throw new Error('Invalid response')
          }
        } catch (err) {
          // Reload page
          if (result.reload === true) {
            window.location.reload()
            return
          }
          // Trigger update in case we need a fresh nonce
          if (result.refresh === true) {
            jQuery(document.body).trigger('update_checkout')
          }
          // Add new errors
          if (result.messages) {
            submitError(result.messages)
          } else {
            submitError('<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>')
          }
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        submitError('<div class="woocommerce-error">' + errorThrown + '</div>')
      }
    })
  }

  function submitError (errorMessage) {
    jQuery('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove()
    checkoutForm.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + errorMessage + '</div>')
    checkoutForm.removeClass('processing').unblock()
    checkoutForm.find('.input-text, select, input:checkbox').trigger('validate').blur()
    jQuery('html, body').animate({
      scrollTop: (jQuery('form.checkout').offset().top - 100)
    }, 1000)
    jQuery(document.body).trigger('checkout_error')
  }

  checkoutForm.on('checkout_place_order', function () {
    doSubmit()
    return false
  })
