import Countdown from '@/js/countdown';
import base64 from 'base-64';

import { jQuery, moment, Raumaushang } from '@/js/bootstrap';

Object.assign(Raumaushang, {
    delays: {
        schedules: 30 * 1000, // 5 * 60 * 1000,
        pagination: 15 * 1000
    },
    currentPage: 0,
    schedules: null
});

Raumaushang.request = function (url, callback) {
    var request = new XMLHttpRequest();
    request.open(
        'GET',
        Raumaushang.api.url + url
    );
    request.setRequestHeader('Authorization', 'Basic ' + base64.encode(
        [Raumaushang.api.auth.username, Raumaushang.api.auth.password].join(':')
    ));
    request.addEventListener('load', function (event) {
        var version     = this.getResponseHeader('X-Plugin-Version'),
            server_time = this.getResponseHeader('X-Raumaushang-Timestamp');

        if (version && version !== Raumaushang.version) {
            location.reload();
        } else if (jQuery.isFunction(callback)) {
            Raumaushang.setMoment(server_time);
            try {
                callback(JSON.parse(request.responseText));
            } catch (e) {
            }
        }
    });
    request.send();
};

Raumaushang.requestSchedules = function () {
    Raumaushang.request('raumaushang/current-view/' + Raumaushang.current_id, function (json) {
        Raumaushang.schedules = json;
    });

    window.setTimeout(Raumaushang.requestSchedules, Raumaushang.delays.schedules);
};

Raumaushang.paginate = function () {
    if (Raumaushang.schedules === null) {
        return;
    }

    var totalPages = Math.ceil(Raumaushang.schedules.length / Raumaushang.maxPages),
        list = jQuery('');

    Raumaushang.currentPage = ((Raumaushang.currentPage + 1) % totalPages) || 0;

    jQuery('.courses .course').remove();

    Raumaushang.schedules.slice(
        Raumaushang.currentPage * Raumaushang.maxPages,
        (Raumaushang.currentPage + 1) * Raumaushang.maxPages
    ).forEach(function (schedule) {
        var item = jQuery('<li class="course">'),
            teachers = jQuery('<ul class="teachers">');

        jQuery('<div class="time">').text(
            [
                moment(schedule.begin).format('HH:mm'),
                moment(schedule.end).format('HH:mm')
            ].join(' - ')
        ).appendTo(item);
        jQuery('<div class="room">').text(schedule.room).appendTo(item);
        jQuery('<div class="title">').text(
            [schedule.code, schedule.name].join(' ').trim() || 'Keine Angaben'
        ).appendTo(item);
        schedule.teachers.forEach(function (teacher) {
            jQuery('<li>').text(teacher).appendTo(teachers);
        });
        teachers.appendTo(item);

        list = list.add(item);
    });

    jQuery('.courses').prepend(list);

    jQuery('footer .current-page').text(Raumaushang.currentPage + 1);
    jQuery('footer .total-pages').text(totalPages);
    jQuery('footer .next-page').text((Raumaushang.currentPage + 1) % totalPages + 1);
    jQuery('footer').toggle(totalPages > 1);

    Raumaushang.applyScrolling();
};

Raumaushang.applyScrolling = function () {
    jQuery('.course').find('.room,.teachers').each(function () {
        var height = jQuery(this).height(),
            actual = jQuery(this)[0].scrollHeight;

        if (height === actual) {
            return;
        }

        Raumaushang.scroll(this, actual - height);
    });
};

Raumaushang.scroll = function (element, value, revert) {
    setTimeout(function () {
        const prefix = revert ? '-=' : '+=';
        jQuery(element).animate({
            scrollTop: prefix + (value + 23)
        }, 2500, 'linear', () => {
            Raumaushang.scroll(element, value, !revert);
        });
    }, 2500);
};

jQuery(document).ready(function () {
    Raumaushang.requestSchedules();
    Raumaushang.applyScrolling();

    Countdown.add('pagination', Raumaushang.delays.pagination, Raumaushang.paginate, {
        check_interval: 100,
        interval: true,
        on_tick: function (diff) {
            var percent = Math.max(0, 100 - diff * 100 / this.options.duration);
            jQuery('footer > .progress').css('right', percent + '%');
        }
    });
});

// Clock/date
window.setInterval(function () {
    jQuery('header > aside > time').text(Raumaushang.getMoment().format('HH:mm'));
    jQuery('header > aside > date').text(Raumaushang.getMoment().format('dddd, DD.MM.YYYY'));
}, 100);
