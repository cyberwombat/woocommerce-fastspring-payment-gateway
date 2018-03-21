/* globals jQuery, woocommerce_fastspring_params, fastspring, wc_checkout_params */

  var checkoutForm = jQuery('form.checkout')

  function getSpinnerPath (el) {
    var scriptElements = document.getElementsByTagName('script')
    for (var i = 0; i < scriptElements.length; i++) {
      var source = scriptElements[i].src
      if (source.indexOf('fastspring-checkout') > -1) {
        var location = source.substring(0, source.indexOf('fastspring-checkout')) + '../img/spin.svg'
        return location
      }
    }
    return false
  }

  function setLoading () {
    // checkoutForm.block({
    //   message: null,
    //   overlayCSS: {
    //     background: '#fff',
    //     opacity: 0.6
    //   }
    // })
  }
  function setLoadingFluid () {
    if (!document.getElementById('fscLoader')) {
      var overlay = document.createElement('div')
      overlay.id = 'fscLoader'
      overlay.setAttribute('style', 'background: -webkit-linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.8)); background: -o-linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.8)); background: -moz-linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.8)); background: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.8));')
      overlay.style.width = '100%'
      overlay.style.height = '100%'
      overlay.style.position = 'fixed'
      overlay.style.top = '0'
      overlay.style.left = '0'
      overlay.style.zIndex = '-1'
      overlay.style.transitionProperty = 'opacity'
      overlay.style.transitionDuration = '0.5s'
      overlay.style.opacity = '0'

      var spinner = document.createElement('img')
      spinner.src = getSpinnerPath()
      spinner.style.position = 'absolute'
      spinner.style.top = '50%'
      spinner.style.marginTop = '-50px'
      spinner.style.left = '50%'
      spinner.style.marginLeft = '-50px'
      spinner.style.zIndex = '100000000000000'
      spinner.style.display = 'block'

      overlay.appendChild(spinner)

      document.body.appendChild(overlay)
    }
  }

  function setLoadingFluidDone () {
    checkoutForm.removeClass('processing')
    var overlay = document.getElementById('fscLoader')
    if (overlay) {
      overlay.style.zIndex = '-1'
      overlay.style.display = 'none'
    }
  }

  function setLoadingFluidOn () {
    checkoutForm.addClass('processing')
    var overlay = document.getElementById('fscLoader')

    if (overlay) {
      overlay.style.zIndex = '100000000000000'

      setTimeout(function () {
        overlay.style.opacity = '1'
      }, 100)
    }
  }

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
  // FS Popup close handler - redirect to receipt page if valid
  function fastspringPopupCloseHandler (data) { // eslint-disable-line no-unused-vars
    setLoadingDone()

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
  function launchFS (session) {
    fastspring.builder.secure(session.payload, session.key)
    fastspring.builder.checkout()

    disableFSOverlay()
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
        launchFS(result.session)
      }
    })
  }

  function disableFSOverlay () {
    var o = document.getElementById('fscCanvas')
    if (o) o.style.display = 'none'
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

  // Attach submit event
  checkoutForm.on('checkout_place_order', function () {
    doSubmit()
    return false
  })

  // Preload this in back - it makes spinner show better
  setLoading()
