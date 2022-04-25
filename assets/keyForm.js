/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */


import $ from 'jquery';
import 'symfony-collection-js';

$('#keys_keyAffiliations').formCollection({
    other_btn_add: '#keyAffiliation-add',
    post_add: function(new_elem, context){
        console.log(new_elem);
        $(new_elem).find('.connect-select2').select2();
    },
});
