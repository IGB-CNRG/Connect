/*
 * Copyright (c) 2024 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import {Controller} from "@hotwired/stimulus";
import Routing from "fos-router";
import debounce from "debounce";

export default class extends Controller {
    static targets = ['query', 'resultList','result']
    static values = {
        'apiUrl': String,
    };

    initialize() {
        this.debouncedSearch = debounce(this.debouncedSearch.bind(this), 300)
    }

    debouncedSearch() {
        this.search();
    }

    search(){
        const query = this.queryTarget.value;
        if(query === ''){
            this.closeResults();
            this.resultListTarget.innerHTML = '';
            return;
        }
        const url = Routing.generate('person_searchresultsfragment', {'query':query});

        fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
            },
        })
            .then(response => {
                if(response.redirected){
                    // redirected somewhere else, probably to the login page. error out.
                    return '<div class="px-2">An error has occurred. Please try reloading the page.</div>';
                }
                return response.text()
            })
            .then(html => {
                this.resultListTarget.innerHTML = html;
                if(html === ''){
                    this.closeResults();
                } else {
                    this.openResults();
                }
            });
    }

    scrollUp(){
        const prev = this.#getSelectedResult().previousElementSibling;
        if(prev !== null) {
            this.#selectResult(prev);
        }
    }

    scrollDown(){
        const next = this.#getSelectedResult().nextElementSibling;
        if(next !== null){
            this.#selectResult(next);
        }
    }

    chooseResult(){
        this.#getSelectedResult().click();
    }

    select(event){
        this.#selectResult(event.target);
    }

    clickOutside(event){
        if(this.element === event.target || this.element.contains(event.target)){
            return;
        }

        this.closeResults();
    }

    openResults(){
        this.resultListTarget.classList.remove('d-none');
    }

    closeResults(){
        this.resultListTarget.classList.add('d-none');
    }

    tryReopen(){
        if(this.resultListTarget.innerHTML !== ''){
            this.openResults();
        }
    }

    #selectResult(element){
        for (const result of this.resultTargets) {
            if(element === result){
                result.classList.add('selected');
            } else {
                result.classList.remove('selected');
            }
        }
    }

    #getSelectedResult(){
        return this.element.querySelector('.selected');
    }
}