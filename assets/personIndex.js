/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import './styles/app.scss';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import '@fortawesome/fontawesome-free/css/all.css';
import 'select2/dist/css/select2.css';

import 'select2/dist/js/select2';

const $ = require('jquery');
require('bootstrap');

// TODO is there a way to just import app.js here, getting access to its variables?
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

$('.connect-select2').select2();
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