/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['input', 'submit'];
    static values = {
        'field': String,
        'url': String,
        'errorUrl': String,
        'excludeId': Number,
    };

    /**
     * return true if user is unique
     */
    checkIfUnique() {
        const url = this.urlValue + '?' + this.fieldValue + '=' + this.inputTarget.value;
        console.log(url);
        return fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(response => {
            if (response.status === 200) {
                return response.json().then(data => {
                    console.log(data)
                    if (data.length >= 1) {
                        const person = data[0];
                        if(!this.hasExcludeIdValue || this.excludeIdValue !== person.id) {
                            fetch(`${this.errorUrlValue}?id=${person.id}`).then(response => {
                                if (response.status === 200) {
                                    return response.text();
                                }
                            }).then(html => {
                                $(this.inputTarget).next('.invalid-feedback').remove();
                                $(this.inputTarget).addClass('is-invalid').after(html);
                            }).catch(function (err) {
                                // There was an error
                                console.warn('Something went wrong.', err);
                            });
                            return false;
                        }
                    } else {
                        $(this.inputTarget).next('.invalid-feedback').remove();
                        $(this.inputTarget).removeClass('is-invalid');
                        return true;
                    }
                });
            }
        });
    }

    submitCheck(event){
        event.preventDefault();
        console.log(this.submitTarget.form)
        this.checkIfUnique()
            .then(isUnique=>{
                if(isUnique){
                    console.log('submitting form!');
                    this.submitTarget.form.submit();
                }
            })
    }

    connect(){
        console.log(this.hasExcludeIdValue, this.excludeIdValue)
    }
}