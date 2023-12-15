/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

// todo convert usages of this controller to user-check
export default class extends Controller {
    static targets = ['username'];
    static values = {
        'url': String,
        'errorUrl': String,
    };

    checkIfUsernameUnique() {
        fetch(this.urlValue + '?username=' + this.usernameTarget.value, {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(response => {
            if (response.status === 200) {
                response.json().then(data => {
                    $(this.usernameTarget).next('.invalid-feedback').remove();
                    if (data.length === 1) {
                        const person = data[0];
                        // todo make sure the person we found isn't the same as the person we're editing
                        fetch(`${this.errorUrlValue}?id=${person.id}`).then(response=>{
                            if(response.status===200){
                                return response.text();
                            }
                        }).then(html => {
                            $(this.usernameTarget).addClass('is-invalid').after(html);
                        }).catch(function (err) {
                            // There was an error
                            console.warn('Something went wrong.', err);
                        });
                    } else {
                        $(this.usernameTarget).removeClass('is-invalid');
                    }
                });
            }
        });
    }
}