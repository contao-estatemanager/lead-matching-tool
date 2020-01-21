/**
 * Lead-Matching-Tool
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 * @version 0.0.1
 * @licence https://github.com/contao-estatemanager/lead-matching-tool
 */
var LeadMatchingTool = (function () {

    'use strict';

    var Constructor = function (configId, settings) {
        var pub = {};
        var tool = {};
        var defaults = {
            container: 'TOOL_SELECTOR',
            counter: 'COUNTER_SELECTOR',
            form: 'FORM_SELECTOR',
            loadingClass: 'loading',
            loadingContainer: 'COUNTER_LOADING_SELECTOR',
        };

        var init = function () {
            // extend default settings
            tool.settings = extend(true, defaults, settings);

            tool.container = document.querySelector(tool.settings.container);
            tool.counter = tool.container.querySelector(tool.settings.counter);
            tool.loadingContainer = tool.container.querySelector(tool.settings.loadingContainer);
            tool.form = tool.container.querySelector(tool.settings.form);
            tool.formSubmit = tool.form.querySelector('[type="submit"]');
            tool.request = null;

            // bind form events
            var inputs = tool.form.querySelectorAll('input, select');

            for (var i=0; i<inputs.length; i++) {
                var ev = 'blur';

                if(inputs[i].tagName.toLowerCase() === 'select'){
                    ev = 'change';
                }

                inputs[i].addEventListener(ev, handleFormEvents);
            }

            var marketingInput = tool.form.querySelector('[name="marketingType"]');

            if(!!marketingInput) {
                tool.marketingInput = marketingInput;
                disable('[name="price"]', !marketingInput.value);
            }
        };

        var handleFormEvents = function(e) {
            // disable price field if no marketing type set
            if(!!tool.marketingInput) {
                disable('[name="price"]', !tool.marketingInput.value);
            }

            if(tool.request !== null) {
                tool.request.abort();
            }

            var formData = new FormData(tool.form);
            var params = new URLSearchParams(formData);

            tool.request = new XMLHttpRequest();

            // disable form submit
            tool.formSubmit.disabled = true;

            // set loader class
            if(!!tool.loadingContainer){
                tool.loadingContainer.classList.add(tool.settings.loadingClass);
            }else{
                tool.counter.classList.add(tool.settings.loadingClass);
            }

            tool.request.addEventListener("load", function(e){
                var res = JSON.parse(this.responseText);

                if(!!res.error){
                    console.error(res.message);
                    return;
                }

                tool.formSubmit.disabled = !res.data.count;
                tool.counter.innerHTML = number_format(res.data.count, 0, ',', '.');

                // remove loader class
                if(!!tool.loadingContainer){
                    tool.loadingContainer.classList.remove(tool.settings.loadingClass);
                }else{
                    tool.counter.classList.remove(tool.settings.loadingClass);
                }

                tool.request = null;
            });

            tool.request.open("GET", "/leadmatching/count/" + configId + "?" + params.toString());
            tool.request.send();
        };

        var disable = function(selector, condition){
            tool.form.querySelector(selector).disabled = condition;
        };

        var extend = function () {
            // Variables
            var extended = {};
            var deep = false;
            var i = 0;
            var length = arguments.length;

            // Check if a deep merge
            if ( Object.prototype.toString.call( arguments[0] ) === '[object Boolean]' ) {
                deep = arguments[0];
                i++;
            }

            // Merge the object into the extended object
            var merge = function (obj) {
                for ( var prop in obj ) {
                    if ( Object.prototype.hasOwnProperty.call( obj, prop ) ) {
                        // If deep merge and property is an object, merge properties
                        if ( deep && Object.prototype.toString.call(obj[prop]) === '[object Object]' ) {
                            extended[prop] = extend( true, extended[prop], obj[prop] );
                        } else {
                            extended[prop] = obj[prop];
                        }
                    }
                }
            };

            // Loop through each object and conduct a merge
            for ( ; i < length; i++ ) {
                var obj = arguments[i];
                merge(obj);
            }

            return extended;
        };

        function number_format(number, decimals, decPoint, thousandsSep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number;
            var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
            var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
            var dec = (typeof decPoint === 'undefined') ? '.' : decPoint;
            var s = '';

            var toFixedFix = function (n, prec) {
                if (('' + n).indexOf('e') === -1) {
                    return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
                } else {
                    var arr = ('' + n).split('e');
                    var sig = '';
                    if (+arr[1] + prec > 0) {
                        sig = '+'
                    }
                    return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
                }
            };

            s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }

            return s.join(dec)
        }

        init();

        return pub;
    };

    return Constructor;
})();
