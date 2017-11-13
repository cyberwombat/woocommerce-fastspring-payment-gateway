/* globals jQuery, woocommerce_fastspring_params */

// Get AJAX Url
function getAjaxURL (endpoint) {
  return woocommerce_fastspring_params.ajax_url
    .toString()
    .replace('%%endpoint%%', 'wc_fastspring_' + endpoint)
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
