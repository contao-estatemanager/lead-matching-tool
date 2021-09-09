import {extend} from "./helper/extend";

export class LeadMatching {
    constructor(options) {
        this.options = extend(true, {
            form: '.hasteform_estate > form',
            configId: null,
            proximitySearch: {
                active: false,
                engine: 'system',
                selector: '[name="regions"]'
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
        // ToDo
    }
}
