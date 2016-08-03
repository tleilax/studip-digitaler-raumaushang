/*jslint browser: true */
/*global jQuery */
(function ($) {
    'use strict';

    Date.replaceChars.longDays = [
        'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
    ];

    $(document).on('keypress', function (event) {
        if (event.which === 13) {
            location.reload();
        }
    });

    // Clock/date
    window.setInterval(function () {
        $('header > aside > time').text((new Date()).format('H:i'));
        $('header > aside > date').text((new Date()).format('l, d.m.Y'));
    }, 100);

}(jQuery));