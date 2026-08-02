<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Experience;
use App\Models\Package;
use App\Support\PublicCache;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember(PublicCache::SITEMAP, now()->addHour(), function (): string {
            $urls = collect([
                [route('home'), now()],
                [route('experiences.index'), now()],
                [route('packages.index'), now()],
                [route('destinations.index'), now()],
                [route('custom-tours'), now()],
                [route('about'), now()],
                [route('contact'), now()],
            ]);

            foreach ([
                Experience::class => 'experiences.show',
                Package::class => 'packages.show',
                Destination::class => 'destinations.show',
            ] as $model => $routeName) {
                $model::query()->where('is_active', true)->orderBy('id')
                    ->get(['slug', 'updated_at'])
                    ->each(fn ($record) => $urls->push([route($routeName, $record), $record->updated_at]));
            }

            $entries = $urls->map(function (array $url): string {
                $location = htmlspecialchars($url[0], ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $lastModified = $url[1]?->toAtomString();

                return "  <url>\n    <loc>{$location}</loc>\n    <lastmod>{$lastModified}</lastmod>\n  </url>";
            })->implode("\n");

            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$entries}\n</urlset>";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
