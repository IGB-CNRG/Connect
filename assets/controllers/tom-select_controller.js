/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from '@hotwired/stimulus';
import TomSelect from "tom-select";
import 'tom-select/dist/css/tom-select.bootstrap5.css';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect(){
        new TomSelect(this.element, {});
    }
}
