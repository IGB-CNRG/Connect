import './styles/app.scss';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

// start the Stimulus application
// import './bootstrap';
import '@fortawesome/fontawesome-free/js/fontawesome'
import '@fortawesome/fontawesome-free/js/solid'
import '@fortawesome/fontawesome-free/js/regular'
import 'bootstrap';

const $ = require('jquery');

// TODO is there a way to just import app.js here, getting access to its variables?

require('datatables.net');
require('datatables.net-bs5');
// require('datatables.net-responsive-bs5');
// require('datatables.net-responsive');

$('#people').DataTable();