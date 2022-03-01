import './styles/app.scss';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import '@fortawesome/fontawesome-free/css/all.css';

const $ = require('jquery');
require('bootstrap');

// TODO is there a way to just import app.js here, getting access to its variables?
require('datatables.net');
require('datatables.net-bs5');
// require('datatables.net-responsive-bs5');
// require('datatables.net-responsive');

$('#people').DataTable();