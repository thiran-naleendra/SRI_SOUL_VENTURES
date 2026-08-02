@props(['number'])@if($number)<a class="whatsapp-float" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D+/','',$number) }}" aria-label="Chat with us on WhatsApp">WA</a>@endif
