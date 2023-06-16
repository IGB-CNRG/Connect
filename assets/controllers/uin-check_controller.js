/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import Routing from "fos-router";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['uin'];
    static values = {
        'url': String,
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
                        const personUrl = Routing.generate('person_view', {'slug': person.slug});
                        $(this.uinTarget).addClass('is-invalid').after('<div class="invalid-feedback">A user with this UIN already exists: <a href="' + personUrl + '">' + person.name + '</a></div>');
                    } else {
                        $(this.uinTarget).removeClass('is-invalid');
                    }
                });
            }
        });
    }
}