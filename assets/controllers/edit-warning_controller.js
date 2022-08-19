/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    warn(){
        // todo I don't love this functionality. Is there a better way?
        alert("Please do not edit historical records unless correcting a mistake. All edits will be logged.");
    }
}