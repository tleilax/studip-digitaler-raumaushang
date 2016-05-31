/*jslint browser: true */
/*global jQuery, Raumaushang, Countdown */
(function ($, Raumaushang, Countdown) {

    // Allow :active style to work
    // @see https://css-tricks.com/snippets/css/remove-gray-highlight-when-tapping-links-in-mobile-safari/
    document.addEventListener('touchstart', function(){}, true);

    // Exit with error when illegal call
    if (Raumaushang === undefined) {
        throw 'Invalid call, object Raumaushang missing';
    }

    // Initialize variables
    $.extend(Raumaushang, {
        max: {teachers: 3},
        DIRECTION_NEXT: '>',
        DIRECTION_PREVIOUS: '<',
        schedule_hash: null,
        course_data: {},
        current: {
            timestamp: $('meta[name="current-timestamp"]').attr('content')
        },
        initial: {
            timestamp: $('meta[name="current-timestamp"]').attr('content')
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

        $('#loading-overlay:visible').hide();
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
        return Mustache.render(templates[template_id], data);
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
                    td   = $('tr[data-slot="' + slot + '"] td[data-day="' + day +'"]'),
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
            })
        });

        return;

        var Colors = {1: 'red', 2: 'blue', 3: 'green', 4: 'orange', 5: 'purple'};
        $.each(Raumaushang.offsets, function (day, data) {
            if (day === 'fraction') {
                return;
            }
            $.each(data.offsets, function (slot, offsets) {
                if (slot % 2 == day % 2) {
                    return;
                }
                $('<div>').text(day + ' / ' + slot + ' (' + data.left + '/' + data.right + ' x ' + offsets.top + ')').css({
                    position: 'absolute',
                    left: data.left,
                    right: data.right,
                    top: offsets.top,
                    bottom: offsets.bottom,
                    border: '1px solid green',
                    background: Colors[day]
                }).appendTo('body');
            });
        });
    };

    // Define request function
    var requests = {};
    Raumaushang.request = function (url, data, callback, forced) {
        if (!forced && requests.hasOwnProperty(url)) {
            var cached = requests[url];
            if (cached.timestamp.format('Ymd') === (new Date).format('Ymd')) {
                return callback(cached.hash, cached.data);
            }
        }

        window.setTimeout(function () {
            $('#loading-overlay').show();
        }, 300);

        return $.ajax({
            type: 'GET',
            url: Raumaushang.api.url + url,
            data: data || {},
            dataType: 'json',
            username: Raumaushang.api.auth.username,
            password: Raumaushang.api.auth.password,
        }).then(function (json, status, jqxhr) {
            var schedule_hash = jqxhr.getResponseHeader('X-Schedule-Hash');

            requests[url] = {
                timestamp: new Date(),
                hash: schedule_hash,
                data: json
            };

            if ($.isFunction(callback)) {
                callback(schedule_hash, json);
            }

        }).fail(function (jqxhr, text, error) {
            if (console !== undefined) {
                console.log('ajax failed', text, error, url);
            }
        }).always(function () {
            $('#loading-overlay').hide();
        });
    }

    // Highlights cells
    Raumaushang.highlight = function () {
        var now  = new Date,
            day  = now.format('w'),
            slot = window.parseInt(now.format('H'), 10);
        $('body > .schedule-item').removeClass('current-slot').filter('[data-slot~="' + slot + '"][data-day="' + day + '"]:not(.is-holiday)').addClass('current-slot');

        window.setTimeout(Raumaushang.highlight, 250);
    };

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

    //
    Raumaushang.createItemOffsets = function (item, offset) {
        if (!offset.offsets.hasOwnProperty(item.slot)) {
            console.log(item, offset);
        }

        var top_offset = offset.offsets[item.slot],
            bottom_slot = item.slot + (item.fraction + item.duration) / 4,
            bottom_offset;

        if (offset.offsets.hasOwnProperty(Math.floor(bottom_slot))) {
            bottom_offset = offset.offsets[Math.floor(bottom_slot)];
        } else {
            console.log(item, offset, bottom_slot);
            bottom_offset = offset.offsets[21];
        }

        return {
            left: offset.left,
            right: offset.right,
            top: Math.round(top_offset.top + item.fraction * top_offset.height / 4),
            bottom: bottom_offset.bottom + (bottom_slot % 1 > 0 ? 1 - bottom_slot % 1 : 0) * bottom_offset.height
        };
    }

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
            forced      = false;;

        if (Raumaushang.current.timestamp) {
            if (direction === Raumaushang.DIRECTION_NEXT) {
                chunks.push(Raumaushang.current.timestamp + 7 * 24 * 60 * 60);
            } else if (direction === Raumaushang.DIRECTION_PREVIOUS) {
                chunks.push(Raumaushang.current.timestamp - 7 * 24 * 60 * 60);
            } else {
                chunks.push(Raumaushang.current.timestamp);
                forced = true;
            }
        }

        Raumaushang.request(chunks.join('/'), {}, function (schedule_hash, json) {
            var first     = null,
                last      = null,
                text,
                cells = $();
            if (schedule_hash !== Raumaushang.schedule_hash) {
                Raumaushang.schedule_hash = schedule_hash;

                $('.schedule-item').remove();

                $.each(json, function (day, day_data) {
                    var str = (new Date(day_data.timestamp * 1000)).format('d.m.');

                    day = parseInt(day, 10);

                    $('th[data-day="' + day + '"] date', table).text(str);

                    if (day === 1) {
                        Raumaushang.current.timestamp = day_data.timestamp;
                    }
                    if (first === null) {
                        first = day_data.timestamp;
                    }
                    last = day_data.timestamp;

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
                            cell = $(html).css(styles).attr('data-foo', styles.min_diff);

                        cells = cells.add(cell);
                    });
                });

                $('body').append(cells);
                $(cells).filter(':not([data-duration="1"],[data-duration="2"])').find('.name').clamp();

                // Update week display
                first = new Date(first * 1000);
                last  = new Date(last * 1000);
                text  = 'Kalenderwoche <strong>' + first.format('W/Y') + '</strong>';
                text += ' vom <strong>' + first.format('d.m.') + '</strong>';
                text += ' bis <strong>' + last.format('d.m.') + '</strong>';

                $('body > header small').html(text);
            }
            Countdown.start('main', true);

            if ($.isFunction(callback)) {
                callback();
            }
        }, forced);
    };

    // Handlers
    $(document).ready(function () {
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
    }).on('select', '*', function (event) {
        event.preventDefault();
    }).on('mousemove mousedown mouseup touchmove touchstart touchend', function (event) {
        Countdown.reset();
    }).on('click', '.course-info', function () {
        $('#loading-overlay').show();

        var course_id = $(this).blur().data().courseId,
            data      = Raumaushang.course_data[course_id],
            day       = $(this).data().day,
            slot      = $(this).data().slot,
            rendered = 'error';

        $('#course-overlay').html(render('#course-template', $.extend({}, data, {
            begin: (new Date(data.begin * 1000)).format('d.m.Y H:i'),
            end: (new Date(data.end * 1000)).format('d.m.Y H:i'),
            hasTeachers: data.teachers.length > 0,
            hasModules: data.modules.length > 0
        }))).find('.qrcode').makeQRCode();

        showOverlay('#course-overlay', Raumaushang.durations.course);

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
    }).on('click', '.qrcode', function (event) {
        $(this).toggleClass('enlarged');

        event.preventDefault();
        event.stopPropagation();
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
        $('#clock').text((new Date).format('H:i:s'));
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
        },
        clamp: function () {
            return this.each(function () {
                if (this.children.length > 0) {
                    throw 'Cannot execute clamp() on non-text nodes';
                }
                var chunks  = $(this).text().split(' '),
                    height  = $(this).height(),
                    changed = false;
                $(this).wrapInner('<div>');

                while (height < $('div', this).height() && chunks.length > 0) {
                    chunks.pop();
                    $('div', this).text(chunks.join(' ') + '...');
                }

                $(this).text($('div', this).text());
            });
        }
    });


}(jQuery, Raumaushang, Countdown));