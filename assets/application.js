/*jslint browser: true */
/*global jQuery, Countdown */
(function ($, Countdown) {

    function request(url, data) {
        if (Raumaushang.api_url === undefined) {
            Raumaushang.api_url = $('meta[name="api-url"]').attr('content');
        }

//        $('#overlay').show();

        return $.ajax({
            type: 'GET',
            url: Raumaushang.api_url + url,
            data: data || {},
            dataType: 'json',
            username: Raumaushang.auth.username,
            password: Raumaushang.auth.password,
        }).fail(function () {
            $('body > code').html('ajax failed');
        }).always(function () {
            $('#overlay').hide();
        });
    }


    $(document).ready(function () {
        // Init and start countdown that will reload the page after a certain
        // duration
        var countdown = new Countdown(function () {
            $('body > code').text('reloading...');
            window.location.reload();
        }, {
            duration: 5 * 60 * 1000
        });
        $(document).on('mousemove mousedown mouseup touchmove touchstart touchend', function (event) {
            $('body > code').text('countdown reset: ' + event.type);
            countdown.reset();
        });
        countdown.start();

        // Initialize schedule table
        $('.week-schedule[data-resource-id]').each(function () {
            var resource_id = $(this).data().resourceId,
                old_table   = $(this),
                new_table   = old_table.clone(),
                structure   = {};

            $('tbody tr', old_table).each(function () {
                var slot = $(this).data().slot;
                structure[slot] = {};
                $('thead th[data-day]', old_table).each(function () {
                    var day = $(this).data().day;
                    structure[slot][day] = null;
                })
            });


            request('/raumaushang/schedule/' + resource_id, {group_by_weekday: 1}).then(function (json) {
                $.each(json, function (day, slots) {
                    day = parseInt(day, 10);

                    $.each(slots, function (slot, data) {
                        slot = parseInt(slot, 10);

                        structure[slot][day] = data;

                        for (var i = 1; i < data.duration; i += 1) {
                            if (structure[slot + i] !== undefined) {
                                delete structure[slot + i][day];
                            }
                        }
                    });
                });

                //
                $('td[data-day]', new_table).remove();
                $.each(structure, function (slot, days) {
                    var row = $('tr[data-slot="' + slot + '"]', new_table);
                    $.each(days, function (day, data) {
                        var td = $('<td>&nbsp;</td>'),
                            a  = $('<a class="course-info">');
                        td.attr('data-day', day);
                        if (data !== null) {
                            a.text(data.code + ' ' + data.name);
                            a.attr('href', '#' + data.id);
                            a.attr('data-course-id', data.id);
                            td.html(a);
                            if (data.teachers) {
                                td.append('<br>');
                                td.append('<div>' + data.teachers + '</div>');
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
            });

        });
    });

    $(document).on('click', 'a.course-info', function () {
        var course_id = $(this).data.courseId;
        console.log(course_id);
    });

}(jQuery, Countdown));