/* globals jQuery, woocommerce_fastspring_params, fastspring, wc_checkout_params */
var checkoutForm = jQuery('form.checkout')

function setLoadingDone () {
  checkoutForm.removeClass('processing').unblock()
}

function setLoadingOn () {
  checkoutForm.addClass('processing').block({
    message: null,
    overlayCSS: {
      background: '#fff',
      opacity: 0.6
    }
  })
}
// Get AJAX Url
function getAjaxURL (endpoint) {
  return woocommerce_fastspring_params.ajax_url.toString().replace('%%endpoint%%', 'wc_fastspring_' + endpoint)
}

// Before FS request handler
function fastspringBeforeRequestHandler () { // eslint-disable-line no-unused-vars
  setLoadingDone()
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
// AJAX call to get odrer payment page for receipt and potentially mark order as complete usign FS API
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
// Launch FS (popup or redirect)
function launchFastSpring (session) {
  fastspring.builder.secure(session.payload, session.key)
  fastspring.builder.checkout()
}
// Create order and return payload for FS
function setOrder (cb) {
  jQuery.ajax({
    type: 'POST',
    url: wc_checkout_params.checkout_url,
    data: checkoutForm.serialize(),
    dataType: 'json',
    success: function (result) {
      try {
        if (result.result === 'success') {
          cb(null, result)
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
// Checkout form handler - create order and launch FS
function doSubmit () {
  setLoadingOn()
  setOrder(function (err, result) {
    if (!err) {
      launchFastSpring(result.session)
    }
  })
}
// Error handler
function submitError (errorMessage) {
  setLoadingDone()
  jQuery('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove()
  checkoutForm.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + errorMessage + '</div>')
  checkoutForm.removeClass('processing')
  checkoutForm.find('.input-text, select, input:checkbox').trigger('validate').blur()
  jQuery('html, body').animate({
    scrollTop: (jQuery('form.checkout').offset().top - 100)
  }, 1000)
  jQuery(document.body).trigger('checkout_error')
}
// Check if FS is selected
function isFastSpringSelected () {
  return jQuery('.woocommerce-checkout input[name="payment_method"]:checked').attr('id') === 'payment_method_fastspring'
}
// Attach submit event if FS is selected
checkoutForm.on('checkout_place_order', function () {
  if (isFastSpringSelected()) {
    doSubmit()
    return false
  }
})
