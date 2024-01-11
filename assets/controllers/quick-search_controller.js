/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import TomSelect from "tom-select";
import 'tom-select/dist/css/tom-select.bootstrap5.css';
import Routing from "fos-router";

export default class extends Controller {

    connect() {
        const clickHandler = (value, item) => {
            window.location.assign(Routing.generate('person_view', {'slug':value}));
        }
        new TomSelect(this.element, {
            valueField: 'slug',
            labelField: 'name',
            render: {
                option: function(item, escape){
                    return '<div>'+
                        '<span class="quick-search-label">'+escape(item.name)+'</span>'+
                        '<span class="quick-search-caption">'+escape(item.email)+'</span>'+
                        '</div>';
                }
            },
            searchField: ['firstName', 'lastName', 'email'],
            sortField:[{field:'lastName'},{field:'firstName'},{field:'$score'}],
            maxOptions: 6,
            onItemAdd: clickHandler,
            load: function (query, callback) {
                const url = Routing.generate('_api_/people{._format}_get_collection', {'search': query});

                fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                    .then(response => response.json())
                    .then(json => {
                        console.log(json);
                        callback(json);
                    })
                    .catch(() => {
                        callback();
                    });
            },
        });
    }
}