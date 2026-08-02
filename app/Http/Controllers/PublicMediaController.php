<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(string $path): StreamedResponse|Response
    {
        $path = ltrim($path, '/');
        $segments = explode('/', $path);

        abort_if(
            $path === ''
            || str_contains($path, "\0")
            || in_array('..', $segments, true)
            || in_array('.', $segments, true),
            404
        );

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
