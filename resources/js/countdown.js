var Countdown = function (callback, options) {
    if (!(callback instanceof Function)) {
        throw 'Invalid arguments passed as callback';
    }

    this.options = Object.assign({}, Countdown.default_options, options);

    this.started  = null;
    this.interval = null;
    this.ticks = 0;
    this.callback = callback;
};
Countdown.default_options = {
    duration: 5 * 60 * 1000,
    check_interval: 250,
    interval: false
};
Countdown.stash = {};
Countdown.add = function (identifier, duration, callback, options) {
    options = options || {};
    options.duration = duration;

    if (!this.stash.hasOwnProperty(identifier)) {
        this.stash[identifier] = new Countdown(callback, options);
    } else {
        this.stash[identifier].setCallback(callback);
    }

    return this.stash[identifier].start(true);
};
Countdown.start = function (identifier, reset) {
    if (!this.stash.hasOwnProperty(identifier)) {
        throw 'Unknown countdown "' + identifier + '"';
    }
    this.stash[identifier].start(reset || false);
};
Countdown.stop = function (identifier) {
    if (identifier === undefined) {
        Object.values(this.stash).forEach((countdown) => {
            countdown.stop();
        });
    } else if (!this.stash.hasOwnProperty(identifier)) {
        throw 'Unknown countdown "' + identifier + '"';
    } else {
        this.stash[identifier].stop();
    }
};
Countdown.reset = function (identifier) {
    if (identifier === undefined) {
        Object.values(this.stash).forEach((countdown) => {
            countdown.reset();
        });
    } else if (!this.stash.hasOwnProperty(identifier)) {
        throw 'Unknown countdown "' + identifier + '"';
    } else {
        this.stash[identifier].reset();
    }
};

Countdown.prototype.start = function (reset) {
    reset = reset || false;

    if (this.started === null || reset) {
        this.ticks = 0;
        this.started = +(new Date);
    }

    if (this.interval === null) {
        this.interval = window.setInterval(this.check.bind(this), this.options.check_interval);
    }
};
Countdown.prototype.reset = function () {
    if (this.started !== null) {
        this.ticks = 0;
        this.started = +(new Date);
    }
};
Countdown.prototype.stop = function () {
    window.clearInterval(this.interval);

    this.interval = null;
    this.started  = null;
};
Countdown.prototype.check = function () {
    var now  = +(new Date),
        diff = now - this.started;
    if (diff > this.options.duration) {
        this.stop();
        this.callback();

        if (this.options.interval) {
            this.start(true);
        }
    }
    if (this.options.on_tick instanceof Function) {
        this.options.on_tick.call(this, diff);
    }
    this.ticks += 1;
};
Countdown.prototype.setCallback = function (callback) {
    this.callback = callback;
};

export default Countdown;
