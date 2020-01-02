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
            loadingClass: 'loading'
        };

        var init = function () {
            // extend default settings
            tool.settings = extend(true, defaults, settings);

            tool.container = document.querySelector(tool.settings.container);
            tool.counter = tool.container.querySelector(tool.settings.counter);
            tool.form = tool.container.querySelector(tool.settings.form);
            tool.formSubmit = tool.form.querySelector('[type="submit"]');

            // bind form events
            var inputs = tool.form.querySelectorAll('input, select');

            for (var i=0; i<inputs.length; i++) {
                var ev = 'blur';

                if(inputs[i].tagName.toLowerCase() === 'select'){
                    ev = 'change';
                }

                inputs[i].addEventListener(ev, handleFormEvents);
            }
        };

        var handleFormEvents = function(e) {
            var formData = new FormData(tool.form);
            var params = new URLSearchParams(formData);
            var request = new XMLHttpRequest();

            // disable form submit
            tool.formSubmit.disabled = true;

            // set loader class
            tool.counter.classList.add(tool.settings.loadingClass);

            request.addEventListener("load", function(e){
                var res = JSON.parse(this.responseText);

                if(!!res.error){
                    console.error(res.message);
                    return;
                }

                tool.formSubmit.disabled = !res.data.count;
                tool.counter.innerHTML = res.data.count;

                // remove loader class
                tool.counter.classList.remove(tool.settings.loadingClass);
            });

            request.open("GET", "/leadmatching/count/" + configId + "?" + params.toString());
            request.send();
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

        init();

        return pub;
    };

    return Constructor;
})();
