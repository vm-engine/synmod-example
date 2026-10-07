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
