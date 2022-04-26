/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import 'symfony-collection-js';

const $ = require("jquery");

export default class extends Controller {
    static targets = ['collection','otherAdd'];

    connect() {
        let options = {};
        if(this.hasOtherAddTarget){
            options = {
                other_btn_add: '#'+this.otherAddTarget.id,
            };
        }
        $(this.collectionTarget).formCollection(options);
    }
}