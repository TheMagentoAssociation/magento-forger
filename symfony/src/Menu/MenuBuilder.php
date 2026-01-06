<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class MenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {}

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav me-auto');

        $menu->addChild('Home', [
            'route' => 'home',
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link'],
        ]);

        $menu->addChild('PRs by Month', [
            'route' => 'prs-PRsByMonth',
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link'],
        ]);

        $menu->addChild('Issues by Month', [
            'route' => 'issues-issuesByMonth',
            'attributes' => ['class' => 'nav-item'],
            'linkAttributes' => ['class' => 'nav-link'],
        ]);

        // Labels dropdown
        $labels = $menu->addChild('Labels', [
            'uri' => '#',
            'attributes' => [
                'class' => 'nav-item dropdown',
            ],
            'linkAttributes' => [
                'class' => 'nav-link dropdown-toggle',
                'data-bs-toggle' => 'dropdown',
                'role' => 'button',
                'aria-expanded' => 'false',
            ],
        ]);

        $labels->setChildrenAttribute('class', 'dropdown-menu');

        $labels->addChild('All Labels', [
            'route' => 'labels-listAllLabels',
            'linkAttributes' => ['class' => 'dropdown-item'],
        ]);

        $labels->addChild('PRs Missing Component', [
            'route' => 'labels-PRsWithoutComponentLabel',
            'linkAttributes' => ['class' => 'dropdown-item'],
        ]);

        return $menu;
    }
}
