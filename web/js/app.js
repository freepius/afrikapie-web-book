/*global $, document */

document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    /**
     * Activate the Bootstrap TOOLTIPS.
     */
    $('[data-title]:not([data-lightbox])').tooltip({
        container: 'body'
    });

});
