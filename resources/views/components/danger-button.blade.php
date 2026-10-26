<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded bg-danger px-5 py-3 text-sm font-semibold text-white transition-colors duration-150 hover:bg-danger-700 disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
