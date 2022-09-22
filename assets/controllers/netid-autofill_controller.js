/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['username', 'email'];

    connect() {
        console.log(this.usernameTarget, this.emailTarget);
    }

    updateFields(event){
        this.usernameTarget.value = event.target.value;
        this.emailTarget.value = event.target.value + '@illinois.edu';
    }
}