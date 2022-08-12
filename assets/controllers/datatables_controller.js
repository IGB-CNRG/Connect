/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

import {Controller} from "@hotwired/stimulus";
import $ from 'jquery';

require('datatables.net');
require('datatables.net-bs5');

export default class extends Controller {
    static targets = ['table'];
    static values = {
        comboColumn: String,
        comboPattern: Array
    }

    connect() {
        this.dt = $(this.tableTarget).DataTable();
    }

    columnSearch() {
        const searchRegex = $(event.currentTarget).val().map(x => $.fn.dataTable.util.escapeRegex(x)).join('|');
        const column = this.dt.column(event.currentTarget.dataset.column);
        column.search(searchRegex, true, false).draw();

        if (this.comboPatternValue.length > 0 && this.comboPatternValue.includes(event.currentTarget.id)) {
            this.comboSearch();
        }
    }

    comboSearch() {
        // Get the values for the combo search
        let comboValues = [];
        this.comboPatternValue.forEach(inputId => comboValues.push($(`#${inputId}`).val()))
        // Combine them in every possible way
        let regexes = [];
        comboValues.forEach(function (values, index) {
            const separator = index === comboValues.length - 1 ? ';' : ',';
            let newRegexes = [];
            if (regexes.length === 0) {
                regexes = [''];
            }
            if (values.length === 0) {
                regexes.forEach(regex => newRegexes.push(`${regex}[^,]*${separator}`));
                regexes = newRegexes;
            } else {
                regexes.forEach(regex => values.forEach(value => newRegexes.push(regex + $.fn.dataTable.util.escapeRegex(value) + separator)));
                regexes = newRegexes;
            }
        });

        // Perform column search with joined regex
        const regex = regexes.join('|');
        this.dt.column(this.comboColumnValue).search(regex, true, false).draw();
    }
}