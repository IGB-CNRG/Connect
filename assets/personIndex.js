/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

const $ = require('jquery');
require('datatables.net');
require('datatables.net-bs5');
// require('datatables.net-responsive-bs5');
// require('datatables.net-responsive');

function multiWordColumnSearchExact(column){
    return function () {
        const searchRegex = $(this).val().map(x=>'^'+$.fn.dataTable.util.escapeRegex(x)+'$').join('|');
        column.search(searchRegex, true, false).draw();
    }
}

function multiWordColumnSearch(column){
    return function () {
        const searchRegex = $(this).val().map(x=>$.fn.dataTable.util.escapeRegex(x)).join('|');
        column.search(searchRegex, true, false).draw();
    }
}

$('#people').DataTable({
    initComplete: function () {
        let column = this.api().column(2);
        $('#theme-select')
            .on('change', multiWordColumnSearchExact(column))
            .select2({
                placeholder: 'Filter by theme',
                width: 'style',
            });

        column = this.api().column(3);
        $('#theme-role-select')
            .on('change', multiWordColumnSearch(column))
            .select2({
                placeholder: 'Filter by role',
                width: 'style',
            })

        column = this.api().column(4);
        $('#member-category-select')
            .on('change', multiWordColumnSearchExact(column))
            .select2({
                placeholder: 'Filter by employee type',
                width: 'style',
            })
    }
});