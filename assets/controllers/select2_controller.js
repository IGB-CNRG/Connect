/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import 'select2/dist/js/select2';
import 'select2-bootstrap-5-theme/src/select2-bootstrap-5-theme.scss';

const $ = require("jquery");

export default class extends Controller {
    connect(){
        let options = {
            // width: 'style'
            theme: "bootstrap-5",
        };
        if(this.element.dataset.hasOwnProperty('placeholder')){
            options.placeholder = this.element.dataset.placeholder;
        }
        $(this.element).select2(options).on('select2:select select2:unselect', function(){
            let event = new Event('change', {bubbles: true}); // fire a native change event
            this.dispatchEvent(event);
        });
    }
}