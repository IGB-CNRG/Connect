/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['select', 'other'];

    connect() {

    }

    toggle() {
        if ($(this.selectTarget).val() === "") {
            $(this.otherTarget).prop("disabled", false);
        } else {
            $(this.otherTarget).prop("disabled", true).val("");
        }
    }
}