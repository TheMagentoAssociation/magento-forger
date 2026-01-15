<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UniverseBarController extends AbstractController
{
    /** @var array<int, string> */
    private const ALLOWED_ORIGINS = [
        'magento-opensource.com',
        'docs.magento-opensource.com',
        'magentoassociation.org',
        '*.magentoassociation.org',
        'meet-magento.com',
        'forger.magento-opensource.com',
        '*.ddev.site',
    ];

    #[Route('/universe-bar', name: 'universe_bar')]
    public function __invoke(Request $request): Response
    {
        $origin = $request->headers->get('Origin');

        if (!$this->isOriginAllowed($origin, $request)) {
            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $html = $this->renderView('components/_universe_bar.html.twig');

        $response = new Response($html, Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');

        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        return $response;
    }

    private function isOriginAllowed(?string $origin, Request $request): bool
    {
        // DDEV Fallback
        if (str_contains($request->getHost(), 'ddev.site')) {
            return true;
        }

        if (!$origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (!$host) {
            return false;
        }

        foreach (self::ALLOWED_ORIGINS as $allowed) {
            if ($allowed === $host) {
                return true;
            }
            if (str_starts_with($allowed, '*.') && str_ends_with($host, substr($allowed, 1))) {
                return true;
            }
        }

        return false;
    }
}
