import { Tygh } from '../..';
import { defaultOptions } from './defaultOptions';
import { ObjectPicker } from "./objectPicker";
import $ from "jquery";

const _ = Tygh;

function execute($elems, method, ...params) {
    $elems.each(function () {
        let $elem = $(this),
            picker = $elem.data('caObjectPicker');

        if (!picker || typeof picker[method] !== 'function') {
            return;
        }

        picker[method](...params);
    });
}

function init($elems, options) {
    if (!$elems.length) {
        return;
    }

    loadSelect2().done(function () {
        let objectsMap = {},
            elemsMap = {};

        $elems.each(function () {
            let $elem = $(this),
                options = $.extend({}, defaultOptions, getOptions($elem, defaultOptions), options);

            if (options.lazyLoad && !$elem.data('caObjectPickerLazyLoadInited')) {
                attachLazyLoadEvent($elem);

                return;
            }
            
            let picker = new ObjectPicker($elem, options);

            $elem.data('caObjectPicker', picker);

            if (picker.isAjaxSource()) {
                if (!objectsMap[picker.options.objectType]) {
                    objectsMap[picker.options.objectType] = new Set();
                }

                if (!elemsMap[picker.options.objectType]) {
                    elemsMap[picker.options.objectType] = [];
                }

                picker.getSelectedObjectIds().forEach(function (v) {
                    if (v && v != 0) {
                        objectsMap[picker.options.objectType].add(v);
                    }
                });

                elemsMap[picker.options.objectType].push($elem);
            }
        });

        $.each(objectsMap, function (objectType, objectIds) {
            if (objectIds.size) {
                ObjectPicker.loadObjects($(elemsMap[objectType]), objectType, objectIds);
            }
        });
    });
}

function loadSelect2() {
    var defer = $.Deferred();

    if (!$.fn.select2) {
        $.getScript('js/lib/select2/dist/js/select2.full.min.js', defer.resolve);
    } else {
        defer.resolve();
    }

    return defer.promise();
}

function getOptions($elem) {
    var options = {};

    for (var key in defaultOptions) {
        options[key] = $elem.data($.camelCase(`ca-object-picker-${key}`));
    }

    return options;
}

function attachLazyLoadEvent($elem) {
    const $objectPickerContainer = $elem.closest('[data-object-picker]');

    $('[data-ca-object-picker-lazy-load-field]', $objectPickerContainer).on('click', function (e) {
        if ($elem.hasClass("select2-hidden-accessible")) {
            // Select2 has been initialized
            return;
        }
        
        lazyLoadInit($elem);
    });

    $('[data-ca-object-picker-lazy-load-clear]', $objectPickerContainer).on('click', function (e) {
        const newSelectedValues = ($elem.attr('multiple'))
            ? $elem.val().filter(item => item !== ('' + $(this).data('caObjectPickerLazyLoadSelect2Id')))
            : null;

        lazyLoadInit($elem);
        
        $elem.val(newSelectedValues).trigger('change');
    });
}

function lazyLoadInit($elem) {
    const $objectPickerContainer = $elem.closest('[data-object-picker]');
    const $lazyLoadField = $('[data-ca-object-picker-lazy-load-field]', $objectPickerContainer);

    $elem.data({
        'caObjectPickerAutoopen': true,
        'caObjectPickerLazyLoadInited': true,
    }).ceObjectPicker();

    $lazyLoadField.remove();
}

/**
 * @param {JQueryStatic} $
 */
export const ceObjectPickerInit = function ($) {
    $.fn.ceObjectPicker = function (method, ...params) {
        if (!method || method === 'init') {
            init($(this), params[0] || {}, defaultOptions);
        } else {
            execute($(this), method, ...params);
        }

        return $(this);
    };
}