import {extend} from "./helper/extend";
import autoComplete from "@tarekraafat/autocomplete.js";

export class LeadMatching {
    constructor(options) {
        this.options = extend(true, {
            form: '.hasteform_estate > form',
            configId: null,
            proximitySearch: {
                active: false,
                placeholder: 'Regionen',
                selector: '[name="regions"]',
                sourceUrl: '',
                source: 'regions'                   // [string|object]: Default 'regions'
            },
            countLive: {
                active: true,
                sourceUrl: '',
                route: '/leadmatching/count',
                selector: '[data-counter]',
                format: (val) => { return val; }
            }
        }, options || {})

        this.form = document.querySelector(this.options.form)

        if(this.options.countLive?.active) {
            this._initLiveCounting()
        }

        if(this.options.proximitySearch?.active) {
            this._initProximitySearch()
        }
    }

    _initLiveCounting () {
        this.domCounter = document.querySelectorAll(this.options.countLive.selector);
        this.domState = [];

        for(const input of this.form.elements) {
            let eventName = 'blur'

            switch(input.tagName.toLowerCase()) {
                case 'select':
                    eventName = 'change'
                    break

                case 'button':
                    continue
            }

            // Set current input state
            this.domState[input.name] = input.value;

            // Add input event
            input.addEventListener(eventName, (e) => this._count(e));
        }
    }

    _count(event){
        const input = event.target;

        // Check if the values have changed, otherwise do nothing
        if(this.domState[input.name] === input.value) {
            return;
        }

        // Set new input state
        this.domState[input.name] = input.value;

        // Prepare transfer of data
        const source = this.options.countLive.sourceUrl + this.options.countLive.route + '/' + this.options.configId
        const formData = new FormData(this.form)
        const fetchOptions = {
            method: 'POST',
            body: formData,
        }

        // Transfer data and retrieve new number of items
        fetch(source, fetchOptions)
            .then(response => response.json())
            .then(data => {
                for(const countElement of this.domCounter) {
                    countElement.innerText = this.options.countLive.format(data.count);
                }
            })
    }

    _initProximitySearch () {
        const searchOptions = this.options.proximitySearch;
        const domRegionInput = document.querySelector(searchOptions.selector)

        domRegionInput.type = 'search'
        domRegionInput.spellcheck = false
        domRegionInput.autocorrect = 'off'
        domRegionInput.autocomplete = 'off'
        domRegionInput.autocapitalize = 'off'

        // Check source
        if(searchOptions.source === 'regions') {
            searchOptions.source = this._getSystemRegionSource()
        }

        this.autocomplete = new autoComplete({
            selector: searchOptions.selector,
            wrapper: false,
            placeHolder: searchOptions.placeholder,
            data: searchOptions.source,
            resultsList: {
                element: (list, data) => {
                    if (!data.results.length) {
                        // Create "No Results" message element
                        const message = document.createElement("div");
                        // Add class to the created element
                        message.setAttribute("class", "no_result");
                        // Add message text content
                        message.innerHTML = `<span>Found No Results for "${data.query}"</span>`;
                        // Append message element to the results list
                        list.prepend(message);
                    }
                },
                noResults: true,
            },
            resultItem: {
                highlight: true
            },
            events: {
                input: {
                    selection: (event) => {
                        const selection = event.detail.selection.value;
                        this.autocomplete.input.value = selection;
                    }
                }
            }
        });
    }

    _getSystemRegionSource() {
        return {
            src: async (query) => {
                try {
                    const post = new FormData();
                          post.append('query', query);

                    const source = await fetch(this.options.proximitySearch.sourceUrl + '/region/query', {
                        method: 'POST',
                        body: post,
                    });

                    const data = await source.json();
                    console.log(data.results);

                    return data.results;
                } catch (error) {
                    return error;
                }
            },
            keys: ["region"]
        };
    }
}
