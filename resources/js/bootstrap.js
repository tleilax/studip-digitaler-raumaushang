import jQuery from 'jquery';
import moment from 'moment-timezone';

// Exit with error when illegal call
if (window.Raumaushang === undefined) {
    throw 'Invalid call, object Raumaushang missing';
}

// Setup moment
moment.locale('de');
moment.tz.add('Europe/Berlin|CET CEST CEMT|-10 -20 -30|01010101010101210101210101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010|-2aFe0 11d0 1iO0 11A0 1o00 11A0 Qrc0 6i00 WM0 1fA0 1cM0 1cM0 1cM0 kL0 Nc0 m10 WM0 1ao0 1cp0 dX0 jz0 Dd0 1io0 17c0 1fA0 1a00 1ehA0 1a00 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1fA0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1fA0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1cM0 1fA0 1o00 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00 11A0 1qM0 WM0 1qM0 WM0 1qM0 WM0 1qM0 11A0 1o00 11A0 1o00|41e5');
moment.tz.setDefault(window.Raumaushang.timezone);

// Setup raumaushang
const Raumaushang = Object.assign({}, window.Raumaushang, {
    setMoment(now) {
        this.now    = moment(now);
        this.chdate = moment();
    },
    getMoment() {
        var diff = moment().diff(this.chdate);
        return moment(this.now).add(diff, 'milliseconds');
    },
});

Raumaushang.setMoment(window.Raumaushang.now);

// Extend jQuery
jQuery.fn.extend({
    clamp() {
        return this.each(function () {
            if (this.children.length > 0) {
                throw 'Cannot execute clamp() on non-text nodes';
            }
            var chunks  = jQuery(this).text().split(' '),
                height  = jQuery(this).height();
            jQuery(this).wrapInner('<div>');

            while (height < jQuery('div', this).height() && chunks.length > 0) {
                chunks.pop();
                jQuery('div', this).text(chunks.join(' ') + '...');
            }

            jQuery(this).text(jQuery('div', this).text());
        });
    }
});

export { jQuery, moment, Raumaushang };
