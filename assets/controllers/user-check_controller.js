/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import debounce from "debounce";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['input', 'submit'];
    static values = {
        'field': String,
        'url': String,
        'errorUrl': String,
        'excludeId': Number,
        'numeric': Boolean,
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

        const url = this.urlValue + '?' + this.fieldValue + '=' + this.inputTarget.value;
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
                            const error = fetch(`${this.errorUrlValue}?id=${person.id}`).then(response => {
                                if (response.status === 200) {
                                    return response.text();
                                }
                            }).catch(function (err) {
                                // There was an error
                                console.warn('Something went wrong.', err);
                                return 'Something went wrong! Please reload the page and try again.';
                            });
                            return this.setError(error);
                        }
                        return this.clearError();
                    } else {
                        return this.clearError();
                    }
                });
            }
        });
    }

    setError(message){
        $(this.inputTarget).next('.invalid-feedback').remove();
        this.inputTarget.setCustomValidity('This person is already in Connect'); // todo a more general error message?
        message.then(html=>{
            $(this.inputTarget).addClass('is-invalid').attr('aria-invalid', true).after(html);
        });
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