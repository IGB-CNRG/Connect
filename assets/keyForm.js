/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import './styles/app.scss';
import '@fortawesome/fontawesome-free/css/all.css';
import 'select2/dist/css/select2.css';

import $ from 'jquery';
import 'bootstrap';
import 'select2/dist/js/select2';
import 'symfony-collection-js';

$('#keys_keyAffiliations').formCollection({
    other_btn_add: '#keyAffiliation-add',
    post_add: function(new_elem, context){
        console.log(new_elem);
        $(new_elem).find('.connect-select2').select2();
    },
});
