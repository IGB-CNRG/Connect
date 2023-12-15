/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

// todo convert usages of this controller to user-check
export default class extends Controller {
    static targets = ['uin'];
    static values = {
        'url': String,
        'errorUrl': String,
    };

    checkIfUinUnique() {
        fetch(this.urlValue + '?uin=' + this.uinTarget.value, {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        }).then(response => {
            if (response.status === 200) {
                response.json().then(data => {
                    $(this.uinTarget).next('.invalid-feedback').remove();
                    if (data.length === 1) {
                        const person = data[0];
                        // todo make sure the person we found isn't the same as the person we're editing
                        fetch(`${this.errorUrlValue}?id=${person.id}`).then(response=>{
                            if(response.status===200){
                                return response.text();
                            }
                        }).then(html => {
                            $(this.uinTarget).addClass('is-invalid').after(html);
                        }).catch(function (err) {
                            // There was an error
                            console.warn('Something went wrong.', err);
                        });
                    } else {
                        $(this.uinTarget).removeClass('is-invalid');
                    }
                });
            }
        });
    }
}