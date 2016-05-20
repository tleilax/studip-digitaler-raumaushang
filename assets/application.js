/*jslint browser: true */
/*global jQuery, Raumaushang, Countdown */
(function ($, Raumaushang, Countdown) {

    // Allow :active style to work
    // @see https://css-tricks.com/snippets/css/remove-gray-highlight-when-tapping-links-in-mobile-safari/
    document.addEventListener("touchstart", function(){}, true);

    // Exit with error when illegal call
    if (Raumaushang === undefined) {
        throw 'Invalid call, object Raumaushang missing';
    }

    // Debug function
    var debug_timeout = null;
    function debug(message) {
        if (console !== undefined) {
            console.log(arguments);
        }

        var message = $.makeArray(arguments).join(' / ');
        $('body > output').text(message).show();

        if (debug_timeout !== null) {
            clearTimeout(debug_timeout);
        }
        debug_timeout = setTimeout(function () {
            $('body > output').hide('slow', function () {
                debug_timeout = null;
            })
        }, 3000);
    }

    // Initialize variables
    Raumaushang.schedule_hash = null;
    Raumaushang.course_data = {};
    Raumaushang.current = {};

    // Initialize countdowns
    Raumaushang.countdowns = {};
    Raumaushang.countdowns.main = new Countdown(function () {
        debug('reloading...');
        Raumaushang.update();
    }, {
        duration: 5 * 60 * 1000,
        on_tick: function (diff) {
            var element = $('body > progress');
            if (element.length > 0) {
                element.attr('value', 100 - diff * 100 / this.options.duration);
            }
        }
    });
    Raumaushang.countdowns.course = new Countdown(function () {
        $('#course-overlay:visible').click();
    }, {duration: 30 * 1000});

    // Define request function
    Raumaushang.request = function (url, data) {
        $('#loading-overlay').show();

        return $.ajax({
            type: 'GET',
            url: Raumaushang.api.url + url,
            data: data || {},
            dataType: 'json',
            username: Raumaushang.api.auth.username,
            password: Raumaushang.api.auth.password,
        }).fail(function (jqxhr, text, error) {
            debug('ajax failed', text, error, url);
        }).always(function () {
            $('#loading-overlay').hide();
        });
    }

    // Highlights cells
    Raumaushang.highlight = function () {
        var now  = new Date,
            day  = now.format('w'),
            slot = window.parseInt(now.format('H'), 10);
        $('tr[data-slot],td[data-day],th[data-day]').removeClass('current-day current-slot');
        $('[data-day="' + day + '"]').addClass('current-day');

        $('tr[data-slot="' + slot + '"],td[data-slot~="' + slot + '"]').addClass('current-slot');


        window.setTimeout(Raumaushang.highlight, 5 * 1000);
    };

    // Updates the table (internally requests data)
    Raumaushang.update = function (callback) {
        $('.week-schedule[data-resource-id]').each(function () {
            var resource_id = $(this).data().resourceId,
                old_table   = $(this);

            Raumaushang.request('/raumaushang/schedule/' + resource_id, {group_by_weekday: 1}).then(function (json, status, jqxhr) {
                var schedule_hash = jqxhr.getResponseHeader('X-Schedule-Hash'),
                    structure     = {},
                    new_table     = old_table.clone();
                if (schedule_hash === Raumaushang.schedule_hash) {
                    debug('known hash, leaving...');
                    return;
                }
                Raumaushang.schedule_hash = schedule_hash;

                $('tbody tr', old_table).each(function () {
                    var slot = $(this).data().slot;
                    structure[slot] = {};
                    $('thead th[data-day]', old_table).each(function () {
                        var day = $(this).data().day;
                        structure[slot][day] = null;
                    })
                });

                $.each(json, function (day, day_data) {
                    var day = parseInt(day, 10),
                        str = (new Date(day_data.timestamp * 1000)).format('d.m.');
                    $('th[data-day="' + day + '"] date', new_table).text(str);

                    if (day === 1) {
                        Raumaushang.current.timestamp = day_data.timestamp;
                    }

                    $.each(day_data.slots, function (slot, data) {
                        slot = parseInt(slot, 10);

                        data.slots = [slot];
                        for (var i = 1; i < data.duration; i += 1) {
                            if (structure[slot + i] !== undefined) {
                                delete structure[slot + i][day];
                                data.slots.push(slot + i);
                            }
                        }

                        structure[slot][day] = data;

                        Raumaushang.course_data[data.id] = data;
                    });
                });

                //
                $('td[data-day]', new_table).remove();
                $.each(structure, function (slot, days) {
                    var row = $('tr[data-slot="' + slot + '"]', new_table);
                    $.each(days, function (day, data) {
                        var td = $('<td>&nbsp;</td>'),
                            teachers = $('<ul class="teachers">');
                        td.attr('data-day', day);
                        if (data !== null) {
                            td.attr('data-slot', data.slots.join(' '));
                            td.append('<div class="name">' + [data.code, data.name].join(' ') + '</div>');

                            td.addClass('course-info');
                            td.attr('data-course-id', data.id);
                            if (data.teachers.length) {
                                $.each(data.teachers, function (index, teacher) {
                                    $('<li>').text(teacher.nachname).appendTo(teachers);
                                });
                                td.append(teachers);
                            }
                            if (data.duration > 1) {
                                td.attr('rowspan', data.duration);
                            }
                        }
                        row.append(td);
                    });
                });

                //
                delete structure;
                old_table.replaceWith(new_table);
            }).always(function () {
                Raumaushang.countdowns.main.start(true);

                if ($.isFunction(callback)) {
                    callback();
                }
            });
        });
    };

    // Handlers

    $(document).ready(function () {
        // Init and start countdown that will reload the page after a certain
        // duration
        $(document).on('mousemove mousedown mouseup touchmove touchstart touchend', function (event) {
            Raumaushang.countdowns.course.reset();
            Raumaushang.countdowns.main.reset();
        });

        // Initialize schedule table
        Raumaushang.update(function () {
            Raumaushang.highlight();
        });
    });

    $(document).on('click', '.course-info', function () {
        $('#loading-overlay').show();

        var course_id = $(this).blur().data().courseId,
            data      = Raumaushang.course_data[course_id],
            overlay   = $('#course-overlay').hide().empty(),
            begin     = new Date(data.begin * 1000),
            end       = new Date(data.end * 1000),
            day       = $(this).data().day,
            slot      = $(this).closest('tr').data().slot,
            teachers  = $('<dl class="teachers"><dt>Lehrende</dt></dl>'),
            modules   = $('<dl class="modules"><dt>Module:</dt></dl>');
        debug(data);

        $('<h2>').text([data.code, data.name].join(' ')).appendTo(overlay);
        $('<date class="begin">').text(begin.format('d.m.Y H:i')).appendTo(overlay);
        $('<span> bis </span>').appendTo(overlay);
        $('<date class="end">').text(end.format('d.m.Y H:i')).appendTo(overlay);

        if (data.teachers.length) {
            $.each(data.teachers, function (index, teacher) {
                $('<dd>').text(teacher.name_full).appendTo(teachers);
            });
            teachers.appendTo(overlay);
        }

        if (data.modules.length) {
            $.each(data.modules, function (index, module) {
                $('<dd>').text(module).appendTo(modules);
            });
            modules.appendTo(overlay);
        }

        Raumaushang.countdowns.main.stop();
        $('#loading-overlay').hide();

        Raumaushang.countdowns.course.start(true);
        overlay.show();

        Raumaushang.current.slot = slot;
        Raumaushang.current.day  = day;

        return false;
    });

    $(document).on('click', '#course-overlay', function () {
        Raumaushang.countdowns.course.stop();
        Raumaushang.countdowns.main.start();
        $(this).hide();
    });

    $('body').on('swipedown swipeup', function (event) {
        if ($('#course-overlay').is(':not(:visible)')) {
            return;
        }

        var elements = $('tr[data-slot="' + Raumaushang.current.slot + '"]');
        elements = event.type === 'swipeup'
                 ? elements.nextAll('tr')
                 : elements.prevAll('tr');
        elements.find('td[data-day="' + Raumaushang.current.day + '"].course-info')
                .first()
                .click();
    });

    $(document).on('keypress', function (event) {
        var code = (event.which || event.keyCode);
        if (code == 27 || code == 13) {
            window.location.reload();
            event.preventDefault();
            event.stopPropagation();
        }
    }).on('select', '*', function (event) {
        event.preventDefault();
    });

    $(document).on('click', '#help-overlay-switch', function () {
        $('#help-overlay').show();
    }).on('click', '#help-overlay', function () {
        $(this).hide();
    });

}(jQuery, Raumaushang, Countdown));