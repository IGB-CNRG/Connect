/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import 'symfony-collection-js';

const $ = require("jquery");

export default class extends Controller {
    static targets = ['collection', 'otherAdd'];
    static values = {
        confirmDeletion: {type: Boolean, default: true},
    };

    connect() {
        let options = {};
        if (this.hasOtherAddTarget) {
            options = {
                other_btn_add: '#' + this.otherAddTarget.id,
            };
        }
        $(this.collectionTarget).formCollection(options);
    }

    deleteRow(event) {
        event.preventDefault();
        event.stopPropagation();
        if (!this.confirmDeletionValue || confirm("Only delete entries if they were created by mistake. Otherwise, please set the end date appropriately.\n\nAre you sure you want to delete this entry? This cannot be undone."))
            $(event.target).closest('.collection-row').remove();
    }

    add(event) {
        event.preventDefault();
        event.stopPropagation();
        $(this.collectionTarget).formCollection('add');
    }
}