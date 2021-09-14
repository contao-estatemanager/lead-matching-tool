import { extend } from "./helper/extend";
import { Loader } from '@googlemaps/js-api-loader';
import autoComplete from "@tarekraafat/autocomplete.js";

export class LeadMatching {
    constructor(options) {
        this.options = extend(true, {
            form: '.hasteform_estate > form',
            configId: null,
            baseUrl: '',
            proximitySearch: {
                active: false,
                autoComplete: {
                    selector: '[name="regions"]',
                    placeHolder: 'Regionen',
                    wrapper: true,
                    submit: false,
                    debounce: 300,
                    threshold: 2,
                },
                inputs: {
                    lat: '[name="region_lat"]',
                    lng: '[name="region_lng"]'
                },
                engine: 'google',
                google: {
                    loader: {
                        apiKey: '',
                        version: 'weekly',
                        libraries: ['places']
                    },
                    autocomplete: {
                        types: ['(cities)'],
                        componentRestrictions: {
                            country: ['deu','aut','che']
                        }
                    }
                },
                route: '/region/all',
                noResultsFound: 'Found No Results for %s',
            },
            countLive: {
                active: true,
                route: '/leadmatching/count',
                selector: '[data-counter]',
                format: (val) => { return val }
            }
        }, options || {})

        this.form = document.querySelector(this.options.form)
        this.form.addEventListener('submit', (e) => this._onSubmitForm(e))

        this.strict = false;
        this.valid  = true;
        this.validationErrors = [];

        if(this.options.countLive?.active) {
            this._initLiveCounting()
        }

        if(this.options.proximitySearch?.active) {
            this._initProximitySearch()
        }
    }

    /**
     * Callback on submit
     *
     * @param e
     * @returns {boolean}
     * @private
     */
    _onSubmitForm(e) {
        if(!this.valid){
            // Call validation errors
            for(const fn of this.validationErrors){
                fn()
            }

            e.preventDefault()
            return false
        }
    }

    /**
     * Initialize proximity search
     *
     * @private
     */
    _initProximitySearch() {
        const searchOptions = this.options.proximitySearch
        const domRegionInput = document.querySelector(searchOptions.autoComplete.selector)

        domRegionInput.type = 'search'
        domRegionInput.spellcheck = false
        domRegionInput.autocorrect = 'off'
        domRegionInput.autocomplete = 'off'
        domRegionInput.autocapitalize = 'off'
        domRegionInput.parentElement.classList.add('regions')

        // Set strict mode if field is required
        this.strict = domRegionInput.hasAttribute('required')

        if(!this.strict){
            this.valid = true
        }else{
            let isValid = true;

            // Check if already valid data exists
            for(const key in searchOptions.inputs) {
                const input = document.querySelector(searchOptions.inputs[key])
                if(input.value.trim() === ''){
                    isValid = false
                    break
                }
            }

            this.valid = isValid
            this.validationErrors.push(() => {
                domRegionInput.value = ''
                this.form.reportValidity()
            })
        }

        // Default autoComplete options
        const autoCompleteDefaults = {
            data: this._systemAutoCompleteData(),
            resultsList: {
                element: (list, data) => {
                    if (!data.results.length) {
                        const message = document.createElement("div")

                        message.setAttribute("class", "no_result")
                        message.innerHTML = searchOptions.noResultsFound.replace('%s', data.query)

                        list.prepend(message)
                    }
                },
                maxResults: 10,
                noResults: true
            },
            resultItem: {
                highlight: true
            },
            events: {
                input: {
                    focus: (event) => {
                        // Save current value
                        this.lastValue = event.target.value;

                        // Open autocomplete if any search made
                        if(this.autocomplete.feedback?.matches?.length){
                            this.autocomplete.open()
                        }
                    },
                    keyup: (event) => {
                        // Clear fields on value change
                        if(this.lastValue !== event.target.value){
                            for(const key in searchOptions.inputs) {
                                const input = document.querySelector(searchOptions.inputs[key])
                                input.value = ''
                            }

                            this.valid = false
                        }
                    },
                    blur: (event) => {
                        // Is list open (no selection made), select first element
                        if(this.autocomplete.isOpen && this.autocomplete.feedback?.matches?.length){
                            this.autocomplete.select(0)
                        }else{
                            this.autocomplete.close()
                        }

                        // Count
                        if(this.options.countLive?.active) {
                            this.count(event)
                        }
                    },
                    selection: (event) => {
                        const selection = event.detail.selection
                        const value = selection.value[selection.key]

                        this.autocomplete.input.value = value
                        this.lastValue = value;

                        for(const key in searchOptions.inputs) {
                            const input = document.querySelector(searchOptions.inputs[key])

                            if(key in selection.value && !!input) {
                                input.value = selection.value[key]
                            }
                        }

                        this.valid = true;
                    }
                }
            }
        }

        if(searchOptions.engine === 'google') {
            this.google = null
            this.loader = new Loader(this.options.proximitySearch.google.loader)
                .load()
                .then((google) => this.google = google)

            autoCompleteDefaults.data = this._googleAutocompleteData()
        }

        // Merge options and initialize autComplete
        this.autocomplete = new autoComplete(
            extend(true, autoCompleteDefaults, searchOptions.autoComplete || {})
        );
    }

    /**
     * Fetch records from system (options.proximitySearch.engine = system)
     *
     * @returns {{cache: boolean, src: ((function(*=): Promise<*|undefined>)|*), keys: string[]}}
     * @private
     */
    _systemAutoCompleteData() {
        return {
            src: async (query) => {
                try {
                    const post = new FormData()
                    post.append('query', query)

                    const source = await fetch(this.options.baseUrl + searchOptions.route, {
                        method: 'POST',
                        body: post
                    });

                    const data = await source.json()

                    return data.results
                } catch (error) {
                    return error
                }
            },
            keys: ["title"],
            cache: true
        }
    }

    /**
     * Fetch records from google (options.proximitySearch.engine = google)
     *
     * @returns {{cache: boolean, src: ((function(*=): Promise<unknown>)|*), keys: string[]}}
     * @private
     */
    _googleAutocompleteData() {
        return {
            src: async (query) => {

                if(!this.google){
                    console.error('Google Services are not available')
                }

                try {
                    let data = [];

                    const geocoder = new this.google.maps.Geocoder;
                    const autocomplete = new this.google.maps.places.AutocompleteService();
                    const promise = new Promise((resolve => {
                        autocomplete.getPlacePredictions({
                            ...{input: query},
                            ...this.options.proximitySearch.google.autocomplete
                        }, (predictions, status) => {

                            if(predictions) {
                                let geoprom = []

                                for(const prediction of predictions){
                                    geoprom.push(new Promise((resolve) => {
                                        geocoder.geocode({
                                            placeId: prediction.place_id
                                        }, (response, status) => {
                                            resolve(response)
                                        })
                                    }));
                                }

                                Promise.all(geoprom).then((items) => {
                                    for (const item of items){
                                        data.push({
                                            ...item[0],
                                            'lat': item[0].geometry.location.lat(),
                                            'lng': item[0].geometry.location.lng()
                                        })
                                    }

                                    resolve(data)
                                });
                            }
                        })
                    }))

                    return promise
                } catch (error) {
                    return error
                }
            },
            keys: ["formatted_address"],
            cache: false
        }
    }

    /**
     * Initialize live counting
     *
     * @private
     */
    _initLiveCounting() {
        this.domCounter = document.querySelectorAll(this.options.countLive.selector)
        this.domState = []

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
            this.domState[input.name] = input.value

            // Add input event
            input.addEventListener(eventName, (e) => this.count(e))
        }
    }

    /**
     * Count by current form data
     *
     * @param event
     */
    count(event){
        const input = event.target

        // Check if the values have changed, otherwise do nothing
        if(this.domState[input.name] === input.value) {
            return;
        }

        // Set new input state
        this.domState[input.name] = input.value

        // Prepare transfer of data
        const source = this.options.baseUrl + this.options.countLive.route + '/' + this.options.configId
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
                    countElement.innerText = this.options.countLive.format(data.count)
                }
            })
    }
}
