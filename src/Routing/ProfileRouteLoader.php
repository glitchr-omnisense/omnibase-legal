<?php

namespace Base\Legal\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * The bundle's routes for the site's profession, whichever it is:
 *
 *     # config/routes.yaml
 *     legal:
 *         resource: .
 *         type: legal
 *
 * The practice's pages when the profession keeps one (the fields of
 * practice, the mediation, a first appointment asked, the client's space),
 * then the profile's own (a lawyer's fees and the memo for the Ordre, a
 * notary's tariff) - and none of another profession's. The back office's
 * pages are read when omnibase/admin is installed.
 */
final class ProfileRouteLoader extends Loader
{
    public function __construct(
        private readonly string $profession,
        private readonly bool $practice = true,
        private readonly bool $admin = true,
    ) {
        parent::__construct();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return 'legal' === $type;
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $src = \dirname(__DIR__);
        $folders = array_merge($this->practice ? [$src.'/Practice/Controller'] : [], [$src.'/'.ucfirst($this->profession).'/Controller']);

        $routes = new RouteCollection();
        foreach ($folders as $folder) {
            foreach (array_merge(['Client'], $this->admin ? ['Admin'] : []) as $side) {
                if (is_dir($folder.'/'.$side)) {
                    $routes->addCollection($this->import($folder.'/'.$side, 'attribute'));
                }
            }
        }

        return $routes;
    }
}
