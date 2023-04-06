/*
 * Copyright (c) 2023 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import Routing from "fos-router";

const $ = require("jquery");

export default class extends Controller {
    static targets = ['username'];
    static values = {
        'url': String,
    };

    checkIfUsernameUnique(){
        const usernameTarget = this.usernameTarget;
        $.ajax(this.urlValue+'?username='+this.usernameTarget.value, {
            'success': function(data){
                if(data.id>0) {
                    const personUrl = Routing.generate('person_view', {'slug': data.slug});
                    $(usernameTarget).addClass('is-invalid').after('<div class="invalid-feedback">A user with this username already exists: <a href="' + personUrl + '">' + data.name + '</a></div>');
                } else {
                    $(usernameTarget).removeClass('is-invalid').next('.invalid-feedback').remove();
                }
            }
        });
    }
}