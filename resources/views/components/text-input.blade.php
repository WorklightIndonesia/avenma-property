@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded border-line bg-white text-ink-950 placeholder:text-ink-500 focus:border-gold-700 focus:ring-gold-500/30']) }}>
