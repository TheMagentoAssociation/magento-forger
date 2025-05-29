<?php
namespace App\Menus;

use App\Helpers\RouteLabelHelper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Spatie\Menu\Laravel\Menu;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Link;

class MainMenu
{
    public static function build(): Menu
    {
        $currentRoute = Route::currentRouteName();

        $routes = collect(Route::getRoutes())
            ->map(fn($route) => $route->getName())
            ->filter()
            ->filter(fn($name) => !empty($name) && preg_match('/^(home|issues|prs|labels)(-[\w]+)?$/', $name));

        $menu = Menu::new()
            ->addClass('navbar-nav me-auto mb-2 mb-lg-0')
            ->setActiveFromRequest();

        $grouped = $routes->groupBy(fn($name) => explode('-', $name)[0]);

        foreach ($grouped as $mainItem => $subRoutes) {
            $mainRouteExists = $subRoutes->contains($mainItem);
            $childRoutes = $subRoutes->filter(fn($name) => $name !== $mainItem);

            if ($mainRouteExists && $childRoutes->isEmpty()) {
                // Single item, no submenu
                $menu->add(
                    Link::toRoute($mainItem, ucfirst($mainItem))
                        ->addClass('nav-link')
                        ->addParentClass('nav-item')
                );
            } elseif ($childRoutes->isNotEmpty()) {
                // Build dropdown submenu
                $submenuItems = '';
                foreach ($childRoutes as $child) {
                    $label = self::formatLabel($child);
                    $submenuItems .= sprintf(
                        '<li><a class="dropdown-item" href="%s">%s</a></li>',
                        route($child),
                        $label
                    );
                }

                if (trim($submenuItems) !== '') {
                    // Check if current route matches one of the children
                    $isActive = $childRoutes->contains($currentRoute) ? ' active' : '';

                    $dropdownHtml = sprintf(
                        '<li class="nav-item dropdown%s">
        <a class="nav-link dropdown-toggle" href="#" id="dropdown-%s" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            %s
        </a>
        <ul class="dropdown-menu" aria-labelledby="dropdown-%s">
            %s
        </ul>
    </li>',
                        $isActive,             // %1$s
                        $mainItem,             // %2$s
                        ucfirst($mainItem),    // %3$s
                        $mainItem,             // %4$s again for aria-labelledby
                        $submenuItems          // %5$s
                    );

                    $menu->add(Html::raw(trim($dropdownHtml)));
                }
            }
        }

        return $menu;
    }

    private static function formatLabel(string $routeName): string
    {
        return RouteLabelHelper::formatLabel($routeName);
    }
}
