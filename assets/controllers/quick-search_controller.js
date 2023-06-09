/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import TomSelect from "tom-select";
import 'tom-select/dist/css/tom-select.bootstrap5.css';
import Routing from "fos-router";

export default class extends Controller {
    static values = {
        'apiUrl': String,
    };
    connect() {
        const apiUrl = this.apiUrlValue;
        const clickHandler = (value, item) => {
            window.location.assign(Routing.generate('person_view', {'slug':value}));
        }
        new TomSelect(this.element, {
            valueField: 'slug',
            labelField: 'name',
            searchField: 'name',
            sortField:[{field:'lastName'},{field:'firstName'},{field:'$score'}],
            maxOptions: 6,
            onItemAdd: clickHandler,
            load: function (query, callback) {
                const url = `${apiUrl}?search=` + encodeURIComponent(query);

                fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            },
        });
    }
}