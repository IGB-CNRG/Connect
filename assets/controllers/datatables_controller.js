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

        if (this.comboPatternValue.includes(event.currentTarget.id)) {
            this.comboSearch();
        }
    }

    comboSearch() {
        // Get the values for the combosearch
        let comboValues = [];
        this.comboPatternValue.forEach(inputId => comboValues.push($(`#${inputId}`).val()))
        // Combine them in every possible way
        let regexs = [];
        comboValues.forEach(function (values, index) {
            const separator = index === comboValues.length - 1 ? ';' : ',';
            let newRegexs = [];
            if (regexs.length === 0) {
                regexs = [''];
            }
            if(values.length === 0){
                regexs.forEach(regex =>newRegexs.push(`${regex}[^,]*${separator}`));
                regexs = newRegexs;
            } else {
                regexs.forEach(regex => values.forEach(value => newRegexs.push(regex + $.fn.dataTable.util.escapeRegex(value) + separator)));
                regexs = newRegexs;
            }
        });

        // Perform column search with joined regex
        const regex = regexs.join('|');
        this.dt.column(this.comboColumnValue).search(regex, true, false).draw();
    }
}