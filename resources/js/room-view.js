import Countdown from '@/js/countdown';
import Qrcode from 'qrcode_js';
import Mustache from 'mustache';
import { jQuery, moment, Raumaushang } from '@/js/bootstrap';
// import '../../node_modules/jquery.event.swipe/js/jquery.event.swipe.js';

function debuglog() {
    if (window.hasOwnProperty('console')) {
        window.console.log.apply(window, arguments);
    }
}

if (!window.hasOwnProperty('module')) {
    window.module = {};
}

// Allow :active style to work
// @see https://css-tricks.com/snippets/css/remove-gray-highlight-when-tapping-links-in-mobile-safari/
document.addEventListener('touchstart', () => null, true);

Object.assign(Raumaushang, {
    loadingOverlayTimeout: null,
    showLoadingOverlay(immediately) {
        clearTimeout(Raumaushang.loadingOverlayTimeout);
        if (immediately) {
            jQuery('#loading-overlay').show();
        } else {
            Raumaushang.loadingOverlayTimeout = window.setTimeout(() => {
                jQuery('#loading-overlay').show();
            }, 300);
        }
    },
    hideLoadingOverlay() {
        clearTimeout(Raumaushang.loadingOverlayTimeout);
        jQuery('#loading-overlay').hide();
    },

    max: {teachers: 3},
    DIRECTION_NEXT: '>',
    DIRECTION_PREVIOUS: '<',
    schedule_hash: null,
    course_data: {},
    current: {
        timestamp: moment(
            jQuery('meta[name="current-timestamp"]').attr('content')
        )
    },
    initial: {
        timestamp: moment(
            jQuery('meta[name="current-timestamp"]').attr('content')
        )
    },
    durations: {
        reload: 5 * 60 * 1000,
        course: 30 * 1000,
        help: 30 * 1000,
        opencast: 30 * 1000,
        return_to_current: 15 * 1000,
        overlay_default: 30 * 1000
    }
});

//
function showOverlay(selector, duration) {
    var hide = function (event) {
        if (event && jQuery(event.target).closest('.qrcode').length > 0) {
            return;
        }

        jQuery(selector).off('.overlay', hide).hide();
        Countdown.stop(selector);
        Countdown.start('main', true);

        if (event !== undefined) {
            event.preventDefault();
            event.stopPropagation();
        }
    };

    Raumaushang.hideLoadingOverlay();
    Countdown.stop('main');

    jQuery(selector).on('click.overlay', hide).show();
    Countdown.add(selector, duration || Raumaushang.durations.overlay_default, hide);
}

//
var templates = {};
function render(template_id, data) {
    if (!templates.hasOwnProperty(template_id)) {
        templates[template_id] = jQuery(template_id).html();
        Mustache.parse(templates[template_id]);
    }
    return Mustache.render(templates[template_id] || '', data);
}

// Initialize countdowns
Countdown.add('main', Raumaushang.durations.reload, function () {
    Raumaushang.update();
}, {
    on_tick: function (diff) {
        var element = jQuery('body > progress');
        if (element.length > 0) {
            element.attr('value', 100 - diff * 100 / this.options.duration);
        }
    }
});

Raumaushang.init = function () {
    Raumaushang.offsets = {
        fraction: null
    };

    var table         = jQuery('.week-schedule[data-resource-id]').first(),
        window_width  = jQuery('body').outerWidth(true),
        window_height = jQuery('body').outerHeight(true);

    jQuery('tbody tr', table).each(function () {
        var slot = jQuery(this).data().slot;
        jQuery('thead th[data-day]', table).each(function () {
            var day  = jQuery(this).data().day,
                td   = jQuery('tr[data-slot="' + slot + '"] td[data-day="' + day + '"]'),
                box = td[0].getBoundingClientRect();
            if (Raumaushang.offsets[day] === undefined) {
                Raumaushang.offsets[day] = {
                    left: box.left - 1,
                    right: window_width - box.right,
                    offsets: {}
                };
            }
            Raumaushang.offsets[day].offsets[slot] = {
                top: box.top - 1,
                bottom: window_height - box.bottom,
                height: box.height
            };
        });
    });
};

// Define request function
Raumaushang.request = function (url, data, callback) {
    Raumaushang.showLoadingOverlay();

    return jQuery.ajax({
        type: 'GET',
        url: Raumaushang.api.url + url,
        data: data || {},
        dataType: 'json',
        username: Raumaushang.api.auth.username,
        password: Raumaushang.api.auth.password
    }).then(function (json, status, jqxhr) {
        var schedule_hash = jqxhr.getResponseHeader('X-Schedule-Hash'),
            version       = jqxhr.getResponseHeader('X-Plugin-Version'),
            server_time   = jqxhr.getResponseHeader('X-Raumaushang-Timestamp');

        Raumaushang.setMoment(server_time);

        if (version && version !== Raumaushang.version) {
            location.reload();
            return;
        }

        if (jQuery.isFunction(callback)) {
            callback(schedule_hash, json);
        }
    }).fail(function (jqxhr, text, error) {
        debuglog('ajax failed', text, error, url);
    }).always(function () {
        Raumaushang.hideLoadingOverlay();
    });
};

// Highlights cells
Raumaushang.highlight = function () {
    var now  = Raumaushang.getMoment(),
        day  = now.format('d'),
        slot = window.parseInt(now.format('H'), 10);

    jQuery('body > .schedule-item').removeClass('current-slot');

    if (Raumaushang.current.timestamp.format('W') === now.format('W')) {
        jQuery('body > .schedule-item[data-slot~="' + slot + '"][data-day="' + day + '"]:not(.is-holiday)').addClass('current-slot');
    }

    window.setTimeout(Raumaushang.highlight, 250);
};

//
Raumaushang.getSpanningSlots = function (item) {
    var slots    = [],
        slot     = item.slot,
        end_slot = item.slot + (item.fraction + item.duration) / 4;
    while (slot < end_slot) {
        slots.push(slot);
        slot += 1;
    }
    return slots.join(' ');
};

Raumaushang.adjustItemSlotAndDuration = function (item, offset) {
    var slots = Object.keys(offset.offsets),
        min   = Math.min.apply(null, slots);

    // Lower bound not exceeded (this includes upper bound exceeded)
    // or lower bound exceedance not fixable => return original
    if (item.slot >= min || item.slot + item.duration / 4 <= min) {
        return item;
    }

    item.duration -= (min - item.slot) * 4;
    item.slot      = min;

    return item;
};

//
Raumaushang.createItemOffsets = function (item, offset) {
    item = Raumaushang.adjustItemSlotAndDuration(item, offset);

    if (!offset.offsets.hasOwnProperty(item.slot)) {
        debuglog('invalid offset', item, offset);
        return false;
    }

    var top_offset = offset.offsets[item.slot],
        bottom_slot = item.slot + (item.fraction + item.duration) / 4,
        bottom_offset;

    if (bottom_slot === Math.floor(bottom_slot)) {
        bottom_slot -= 1;
    }

    if (offset.offsets.hasOwnProperty(Math.floor(bottom_slot))) {
        bottom_offset = offset.offsets[Math.floor(bottom_slot)];
    } else {
        bottom_offset = offset.offsets[21]; // TODO: max should be configurable
    }

    return {
        left: offset.left,
        right: offset.right,
        top: Math.round(top_offset.top + item.fraction * top_offset.height / 4),
        bottom: bottom_offset.bottom + (bottom_slot % 1 > 0 ? 1 - bottom_slot % 1 : 0) * bottom_offset.height
    };
};

// Updates the table (internally requests data)
Raumaushang.update = function (direction, callback) {
    if (arguments.length === 1 && jQuery.isFunction(direction)) {
        callback  = direction;
        direction = null;
    }

    Countdown.stop('main');

    var table       = jQuery('.week-schedule[data-resource-id]').first(),
        resource_id = table.data().resourceId,
        from = null,
        timestamp,
        probe;

    if (Raumaushang.current.timestamp) {
        timestamp = Raumaushang.current.timestamp.clone();
        if (arguments.length === 0 && moment().isAfter(timestamp, 'week')) {
            timestamp = moment().startOf('week');
        }

        if (direction === Raumaushang.DIRECTION_NEXT) {
            timestamp.add(1, 'weeks');
        } else if (direction === Raumaushang.DIRECTION_PREVIOUS) {
            timestamp.subtract(1, 'weeks');
        }

        probe = window.parseInt(timestamp.format('H'), 10);
        if (probe > 20) {
            timestamp.add(24 - probe, 'hours');
        } else if (probe > 0) {
            timestamp.subtract(probe, 'hours');
        }

        from = timestamp.format('X');
    }

    Raumaushang.request(`/raumaushang/room-view/${resource_id}`, {
        from
    }, function (schedule_hash, json) {
        var first = null,
            last  = null,
            text,
            cells = jQuery();
        if (schedule_hash !== Raumaushang.schedule_hash) {
            Raumaushang.schedule_hash = schedule_hash;

            jQuery('.schedule-item').remove();

            jQuery.each(json, function (day, day_data) {
                var str = moment(day_data.timestamp).format('DD.MM.');

                day = parseInt(day, 10);

                if (jQuery('th[data-day="' + day + '"]', table).length === 0) {
                    return;
                }

                jQuery('th[data-day="' + day + '"] date', table).text(str);

                if (day === 1) {
                    Raumaushang.current.timestamp = moment(day_data.timestamp);
                }
                if (first === null) {
                    first = moment(day_data.timestamp);
                }
                last = moment(day_data.timestamp);

                jQuery.each(day_data.slots, function (index, item) {
                    Raumaushang.course_data[item.id] = item;

                    var offset = Raumaushang.offsets[day],
                        html = render('#schedule-item-template', jQuery.extend({}, item || {}, {
                            day: day,
                            slots: Raumaushang.getSpanningSlots(item), // TODO: All spanning slots
                            hasTeachers: item.teachers.length > 0,
                            teachers: item.teachers.length > Raumaushang.max.teachers
                                ? [{nachname: (item.teachers.length.toString() + ' Lehrende')}]
                                : item.teachers
                        })),
                        styles = Raumaushang.createItemOffsets(item, offset),
                        cell;
                    if (styles !== false) {
                        cell = jQuery(html).css(styles).attr('data-foo', styles.min_diff);
                        cells = cells.add(cell);
                    }
                });
            });

            jQuery('body').append(cells);
            jQuery(cells).filter(':not([data-duration="1"],[data-duration="2"])').find('.name').clamp();

            // Update week display

            jQuery('body > header .yearweek').text('KW ' + first.format('W/YYYY'));
        }
        Countdown.start('main', true);

        if (jQuery.isFunction(callback)) {
            callback();
        }
    });
};

// Handlers
jQuery(window).on('load', function () {
    Raumaushang.init();

/*
    jQuery('#loading-overlay').hide();
    Countdown.stop();
    return;
*/

    // Initialize schedule table
    Raumaushang.update(function () {
        Raumaushang.highlight();
    });
});
jQuery(document).on('select', '*', function (event) {
    event.preventDefault();
}).on('mousemove mousedown mouseup touchmove touchstart touchend', function (event) {
    Countdown.reset();
}).on('click', '.course-info', function () {
    Raumaushang.showLoadingOverlay(true);

    var course_id = jQuery(this).blur().data().courseId,
        data      = Raumaushang.course_data[course_id],
        day       = jQuery(this).data().day,
        slot      = jQuery(this).data().slot;

    jQuery('#course-overlay').html(render('#course-template', jQuery.extend({}, data, {
        begin: moment(data.begin).format('DD.MM.YYYY HH:mm'),
        end: moment(data.end).format('DD.MM.YYYY HH:mm'),
        hasTeachers: data.teachers.length > 0,
        hasModules: data.modules.length > 0
    }))).toggle(data.course_id !== null);

    showOverlay('#course-overlay', Raumaushang.durations.course);

    setTimeout(function () {
        jQuery('#course-overlay').find('.qrcode').makeQRCode();
    }, 0);

    jQuery('#course-overlay article').on('movestart', function (event) {
        if (jQuery('.qrcode.enlarged', this).length === 0) {
            event.preventDefault();
        }
    }).scrollTop(0);

    jQuery.extend(Raumaushang.current, {
        slot: slot,
        day: day
    });

    return false;
}).on('click', '.qrcode', function () {
    jQuery(this).toggleClass('enlarged');

    return false;
}).on('click', '#help-overlay-switch', function () {
    showOverlay('#help-overlay', Raumaushang.durations.help);
}).on('click', '#opencast-overlay-switch', function () {
    showOverlay('#opencast-overlay', Raumaushang.durations.opencast);
});

// Swipe actions
const container = document.querySelector('body');
const threshold = {
    distance: 50,
    time: null,
};

let touchStart = {x: null, y: null, time : null};
let touchEnd = {x: null, y: null, time: null};
container.addEventListener('touchstart', event => {
    touchStart.x = event.changedTouches[0].screenX;
    touchStart.y = event.changedTouches[0].screenY;
    touchStart.time = +(new Date);
});
container.addEventListener('touchend', event => {
    touchEnd.x = event.changedTouches[0].screenX;
    touchEnd.y = event.changedTouches[0].screenY;
    touchEnd.time = +(new Date);

    if (
        Math.abs(touchEnd.x - touchStart.x) > threshold.distance
        && touchEnd.time - touchStart.time > threshold.time
    ) {
        if (touchEnd.x > touchStart.x) {
            jQuery('header nav .next-week').click();
        } else {
            jQuery('header nav .previous-week').click();
        }
    }
});

jQuery('body').on('click', 'header nav a', function () {
    const direction = jQuery(this).hasClass('previous-week')
                  ? Raumaushang.DIRECTION_PREVIOUS
                  : Raumaushang.DIRECTION_NEXT;
    Raumaushang.update(direction);

    Countdown.add('return-to-current', Raumaushang.durations.return_to_current, () => {
        Raumaushang.current.timestamp = Raumaushang.initial.timestamp;
        Raumaushang.schedule_hash = null;
        Raumaushang.update();
    });

    return false;
});

// Clock
window.setInterval(function () {
    jQuery('#clock').text(Raumaushang.getMoment().format('HH:mm'));
}, 100);

// Make QR Code
jQuery.fn.extend({
    makeQRCode: function () {
        return this.each(function () {
            var course_id = jQuery(this).data().courseId,
                template  = jQuery('meta[name="course-url-template"]').attr('content');
            new Qrcode(this, {
                text: template.replace('#{course_id}', course_id),
                width: 1024,
                height: 1024,
                correctLevel: Qrcode.CorrectLevel.H
            });
        });
    }
});
