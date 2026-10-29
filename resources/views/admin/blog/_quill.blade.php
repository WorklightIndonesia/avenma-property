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

            const sync = () => {
                input.value = editor.root.innerHTML;
            };

            editor.on('text-change', sync);
            sync();
        });
    </script>
@endpush
