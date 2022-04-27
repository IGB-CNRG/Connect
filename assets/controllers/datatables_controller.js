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

    connect() {
        this.dt = $(this.tableTarget).DataTable();
    }

    columnSearch() {
        const searchRegex = $(event.currentTarget).val().map(x=>$.fn.dataTable.util.escapeRegex(x)).join('|');
        const column = this.dt.column(event.currentTarget.dataset.column);
        column.search(searchRegex, true, false).draw();
    }
}