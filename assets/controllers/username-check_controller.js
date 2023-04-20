/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import Routing from "fos-router";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['username'];
    static values = {
        'url': String,
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
                        const personUrl = Routing.generate('person_view', {'slug': person.slug});
                        $(this.usernameTarget).addClass('is-invalid').after('<div class="invalid-feedback">A user with this username already exists: <a href="' + personUrl + '">' + person.name + '</a></div>');
                    } else {
                        $(this.usernameTarget).removeClass('is-invalid');
                    }
                });
            }
        });
    }
}