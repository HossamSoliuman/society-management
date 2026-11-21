/**
 * Chart.js bootstrapper for <x-chart> components.
 *
 * Every <canvas data-chart="{...}"> is turned into a chart. Pages that swap
 * HTML fragments call appCharts.destroy(el) before and appCharts.init(el)
 * after replacing the markup.
 */
(function () {
    var styles = getComputedStyle(document.documentElement);
    var FALLBACK_EMPTY = '#e2e8f0';

    function resolveColor(color) {
        if (typeof color !== 'string') { return color; }
        var match = color.match(/^var\((--[\w-]+)\)$/);
        if (!match) { return color; }
        return styles.getPropertyValue(match[1]).trim() || '#94a3b8';
    }

    function withAlpha(color, alpha) {
        color = resolveColor(color);
        if (typeof color !== 'string' || color.charAt(0) !== '#') { return color; }
        var hex = color.slice(1);
        if (hex.length === 3) { hex = hex.split('').map(function (c) { return c + c; }).join(''); }
        var r = parseInt(hex.slice(0, 2), 16);
        var g = parseInt(hex.slice(2, 4), 16);
        var b = parseInt(hex.slice(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    var grouping = new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 });

    function formatValue(value, format) {
        var num = Number(value) || 0;
        if (format === 'inr') { return '₹ ' + grouping.format(num); }
        if (format === 'percent') { return grouping.format(num) + '%'; }
        return grouping.format(num);
    }

    function compactTick(value, format) {
        var num = Number(value) || 0;
        var abs = Math.abs(num);
        var trim = function (n) { return String(Math.round(n * 10) / 10); };
        var out;
        if (abs >= 10000000) { out = trim(num / 10000000) + 'Cr'; }
        else if (abs >= 100000) { out = trim(num / 100000) + 'L'; }
        else if (abs >= 1000) { out = trim(num / 1000) + 'K'; }
        else { out = String(Math.round(num)); }
        if (format === 'inr') { return '₹' + out; }
        if (format === 'percent') { return out + '%'; }
        return out;
    }

    function isCircular(type) {
        return type === 'doughnut' || type === 'pie';
    }

    function buildDatasets(config) {
        var type = config.type === 'area' ? 'line' : config.type;
        var defaultFill = config.type === 'area';

        return (config.datasets || []).map(function (ds) {
            var out = { label: ds.label || '', data: ds.data || [] };

            if (isCircular(type)) {
                var total = out.data.reduce(function (sum, v) { return sum + (Number(v) || 0); }, 0);
                out.backgroundColor = (ds.colors || []).map(resolveColor);
                out.borderWidth = 0;
                out.hoverOffset = 4;
                if (total <= 0) {
                    out.data = [1];
                    out.backgroundColor = [FALLBACK_EMPTY];
                    out._empty = true;
                }
                return out;
            }

            var color = resolveColor(ds.color || 'var(--primary)');
            var lineType = (ds.type || type) === 'line';
            out.type = ds.type || undefined;
            out.borderColor = color;

            if (lineType) {
                out.fill = ds.fill === undefined ? defaultFill : !!ds.fill;
                out.backgroundColor = out.fill ? withAlpha(color, 0.1) : color;
                out.borderWidth = 2.5;
                out.tension = 0.35;
                out.cubicInterpolationMode = 'monotone';
                out.pointRadius = ds.points === false ? 0 : 3;
                out.pointHoverRadius = 5;
                out.pointBackgroundColor = '#fff';
                out.pointBorderColor = color;
                out.pointBorderWidth = 2;
                if (ds.dashed) { out.borderDash = [5, 4]; }
            } else {
                out.backgroundColor = ds.colors ? ds.colors.map(resolveColor) : color;
                out.borderWidth = 0;
                out.borderRadius = 4;
                out.maxBarThickness = ds.thickness || 18;
            }

            if (ds.stack) { out.stack = ds.stack; }

            return out;
        });
    }

    function buildOptions(config, datasets) {
        var type = config.type;
        var format = config.format || 'number';
        var empty = datasets.length && datasets[0]._empty;
        var textColor = '#94a3b8';
        var gridColor = '#f1f5f9';

        var options = {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 400 },
            interaction: isCircular(type) ? { mode: 'nearest', intersect: true } : { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: !!config.legend,
                    position: config.legendPosition || 'bottom',
                    labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, color: '#64748b', font: { size: 11 } }
                },
                tooltip: {
                    enabled: !empty,
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 6,
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 12 },
                    boxPadding: 4,
                    callbacks: {
                        label: function (ctx) {
                            var raw = isCircular(type) ? ctx.raw : (config.horizontal ? ctx.parsed.x : ctx.parsed.y);
                            var name = isCircular(type) ? ctx.label : ctx.dataset.label;
                            var text = formatValue(raw, format);
                            if (isCircular(type)) {
                                var total = ctx.dataset.data.reduce(function (s, v) { return s + (Number(v) || 0); }, 0);
                                if (total > 0) { text += ' (' + Math.round(raw / total * 100) + '%)'; }
                            }
                            return name ? ' ' + name + ': ' + text : ' ' + text;
                        }
                    }
                }
            }
        };

        if (isCircular(type)) {
            options.cutout = config.cutout || '72%';
            return options;
        }

        // Solid legend swatches in the series colour (line points are white-filled).
        options.plugins.legend.labels.generateLabels = function (chart) {
            return Chart.defaults.plugins.legend.labels.generateLabels(chart).map(function (item) {
                var ds = chart.data.datasets[item.datasetIndex];
                if (ds && typeof ds.borderColor === 'string') {
                    item.fillStyle = ds.borderColor;
                    item.strokeStyle = ds.borderColor;
                }
                return item;
            });
        };

        var valueAxis = {
            beginAtZero: true,
            stacked: !!config.stacked,
            grid: { color: gridColor, drawTicks: false },
            border: { display: false },
            ticks: {
                color: textColor,
                font: { size: 10 },
                padding: 6,
                maxTicksLimit: 5,
                precision: 0,
                callback: function (value) { return compactTick(value, format); }
            }
        };
        var categoryAxis = {
            stacked: !!config.stacked,
            grid: { display: false },
            border: { display: false },
            ticks: { color: textColor, font: { size: 10 }, maxRotation: 0, autoSkip: true, autoSkipPadding: 8 }
        };

        if (config.horizontal) {
            options.indexAxis = 'y';
            options.scales = { x: valueAxis, y: categoryAxis };
        } else {
            options.scales = { x: categoryAxis, y: valueAxis };
        }

        return options;
    }

    function render(canvas) {
        if (typeof Chart === 'undefined') { return; }
        var existing = Chart.getChart(canvas);
        if (existing) { existing.destroy(); }

        var config;
        try {
            config = JSON.parse(canvas.getAttribute('data-chart'));
        } catch (e) {
            console.error('Invalid chart config', e);
            return;
        }

        var datasets = buildDatasets(config);
        var type = config.type === 'area' ? 'line' : config.type;

        new Chart(canvas, {
            type: type,
            data: { labels: config.labels || [], datasets: datasets },
            options: buildOptions(config, datasets)
        });
    }

    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";
        Chart.defaults.color = '#64748b';
    }

    window.appCharts = {
        init: function (root) {
            (root || document).querySelectorAll('canvas[data-chart]').forEach(render);
        },
        destroy: function (root) {
            if (typeof Chart === 'undefined') { return; }
            (root || document).querySelectorAll('canvas[data-chart]').forEach(function (canvas) {
                var chart = Chart.getChart(canvas);
                if (chart) { chart.destroy(); }
            });
        },
        format: formatValue
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { window.appCharts.init(document); });
    } else {
        window.appCharts.init(document);
    }
})();
