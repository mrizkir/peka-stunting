import { plainTextToHtml, sanitizePastedHtml } from './sanitize-paste-html';

/**
 * Editor isi konten tanpa Quill — contenteditable + perintah format browser.
 */
export function createEducationBodyEditor(config = {}) {
    return {
        initialHtml: config.initialHtml ?? '',
        savedRange: null,

        init() {
            const editor = this.$refs.editor;
            if (! editor || editor.dataset.ready === 'true') {
                return;
            }

            editor.dataset.ready = 'true';
            editor.innerHTML = sanitizePastedHtml(this.initialHtml) || '';

            try {
                document.execCommand('styleWithCSS', false, true);
            } catch (_error) {
                // Browser lama: tetap pakai perintah justify*.
            }

            const remember = () => this.saveSelection();
            editor.addEventListener('mouseup', remember);
            editor.addEventListener('keyup', remember);
            editor.addEventListener('focus', remember);
            editor.addEventListener('input', () => this.sync());

            this.setupPaste(editor);
            this.sync();

            this.$el.closest('form')?.addEventListener('submit', () => this.sync());
        },

        saveSelection() {
            const selection = window.getSelection();
            if (! selection || selection.rangeCount === 0) {
                return;
            }

            const range = selection.getRangeAt(0);
            if (this.$refs.editor.contains(range.commonAncestorContainer)) {
                this.savedRange = range.cloneRange();
            }
        },

        restoreSelection() {
            this.$refs.editor.focus();

            if (! this.savedRange) {
                return;
            }

            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(this.savedRange);
        },

        runCommand(command, value = null) {
            this.restoreSelection();
            document.execCommand(command, false, value);
            this.saveSelection();
            this.sync();
        },

        formatBold() {
            this.runCommand('bold');
        },

        formatItalic() {
            this.runCommand('italic');
        },

        formatList(type) {
            this.runCommand(type === 'ordered' ? 'insertOrderedList' : 'insertUnorderedList');
        },

        setHeader(level) {
            this.restoreSelection();
            const tag = level && String(level) !== '' ? `h${level}` : 'p';
            document.execCommand('formatBlock', false, tag);
            this.saveSelection();
            this.sync();
        },

        setAlign(align) {
            const commands = {
                left: 'justifyLeft',
                center: 'justifyCenter',
                right: 'justifyRight',
                justify: 'justifyFull',
            };

            const command = commands[align];
            if (command) {
                this.runCommand(command);
            }
        },

        setupPaste(editor) {
            editor.addEventListener('paste', (event) => {
                event.preventDefault();

                const clipboard = event.clipboardData;
                if (! clipboard) {
                    return;
                }

                const html = clipboard.getData('text/html');
                const text = clipboard.getData('text/plain');

                let content = '';
                if (html?.trim()) {
                    content = sanitizePastedHtml(html);
                } else if (text?.trim()) {
                    content = plainTextToHtml(text);
                }

                if (! content) {
                    return;
                }

                this.restoreSelection();
                document.execCommand('insertHTML', false, content);
                this.sync();
            });
        },

        sync() {
            if (this.$refs.bodyInput && this.$refs.editor) {
                this.$refs.bodyInput.value = this.$refs.editor.innerHTML;
            }
        },
    };
}
