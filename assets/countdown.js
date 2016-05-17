/*jslint browser: true */
/*global jQuery */
(function ($) {
    var Countdown = function (callback, options) {
        if (!$.isFunction(callback)) {
            throw 'Invalid arguments passed as callback';
        }

        this.options = $.extend({}, Countdown.default_options, options);

        this.started  = null;
        this.interval = null;
        this.ticks = 0;
        this.callback = callback;
    };
    Countdown.default_options = {
        duration: 5 * 60 * 1000,
        check_interval: 250
    };

    Countdown.prototype.start = function () {
        if (this.started === null) {
            this.ticks = 0;
            this.started = +(new Date);

            this.interval = window.setInterval(this.check.bind(this), this.options.check_interval);
        }
    };
    Countdown.prototype.reset = function () {
        this.ticks = 0;
        this.started = +(new Date);
    };
    Countdown.prototype.stop = function () {
        window.clearInterval(this.interval);
    };
    Countdown.prototype.check = function () {
        var now  = +(new Date),
            diff = now - this.started;
        if (diff > this.options.duration) {
            this.stop();
            this.callback();
        }
        if ($.isFunction(this.options.on_tick)) {
            this.options.on_tick.call(this);
        }
        this.ticks += 1;
    };

    window.Countdown = Countdown;
}(jQuery));