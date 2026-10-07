// synmod-example admin JS — imported into the host resources/js/app.js by `php artisan example:setup`.

// <x-example::rich-text> — Jodit bound to a Livewire property. Jodit owns its
// DOM (the wrapper is wire:ignore'd) and is imported lazily so pages without an
// editor never download it. Changes are pushed with a deferred $wire.set.
document.addEventListener('alpine:init', () => {
    Alpine.data('exampleRichText', (config) => ({
        editor: null,

        async init() {
            let Jodit;
            try {
                [{ Jodit }] = await Promise.all([
                    import('jodit'),
                    import('jodit/esm/plugins/all.js'),
                    import('jodit/es2021/jodit.min.css'),
                ]);
            } catch (error) {
                console.error('[example] Jodit failed to load, using plain textarea.', error);
                this.$refs.textarea.addEventListener('input', (event) => {
                    this.$wire.set(config.field, event.target.value, false);
                });
                return;
            }

            this.editor = Jodit.make(this.$refs.textarea, {
                height: config.height,
                toolbarAdaptive: false,
                askBeforePasteHTML: false,
                askBeforePasteFromWord: false,
                defaultActionOnPaste: 'insert_clear_html',
                buttons: [
                    'undo', 'redo', '|',
                    'paragraph', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'ul', 'ol', '|',
                    'link', '|',
                    'source',
                ],
            });

            this.editor.events.on('change', (value) => this.$wire.set(config.field, value, false));
        },

        destroy() {
            if (this.editor) {
                this.editor.destruct();
                this.editor = null;
            }
        },
    }));
});

// <x-example::chart> — ApexCharts, imported lazily. One instance per host;
// Livewire pushes new data on the window event `example-chart-{id}` and the
// chart is updated in place. The host is cleared before rendering because a
// wire:navigate back-navigation restores DOM that may still hold the old SVG.
// `examplePrint` backs the print button (window is not reachable from CSP
// Alpine expressions).
document.addEventListener('alpine:init', () => {
    const chartOptions = (config, dark) => {
        const circular = config.type === 'donut' || config.type === 'pie';
        const options = {
            chart: { type: config.type, height: config.height, toolbar: { show: false }, background: 'transparent', fontFamily: 'inherit' },
            theme: { mode: dark ? 'dark' : 'light' },
            series: config.series,
            dataLabels: { enabled: circular },
            legend: { position: 'bottom' },
            grid: { borderColor: dark ? '#374151' : '#e5e7eb', strokeDashArray: 3 },
            plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
            stroke: { curve: 'smooth', width: config.type === 'line' ? 3 : 0 },
        };

        if (circular) {
            options.labels = config.labels;
        } else {
            options.xaxis = { categories: config.labels };
        }

        return options;
    };

    // Themes put `dark` on <html> or <body> (the default theme uses <body>).
    const isDark = () => document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');

    Alpine.data('exampleChart', (config) => ({
        chart: null,
        failed: false,
        config,

        async init() {
            let ApexCharts;
            try {
                ({ default: ApexCharts } = await import('apexcharts'));
            } catch (error) {
                console.error('[example] ApexCharts failed to load.', error);
                this.failed = true;
                return;
            }

            this.destroy();
            this.$refs.host.innerHTML = '';
            this.chart = new ApexCharts(this.$refs.host, chartOptions(this.config, isDark()));
            await this.chart.render();
        },

        update(detail) {
            this.config = Object.assign({}, this.config, { series: detail.series, labels: detail.labels });

            if (this.chart) {
                this.chart.updateOptions(chartOptions(this.config, isDark()));
            }
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        },
    }));

    Alpine.data('examplePrint', () => ({
        print() {
            window.print();
        },
    }));
});
