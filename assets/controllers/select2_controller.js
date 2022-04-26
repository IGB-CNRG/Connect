/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import 'select2/dist/js/select2';

const $ = require("jquery");

export default class extends Controller {
    connect(){
        console.log('select2 initializing');
        $(this.element).select2().on('select2:select', function(){
            let event = new Event('change', {bubbles: true}); // fire a native change event
            this.dispatchEvent(event);
        });
    }
}