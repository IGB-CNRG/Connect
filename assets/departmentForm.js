/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

import $ from "jquery";

$('body').on('change', '.departmentSelect', function(){
    const $this = $(this);
    const otherInput = $this.closest('.departmentInputs').find('.otherDepartmentInput');
    if($this.val() == ""){
        otherInput.prop("disabled", false);
    } else {
        otherInput.prop("disabled", true).val("");
    }
});