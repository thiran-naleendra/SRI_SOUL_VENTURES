@props(['paginator'])@if($paginator->hasPages())<nav class="mt-5" aria-label="Pagination">{{ $paginator->links('pagination::bootstrap-5') }}</nav>@endif
