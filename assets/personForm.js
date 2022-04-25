/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import $ from 'jquery';
import 'symfony-collection-js';

$('#person_keyAffiliations').formCollection({
    other_btn_add: '#keyAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});
$('#person_themeAffiliations').formCollection({
    other_btn_add: '#themeAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});
$('#person_roomAffiliations').formCollection({
    other_btn_add: '#roomAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});
$('#person_departmentAffiliations').formCollection({
    other_btn_add: '#departmentAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});
$('#person_supervisorAffiliations').formCollection({
    other_btn_add: '#supervisorAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});
$('#person_superviseeAffiliations').formCollection({
    other_btn_add: '#superviseeAffiliation-add',
    post_add: function(new_elem, context){
        $(new_elem).find('.connect-select2').select2();
    },
});