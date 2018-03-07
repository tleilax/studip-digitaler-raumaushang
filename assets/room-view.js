/*jslint browser: true, unparam: true, todo: true */
/*global jQuery, Raumaushang, Countdown, Mustache, moment, QRCode */
(function ($, Raumaushang, Countdown, Mustache, moment, QRCode) {
    'use strict';

    function debuglog() {
        if (window.hasOwnProperty('console')) {
            window.console.log.apply(window, arguments);
        }
    }

    // Allow :active style to work
    // @see https://css-tricks.com/snippets/css/remove-gray-highlight-when-tapping-links-in-mobile-safari/
    document.addEventListener('touchstart', function () { return; }, true);

    Raumaushang.loadingOverlayTimeout = null;
    Raumaushang.showLoadingOverlay = function (immediately) {
        clearTimeout(Raumaushang.loadingOverlayTimeout);
        if (immediately) {
            $('#loading-overlay').show();
        } else {
            Raumaushang.loadingOverlayTimeout = window.setTimeout(function () {
                $('#loading-overlay').show();
            }, 300);
        }
    };
    Raumaushang.hideLoadingOverlay = function () {
        clearTimeout(Raumaushang.loadingOverlayTimeout);
        $('#loading-overlay').hide();
    };

    // Initialize variables
    $.extend(Raumaushang, {
        max: {teachers: 3},
        DIRECTION_NEXT: '>',
        DIRECTION_PREVIOUS: '<',
        schedule_hash: null,
        course_data: {},
        current: {
            timestamp: moment(
                $('meta[name="current-timestamp"]').attr('content')
            )
        },
        initial: {
            timestamp: moment(
                $('meta[name="current-timestamp"]').attr('content')
            )
        },
        durations: {
            reload: 5 * 60 * 1000,
            course: 30 * 1000,
            help: 30 * 1000,
            return_to_current: 15 * 1000,
            overlay_default: 30 * 1000
        }
    });

    //
    function showOverlay(selector, duration) {
        var hide = function (event) {
            if (event && $(event.target).closest('.qrcode').length > 0) {
                return;
            }

            $(selector).off('.overlay', hide).hide();
            Countdown.stop(selector);
            Countdown.start('main', true);

            if (event !== undefined) {
                event.preventDefault();
                event.stopPropagation();
            }
        };

        Raumaushang.hideLoadingOverlay();
        Countdown.stop('main');

        $(selector).on('click.overlay', hide).show();
        Countdown.add(selector, duration || Raumaushang.durations.overlay_default, hide);
    }

    //
    var templates = {};
    function render(template_id, data) {
        if (!templates.hasOwnProperty(template_id)) {
            templates[template_id] = $(template_id).html();
            Mustache.parse(templates[template_id]);
        }
        return Mustache.render(templates[template_id] || '', data);
    }

    // Initialize countdowns
    Countdown.add('main', Raumaushang.durations.reload, function () {
        Raumaushang.update();
    }, {
        on_tick: function (diff) {
            var element = $('body > progress');
            if (element.length > 0) {
                element.attr('value', 100 - diff * 100 / this.options.duration);
            }
        }
    });

    Raumaushang.init = function () {
        Raumaushang.offsets = {
            fraction: null
        };

        var table         = $('.week-schedule[data-resource-id]').first(),
            window_width  = $('body').outerWidth(true),
            window_height = $('body').outerHeight(true);

        $('tbody tr', table).each(function () {
            var slot = $(this).data().slot;
            $('thead th[data-day]', table).each(function () {
                var day  = $(this).data().day,
                    td   = $('tr[data-slot="' + slot + '"] td[data-day="' + day + '"]'),
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
    var requests = {};
    Raumaushang.request = function (url, data, callback, forced) {
        if (!forced && requests.hasOwnProperty(url)) {
            var cached = requests[url];
            if (this.getMoment().isSame(cached.timestamp, 'day')) {
                return callback(cached.hash, cached.data);
            }
        }

        Raumaushang.showLoadingOverlay();

        return $.ajax({
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

            requests[url] = {
                timestamp: Raumaushang.now,
                hash: schedule_hash,
                data: json
            };

            if ($.isFunction(callback)) {
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

        $('body > .schedule-item').removeClass('current-slot');

        if (Raumaushang.current.timestamp.format('W') === now.format('W')) {
            $('body > .schedule-item[data-slot~="' + slot + '"][data-day="' + day + '"]:not(.is-holiday)').addClass('current-slot');
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
        if (arguments.length === 1 && $.isFunction(direction)) {
            callback  = direction;
            direction = null;
        }

        Countdown.stop('main');

        var table       = $('.week-schedule[data-resource-id]').first(),
            resource_id = table.data().resourceId,
            chunks      = ['/raumaushang/schedule', resource_id],
            forced      = false,
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
            } else {
                forced = true;
            }

            probe = window.parseInt(timestamp.format('H'), 10);
            if (probe > 20) {
                timestamp.add(24 - probe, 'hours');
            } else if (probe > 0) {
                timestamp.subtract(probe, 'hours');
            }

            chunks.push(timestamp.format('X'));
        }

        Raumaushang.request(chunks.join('/'), {}, function (schedule_hash, json) {
            var first = null,
                last  = null,
                text,
                cells = $();
            if (schedule_hash !== Raumaushang.schedule_hash) {
                Raumaushang.schedule_hash = schedule_hash;

                $('.schedule-item').remove();

                $.each(json, function (day, day_data) {
                    var str = moment(day_data.timestamp).format('DD.MM.');

                    day = parseInt(day, 10);

                    if ($('th[data-day="' + day + '"]', table).length === 0) {
                        return;
                    }

                    $('th[data-day="' + day + '"] date', table).text(str);

                    if (day === 1) {
                        Raumaushang.current.timestamp = moment(day_data.timestamp);
                    }
                    if (first === null) {
                        first = moment(day_data.timestamp);
                    }
                    last = moment(day_data.timestamp);

                    $.each(day_data.slots, function (index, item) {
                        Raumaushang.course_data[item.id] = item;

                        var offset = Raumaushang.offsets[day],
                            html = render('#schedule-item-template', $.extend({}, item || {}, {
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
                            cell = $(html).css(styles).attr('data-foo', styles.min_diff);
                            cells = cells.add(cell);
                        }
                    });
                });

                $('body').append(cells);
                $(cells).filter(':not([data-duration="1"],[data-duration="2"])').find('.name').clamp();

                // Update week display
                text  = 'Kalenderwoche <strong>' + first.format('W/YYYY') + '</strong>';
                text += ' vom <strong>' + first.format('DD.MM.') + '</strong>';
                text += ' bis <strong>' + last.format('DD.MM.') + '</strong>';

                $('body > header small').html(text);
            }
            Countdown.start('main', true);

            if ($.isFunction(callback)) {
                callback();
            }
        }, forced);
    };

    // Handlers
    $(window).on('load', function () {
        Raumaushang.init();

/*
        $('#loading-overlay').hide();
        Countdown.stop();
        return;
*/

        // Initialize schedule table
        Raumaushang.update(function () {
            Raumaushang.highlight();
        });
    });
    $(document).on('select', '*', function (event) {
        event.preventDefault();
    }).on('mousemove mousedown mouseup touchmove touchstart touchend', function (event) {
        Countdown.reset();
    }).on('click', '.course-info', function () {
        Raumaushang.showLoadingOverlay(true);

        var course_id = $(this).blur().data().courseId,
            data      = Raumaushang.course_data[course_id],
            day       = $(this).data().day,
            slot      = $(this).data().slot;

        $('#course-overlay').html(render('#course-template', $.extend({}, data, {
            begin: moment(data.begin).format('DD.MM.YYYY HH:mm'),
            end: moment(data.end).format('DD.MM.YYYY HH:mm'),
            hasTeachers: data.teachers.length > 0,
            hasModules: data.modules.length > 0
        }))).toggle(data.course_id !== null);

        showOverlay('#course-overlay', Raumaushang.durations.course);

        setTimeout(function () {
            $('#course-overlay').find('.qrcode').makeQRCode();
        }, 0);

        $('#course-overlay article').on('movestart', function (event) {
            if ($('.qrcode.enlarged', this).length === 0) {
                event.preventDefault();
            }
        }).scrollTop(0);

        $.extend(Raumaushang.current, {
            slot: slot,
            day: day
        });

        return false;
    }).on('click', '.qrcode', function () {
        $(this).toggleClass('enlarged');

        return false;
    }).on('click', '#help-overlay-switch', function () {
        showOverlay('#help-overlay', Raumaushang.durations.help);
    });

    // Swipe actions
    $('body').on('swipeleft swiperight', function (event) {
        if ($('#course-overlay,#help-overlay').is(':visible')) {
            return;
        }

        var direction = event.type === 'swiperight'
                      ? Raumaushang.DIRECTION_PREVIOUS
                      : Raumaushang.DIRECTION_NEXT;
        Raumaushang.update(direction);

        Countdown.add('return-to-current', Raumaushang.durations.return_to_current, function () {
            Raumaushang.current.timestamp = Raumaushang.initial.timestamp;
            Raumaushang.schedule_hash = null;
            Raumaushang.update();
        });
    }).on('click', 'header nav a', function () {
        var direction = $(this).hasClass('previous-week')
                      ? Raumaushang.DIRECTION_PREVIOUS
                      : Raumaushang.DIRECTION_NEXT;
        Raumaushang.update(direction);

        Countdown.add('return-to-current', Raumaushang.durations.return_to_current, function () {
            Raumaushang.current.timestamp = Raumaushang.initial.timestamp;
            Raumaushang.schedule_hash = null;
            Raumaushang.update();
        });

        return false;
    });

    // Clock
    window.setInterval(function () {
        $('#clock').text(Raumaushang.getMoment().format('HH:mm'));
    }, 100);

    // Make QR Code
    $.fn.extend({
        makeQRCode: function () {
            return this.each(function () {
                var course_id = $(this).data().courseId,
                    template  = $('meta[name="course-url-template"]').attr('content');
                new QRCode(this, {
                    text: template.replace('#{course_id}', course_id),
                    width: 1024,
                    height: 1024,
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        }
    });


}(jQuery, Raumaushang, Countdown, Mustache, moment, QRCode));
