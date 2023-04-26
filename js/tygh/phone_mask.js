(function (_, $) {
  var mask_list;
  var is_custom_format;
  $.ceEvent('on', 'ce.commoninit', function (context) {
    is_custom_format = !!_.call_phone_mask;
    var $phone_elems = context.find('.cm-mask-phone'),
        phone_validation_mode = _.phone_validation_mode || 'international_format',
        is_international_format = phone_validation_mode === 'international_format',
        is_any_digits = phone_validation_mode === 'any_digits';

    if (!$phone_elems.length || is_international_format && !window.localStorage) {
      return;
    }

    if (is_international_format) {
      $phone_elems.attr('inputmode', 'numeric');
    }

    if (is_international_format || is_custom_format) {
      loadPhoneMasks().then(function (phone_masks) {
        _.phone_masks_list = phone_masks; // backward compatibility

        _.call_requests_phone_masks_list = _.phone_masks_list;
        mask_list = $.masksSort(_.phone_masks_list, ['#'], /[0-9]|#/, "mask");
        var mask_opts = {
          inputmask: {
            definitions: {
              '#': {
                validator: "[0-9]",
                cardinality: 1
              }
            },
            showMaskOnHover: false,
            autoUnmask: false,
            onKeyDown: function onKeyDown() {
              $(this).trigger('_input');
            }
          },
          match: /[0-9]/,
          replace: '#',
          list: mask_list,
          listKey: "mask"
        };
        $phone_elems.each(function (index, elm) {
          if (is_custom_format && $(elm).data('enableCustomMask')) {
            $(elm).inputmask({
              mask: _.call_phone_mask,
              showMaskOnHover: false,
              autoUnmask: false,
              onKeyDown: function onKeyDown() {
                $(this).trigger('_input');
              }
            });
          } else {
            if (!isMaskRemoveValue($(elm).val(), mask_opts)) {
              afterMaskRemoveValueProcess(elm, mask_opts);
              return;
            }

            $(elm).inputmasks(mask_opts);
          }

          bindEvents(elm);
          $(elm).addClass('js-mask-phone-inited');

          if ($(elm).val()) {
            $(elm).oneFirst('keypress keydown', function () {
              if (!validatePhone($(elm))) {
                $(elm).trigger('paste');
              }
            });
            $(elm).prop('defaultValue', $(elm).val());
          }
        });
      });
      registerValidatorPhoneMask();
    } else if (is_any_digits) {
      registerValidatorPhoneMask('is_any_digits');
    }
  });

  function validatePhone($input) {
    if ($.is.blank($input.val()) || !$input.hasClass('js-mask-phone-inited')) {
      return true;
    }

    var mask_is_valid = false;

    if (is_custom_format && $input.data('enableCustomMask')) {
      mask_is_valid = _toRegExp(_.call_phone_mask).test($input.val());
    } else {
      mask_list.forEach(function (mask) {
        mask_is_valid = mask_is_valid || _toRegExp(mask.mask).test($input.val());
      });
    }

    return mask_is_valid && $input.inputmask("isComplete");

    function _toRegExp(mask) {
      var _convertedMask = mask.str_replace('#', '.').str_replace('+', '\\+').str_replace('(', '\\(').str_replace(')', '\\)').str_replace('9', '[0-9]').str_replace('\\[0-9]', '9');

      return new RegExp(_convertedMask);
    }
  }

  function loadPhoneMasks() {
    var oldHashOfAvailableCountries = window.localStorage.getItem('availableCountriesHash'),
        newHashOfAvailableCountries = _.hash_of_available_countries,
        oldHashPhoneMasks = window.localStorage.getItem('phoneMasksHash'),
        newHashPhonesMasks = _.hash_of_phone_masks,
        rawPhoneMasks = window.localStorage.getItem('phoneMasks'),
        phoneMasks,
        d = $.Deferred();

    if (rawPhoneMasks && oldHashPhoneMasks === newHashPhonesMasks) {
      phoneMasks = JSON.parse(rawPhoneMasks);
    }

    if (!phoneMasks || newHashOfAvailableCountries !== undefined && oldHashOfAvailableCountries !== newHashOfAvailableCountries) {
      $.ceAjax('request', fn_url('phone_masks.get_masks'), {
        method: 'get',
        caching: false,
        data: {},
        callback: function callback(response) {
          if (!response || !response.phone_mask_codes) {
            return;
          }

          $.ceEvent('trigger', 'ce.phone_masks.masks_loaded', [response]);
          phoneMasks = Object.keys(response.phone_mask_codes).map(function (key) {
            return response.phone_mask_codes[key];
          });
          window.localStorage.setItem('phoneMasksHash', newHashPhonesMasks);
          window.localStorage.setItem('phoneMasks', JSON.stringify(phoneMasks));
          d.resolve(phoneMasks);
        },
        repeat_on_error: false,
        hidden: true,
        pre_processing: function pre_processing(response) {
          if (response.force_redirection) {
            delete response.force_redirection;
          }

          return false;
        },
        error_callback: function error_callback() {
          d.reject();
        }
      });
      window.localStorage.setItem('availableCountriesHash', newHashOfAvailableCountries);
    } else {
      d.resolve(phoneMasks);
    }

    return d.promise();
  }

  function bindEvents(elm) {
    // Hide the mask if the field is empty
    $(elm).on("blur.inputmasks", function () {
      if ($(this).val() === this.inputmask.maskset._buffer.join('')) {
        $(this).val('');
      }
    });
  }

  function registerValidatorPhoneMask(type) {
    $.ceFormValidator('registerValidator', {
      class_name: 'cm-mask-phone-label',
      message: type === 'is_any_digits' ? _.tr('error_validator_phone') : _.tr('error_validator_phone_mask'),
      func: type === 'is_any_digits' ? function (elm_id, elm, lbl) {
        return $.is.blank(elm.val()) || $.is.phone(elm.val());
      } : function (id) {
        return validatePhone($('#' + id));
      }
    });
  }

  function isMaskRemoveValue(prevValue, mask_opts) {
    var $virtualElem = $('<input>', {
      value: prevValue
    });
    $virtualElem.inputmasks(mask_opts);
    return prevValue === '' || prevValue !== '' && $virtualElem.val() !== '';
  }

  function afterMaskRemoveValueProcess(phoneField, mask_opts) {
    var $phoneField = $(phoneField);
    var $phoneLabel = $('[for="' + $phoneField.attr('id') + '"]');
    var $phoneFieldContainer = $phoneField.closest('.cm-field-container');
    var $form = $phoneField.closest('form'); // Register validator for invalid phone field

    $phoneLabel.addClass('cm-mask-phone-with-phone-label');
    $.ceFormValidator('registerValidator', {
      class_name: 'cm-mask-phone-with-phone-label',
      message: _.tr('error_validator_phone_mask_with_phone').str_replace('[phone]', $phoneField.val()),
      func: function func(elmId, elm) {
        return isMaskRemoveValue($(elm).val(), mask_opts);
      }
    }); // Temporarily disable scrolling and show validator notice for invalid phone field

    var isUndefinedNoScroll = typeof $phoneField.data('caNoScroll') === 'undefined';
    isUndefinedNoScroll && $phoneField.data('caNoScroll', true);
    isUndefinedNoScroll && $phoneFieldContainer.length && $phoneFieldContainer.data('caNoScroll', true);
    !$('[type=submit]', $form).length && !$('input[type=image]', $form).length && $form.ceFormValidator('setClicked', $('.cm-submit', $form).length ? $('.cm-submit:first', $form) : $phoneField);
    $form.ceFormValidator('check', true, null, true);
    isUndefinedNoScroll && $phoneField.removeData('caNoScroll');
    isUndefinedNoScroll && $phoneFieldContainer.length && $phoneFieldContainer.removeData('caNoScroll'); // Mask initialization on invalid phone field focus

    $phoneField.on('focus.maskPhoneWithPhoneLabel', function () {
      $phoneField.off('focus.maskPhoneWithPhoneLabel');
      $phoneLabel.removeClass('cm-mask-phone-with-phone-label');
      $phoneField.inputmasks(mask_opts);
      bindEvents(phoneField);
      $phoneField.addClass('js-mask-phone-inited');
      registerValidatorPhoneMask();
    });
  }
})(Tygh, Tygh.$);