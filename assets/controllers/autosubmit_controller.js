/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus"
// import debounce from 'debounce'

export default class extends Controller {
    initialize() {
        // todo add this back in after we resolve debounce dependency issues (node>=18, 16 on server)
        // this.debouncedSubmit = debounce(this.debouncedSubmit.bind(this), 300)
    }

    submit(e) {
        this.element.requestSubmit()
    }

    debouncedSubmit() {
        this.submit()
    }
}