import './styles/app.scss';
import '@fortawesome/fontawesome-free/css/all.css';
import 'select2/dist/css/select2.css';

import $ from 'jquery';
import 'bootstrap';
import 'select2/dist/js/select2';
import 'symfony-collection-js';

$('.connect-select2').select2();
$('#person_keyAffiliations').formCollection({
    other_btn_add: '#keyAffiliation-add',
});
$('#person_themeAffiliations').formCollection({
    other_btn_add: '#themeAffiliation-add',
});
$('#person_supervisorAffiliations').formCollection({
    other_btn_add: '#supervisorAffiliation-add',
});
$('#person_superviseeAffiliations').formCollection({
    other_btn_add: '#superviseeAffiliation-add',
});
