@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/quill/quill.js') }}"></script>
    <script>
        document.querySelectorAll('[data-quill]').forEach((host) => {
            const input = document.getElementById('input-' + host.dataset.quill);

            if (! window.Quill) {
                return;
            }

            const editor = new Quill(host, {
                theme: 'snow',
                placeholder: 'Tulis isi artikel…',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'blockquote'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean'],
                    ],
                },
            });

            editor.root.setAttribute('aria-label', host.dataset.label);

            // Quill renders icon-only buttons without labels; give each a tooltip.
            // The toolbar sits beside the editor, so read it from the module.
            const toolbar = editor.getModule('toolbar').container;
            const tooltips = { bold: 'Tebal', italic: 'Miring', underline: 'Garis bawah', blockquote: 'Kutipan', link: 'Tautan', clean: 'Hapus format' };

            toolbar.querySelectorAll('button').forEach((button) => {
                const name = [...button.classList].find((cls) => cls.startsWith('ql-'))?.slice(3);

                button.title = name === 'list'
                    ? (button.getAttribute('value') === 'ordered' ? 'Daftar bernomor' : 'Daftar poin')
                    : (tooltips[name] ?? button.title);
            });

            const headerPicker = toolbar.querySelector('.ql-header');

            if (headerPicker) {
                headerPicker.title = 'Judul bagian';
            }

            const sync = () => {
                input.value = editor.root.innerHTML;
            };

            editor.on('text-change', sync);
            sync();
        });
    </script>
@endpush
