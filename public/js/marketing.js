(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Header: solid once the page scrolls, mobile menu toggle. */
    var header = document.querySelector('[data-header]');
    if (header) {
        var syncHeader = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 12);
        };
        syncHeader();
        window.addEventListener('scroll', syncHeader, { passive: true });

        var menuButton = header.querySelector('[data-menu-toggle]');
        if (menuButton) {
            menuButton.addEventListener('click', function () {
                var open = header.classList.toggle('is-open');
                menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
                menuButton.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
                menuButton.querySelector('i').className = open ? 'fas fa-xmark' : 'fas fa-bars';
            });
        }
    }

    /* Demo form: after a submit, bring the first invalid field or the confirmation into view. */
    var firstInvalid = document.querySelector('.mk-form-card .is-invalid');
    var success = document.querySelector('.mk-success');
    if (firstInvalid) {
        firstInvalid.focus({ preventScroll: true });
        firstInvalid.scrollIntoView({ block: 'center' });
    } else if (success && window.innerWidth < 1080) {
        success.scrollIntoView({ block: 'center' });
    }

    /* Role tabs. */
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
        var tabs = Array.prototype.slice.call(group.querySelectorAll('[role="tab"]'));

        var select = function (tab) {
            tabs.forEach(function (other) {
                var selected = other === tab;
                other.setAttribute('aria-selected', selected ? 'true' : 'false');
                other.tabIndex = selected ? 0 : -1;
                document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
            });
        };

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { select(tab); });
            tab.addEventListener('keydown', function (event) {
                var next = null;
                if (event.key === 'ArrowRight') { next = tabs[(index + 1) % tabs.length]; }
                if (event.key === 'ArrowLeft') { next = tabs[(index - 1 + tabs.length) % tabs.length]; }
                if (next) {
                    event.preventDefault();
                    select(next);
                    next.focus();
                }
            });
        });
    });

    /* Hero tower: windows light up as flats pay their maintenance. */
    var scene = document.querySelector('[data-tower]');
    if (!scene) {
        return;
    }

    var flats = Array.prototype.slice.call(scene.querySelectorAll('[data-flat]'));
    var amountEl = scene.querySelector('[data-collected]');
    var barEl = scene.querySelector('[data-bar]');
    var countEl = scene.querySelector('[data-count]');
    var toast = scene.querySelector('[data-toast]');
    var toastTitle = scene.querySelector('[data-toast-title]');
    var toastMeta = scene.querySelector('[data-toast-meta]');
    var methods = ['UPI', 'UPI', 'Net banking', 'Card', 'Cheque at office'];
    var receiptNo = 412;
    var money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
    var total = flats.reduce(function (sum, flat) { return sum + Number(flat.dataset.amount); }, 0);

    var render = function () {
        var paid = flats.filter(function (flat) { return flat.classList.contains('is-paid'); });
        var collected = paid.reduce(function (sum, flat) { return sum + Number(flat.dataset.amount); }, 0);
        amountEl.textContent = money.format(collected);
        barEl.style.width = Math.round((collected / total) * 100) + '%';
        countEl.textContent = paid.length === flats.length
            ? 'All ' + flats.length + ' flats paid'
            : paid.length + ' of ' + flats.length + ' flats paid';
    };

    render();

    if (reduceMotion) {
        return;
    }

    var timer = null;
    var toastTimer = null;
    var resetting = false;

    var showToast = function (flat) {
        var method = methods[Math.floor(Math.random() * methods.length)];
        receiptNo += 1;
        toastTitle.textContent = flat.dataset.flat + ' paid ' + money.format(Number(flat.dataset.amount));
        toastMeta.textContent = method + ' · Receipt RC-' + String(receiptNo).padStart(4, '0') + ' emailed';
        toast.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 1500);
    };

    var tick = function () {
        if (resetting) {
            return;
        }

        var pending = flats.filter(function (flat) { return !flat.classList.contains('is-paid'); });

        if (pending.length === 0) {
            resetting = true;
            setTimeout(function () {
                flats.forEach(function (flat) {
                    flat.classList.remove('is-new');
                    if (Math.random() < 0.45) {
                        flat.classList.remove('is-paid');
                    }
                });
                render();
                resetting = false;
            }, 3200);
            return;
        }

        var flat = pending[Math.floor(Math.random() * pending.length)];
        flat.classList.add('is-paid', 'is-new');
        setTimeout(function () { flat.classList.remove('is-new'); }, 1300);
        showToast(flat);
        render();
    };

    var start = function () {
        if (!timer) {
            timer = setInterval(tick, 2100);
        }
    };
    var stop = function () {
        clearInterval(timer);
        timer = null;
    };

    var inView = true;
    var sync = function () {
        inView && !document.hidden ? start() : stop();
    };

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            inView = entries[0].isIntersecting;
            sync();
        }, { threshold: 0.2 }).observe(scene);
    } else {
        sync();
    }

    document.addEventListener('visibilitychange', sync);
})();
