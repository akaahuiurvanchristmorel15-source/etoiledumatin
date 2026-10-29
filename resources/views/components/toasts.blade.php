@php
    $toasts = collect([
        'success' => ['title' => 'C\'est fait', 'icon' => '<polyline points="5 12.5 10 17 19 7"/>'],
        'error' => ['title' => 'Attention', 'icon' => '<line x1="12" y1="7" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
        'info' => ['title' => 'Information', 'icon' => '<line x1="12" y1="11" x2="12" y2="17"/><line x1="12" y1="7" x2="12.01" y2="7"/>'],
    ])->filter(fn (array $toast, string $type) => session()->has($type));
@endphp

{{-- Pile toujours présente : les messages des mises à jour sans rechargement y sont ajoutés --}}
<div class="toast-stack" aria-live="polite" data-toast-stack>
    @foreach($toasts as $type => $toast)
        <div class="toast toast-{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" data-toast style="--delay: {{ $loop->index * 120 }}ms;">
            <span class="toast-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">{!! $toast['icon'] !!}</svg>
            </span>
            <div class="toast-body">
                <p class="toast-title">{{ $toast['title'] }}</p>
                <p class="toast-message">{{ session($type) }}</p>
            </div>
            <button type="button" class="toast-close" data-toast-close aria-label="Fermer la notification">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <span class="toast-progress" aria-hidden="true"></span>
        </div>
    @endforeach
</div>

@once
    <script>
        /**
         * Active les notifications pas encore prises en charge (au chargement et après chaque mise à jour sans rechargement).
         */
        window.initToasts = function (root) {
            var DURATION = 6000;

            (root || document).querySelectorAll('[data-toast]:not([data-toast-ready])').forEach(function (toast) {
                var remaining = DURATION;
                var startedAt = Date.now();
                var timer;

                toast.setAttribute('data-toast-ready', '');

                function dismiss() {
                    clearTimeout(timer);
                    toast.classList.add('is-leaving');
                    toast.addEventListener('animationend', function () { toast.remove(); }, { once: true });
                }

                function start() {
                    startedAt = Date.now();
                    timer = setTimeout(dismiss, remaining);
                }

                toast.querySelector('[data-toast-close]').addEventListener('click', dismiss);
                toast.addEventListener('mouseenter', function () { clearTimeout(timer); remaining -= Date.now() - startedAt; toast.classList.add('is-paused'); });
                toast.addEventListener('mouseleave', function () { toast.classList.remove('is-paused'); start(); });
                start();
            });
        };

        window.initToasts();
    </script>
@endonce
