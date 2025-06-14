<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UniverseBarController extends Controller
{
    public function render(Request $request)
    {
        $allowedOrigins = [
            'magento-opensource.com',
            'docs.magento-opensource.com',
            'magentoassociation.org',
            '*.magentoassociation.org',
            'meet-magento.com',
            'gh-stats.ddev.site'
        ];

        $origin = $request->headers->get('Origin');

        if ($request->getHost() !== 'gh-stats.ddev.site' || !$this->isOriginAllowed($origin, $allowedOrigins, $request)) {
            return response('Forbidden', 403);
        }

        $html = view('components.universe-bar')->render();

        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('Access-Control-Allow-Origin', $origin);
    }

    private function isOriginAllowed(?string $origin, array $allowedOrigins, Request $request): bool
    {
        // DDEV Fallback
        if ($request->getHost() === 'gh-stats.ddev.site') return true;
        if (!$origin) return false;

        $host = parse_url($origin, PHP_URL_HOST);

        foreach ($allowedOrigins as $allowed) {
            if ($allowed === $host) {
                return true;
            }
            if (Str::startsWith($allowed, '*.') && Str::endsWith($host, substr($allowed, 1))) {
                return true;
            }
        }

        return false;
    }
}
