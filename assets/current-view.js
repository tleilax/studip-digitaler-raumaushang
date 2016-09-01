/*jslint browser: true, unparam: true */
/*global jQuery, Raumaushang, Countdown, Base64 */
(function ($, Raumaushang, Countdown, Base64) {
    'use strict';

    Date.replaceChars.longDays = [
        'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'
    ];

    $.extend(Raumaushang, {
        delays: {
            schedules: 5 * 60 * 1000,
            pagination: 15 * 1000
        },
        currentPage: 0,
        schedules: null
    });

    Raumaushang.request = function (url, callback) {
        var request = new XMLHttpRequest();
        request.open(
            'GET',
            Raumaushang.api.url + url,
            false
        );
        request.setRequestHeader('Authorization', 'Basic ' + Base64.encode(
            [Raumaushang.api.auth.username, Raumaushang.api.auth.password].join(':')
        ));
        request.addEventListener('load', function (event) {
            var version = this.getResponseHeader('X-Plugin-Version');
            if (version && version !== Raumaushang.version) {
                location.reload();
            } else if ($.isFunction(callback)) {
                try {
                    callback(JSON.parse(request.responseText));
                } catch (e) {
                }
            }
        });
        request.send();
    };

    Raumaushang.requestSchedules = function () {
        Raumaushang.request('raumaushang/currentschedule/' + Raumaushang.current_id, function (json) {
            Raumaushang.schedules = json;
        });

        window.setTimeout(Raumaushang.requestSchedules, Raumaushang.delays.schedules);
    };

    Raumaushang.paginate = function () {
        if (Raumaushang.schedules === null) {
            return;
        }

        var totalPages = Math.ceil(Raumaushang.schedules.length / Raumaushang.maxPages),
            list = $('');

        Raumaushang.currentPage = ((Raumaushang.currentPage + 1) % totalPages) || 0;

        $('.courses .course').remove();

        Raumaushang.schedules.slice(
            Raumaushang.currentPage * Raumaushang.maxPages,
            (Raumaushang.currentPage + 1) * Raumaushang.maxPages
        ).forEach(function (schedule) {
            var item = $('<li class="course">'),
                teachers = $('<ul class="teachers">');

            $('<span class="time">').text(
                [
                    (new Date(schedule.begin * 1000)).format('H:i'),
                    (new Date(schedule.end * 1000)).format('H:i')
                ].join(' - ')
            ).appendTo(item);
            $('<span class="room">').text(schedule.room).appendTo(item);
            $('<span class="title">').text(
                [schedule.code, schedule.name].join(' ').trim()
            ).appendTo(item);
            schedule.teachers.forEach(function (teacher) {
                $('<li>').text(teacher).appendTo(teachers);
            });
            teachers.appendTo(item);

            list = list.add(item);
        });

        $('.courses').prepend(list);

        $('footer .current-page').text(Raumaushang.currentPage + 1);
        $('footer .total-pages').text(totalPages);
        $('footer .next-page').text((Raumaushang.currentPage + 1) % totalPages + 1);
        $('footer').toggle(totalPages > 1);
    };

    $(document).ready(function () {
        Raumaushang.requestSchedules();

        Countdown.add('pagination', Raumaushang.delays.pagination, Raumaushang.paginate, {
            check_interval: 100,
            interval: true,
            on_tick: function (diff) {
                var percent = Math.max(0, 100 - diff * 100 / this.options.duration);
                $('footer > .progress').css('right', percent + '%');
            }
        });
    });

    // Clock/date
    window.setInterval(function () {
        $('header > aside > time').text((new Date()).format('H:i'));
        $('header > aside > date').text((new Date()).format('l, d.m.Y'));
    }, 100);

}(jQuery, Raumaushang, Countdown, Base64));