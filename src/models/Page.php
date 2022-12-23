<?php

namespace wsydney76\package\models;

use Illuminate\Support\Collection;
use wsydney76\contentoverview\Plugin as CoPlugin;
use wsydney76\package\Plugin;

class Page extends \wsydney76\contentoverview\models\Page
{
    public string $label = 'Maintain Package';
    public string $pageKey = 'package';

    public function getTabs(): Collection
    {
        $co = CoPlugin::getInstance()->contentoverview;
        $class = Plugin::getInstance()->packageService->getSectionClassFromRequest();

        return collect([
            $co->createTab('Package', [
                $co->createColumn(12, [
                    $co->createSection($class)
                ]),
            ])
        ]);

    }
}