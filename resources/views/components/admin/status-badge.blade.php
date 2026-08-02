@props(['status'])
@php($classes = match (strtolower($status)) { 'active', 'published', 'resolved' => 'text-bg-success', 'pending', 'draft' => 'text-bg-warning', 'inactive', 'archived' => 'text-bg-secondary', default => 'text-bg-light' })
<span {{ $attributes->class(['badge', 'rounded-pill', $classes]) }}>{{ ucfirst($status) }}</span>
