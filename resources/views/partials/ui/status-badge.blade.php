@php
    $tone = $tone ?? 'neutral';
    $label = $label ?? '';
    $allowedTones = ['success', 'warning', 'danger', 'info', 'neutral'];
    if (! in_array($tone, $allowedTones, true)) {
        $tone = 'neutral';
    }
@endphp
<span class="status-badge status-badge--{{ $tone }}">{{ $label }}</span>
