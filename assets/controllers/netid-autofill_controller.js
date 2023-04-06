/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['username', 'email'];

    updateFields(event){
        if(this.hasUsernameTarget) {
            this.usernameTarget.value = event.target.value;
        }
        if(this.hasEmailTarget) {
            this.emailTarget.value = event.target.value + '@illinois.edu';
        }
    }
}