@props(['label', 'value', 'tone' => 'forest'])
<article class="card admin-card dashboard-stat-card h-100"><div class="card-body p-4"><span class="dashboard-stat-icon tone-{{ $tone }}" aria-hidden="true"></span><p class="text-secondary small text-uppercase fw-semibold mb-2">{{ $label }}</p><p class="dashboard-stat-value mb-0">{{ number_format((int) $value) }}</p></div></article>
