/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import debounce from "debounce";
import Routing from "fos-router";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['input', 'submit'];
    static values = {
        'field': String,
        'excludeId': Number,
        'numeric': Boolean,
        'anonymous': Boolean,
    };

    initialize() {
        this.debounceCheck = debounce(this.debounceCheck.bind(this), 300);
    }

    /**
     * return true if user is unique
     */
    checkIfUnique() {
        // first we do some rudimentary input validation
        if(this.inputTarget.value === ''){
            return this.clearError();
        }
        if(this.numericValue && !(/^\d*$/.test(this.inputTarget.value))){
            return this.clearError();
        }

        const url = Routing.generate('_api_/people{._format}_get_collection', {[this.fieldValue]: this.inputTarget.value});
        return fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(response => {
            if (response.status === 200) {
                return response.json().then(data => {
                    if (data.length >= 1) {
                        const person = data[0];
                        if(!this.hasExcludeIdValue || this.excludeIdValue !== person.id) {
                            return this.setError(this.errorMessage(person));
                        }
                        return this.clearError();
                    } else {
                        return this.clearError();
                    }
                });
            }
        });
    }

    errorMessage(person){
        const href = Routing.generate('person_view', {'slug':person.slug});
        if(this.anonymousValue){
            return 'You have previously submitted an IGB entry form. Please contact your lab manager or theme admin for more information.';
        } else {
            return `This person is already in Connect: <a href="${href}">${person.name}</a>`;
        }
    }

    setError(message){
        $(this.inputTarget).next('.invalid-feedback').remove();
        this.inputTarget.setCustomValidity('This person is already in Connect'); // todo a more general error message?
        $(this.inputTarget).addClass('is-invalid').attr('aria-invalid', true).after(`<div class="invalid-feedback">${message}</div>`);
        return false;
    }

    clearError(){
        $(this.inputTarget).next('.invalid-feedback').remove();
        this.inputTarget.setCustomValidity('');
        $(this.inputTarget).removeClass('is-invalid').removeAttr('aria-invalid');
        return true;
    }

    debounceCheck(){
        this.checkIfUnique();
    }
}