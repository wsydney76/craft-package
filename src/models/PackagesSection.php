<?php

namespace wsydney76\package\models;

use Craft;
use craft\elements\Entry;
use wsydney76\contentoverview\models\Section;
use wsydney76\contentoverview\Plugin;

class PackagesSection extends Section
{
    public array|string $section = 'paPackage';

    public ?string $layout = 'cardlets';


    public function init(): void
    {
        $this->actions = [
            'slideout',
            Plugin::getInstance()->contentoverview->createAction()
                ->label(Craft::t('package', 'Maintain package'))
                ->icon('@wsydney76/package/icons/publish.svg')
                ->cpUrl('contentoverview/package')
                ->cpUrlTarget('')
        ];
    }

    public function getInfo(Entry $entry): string
    {
        $count = Entry::find()
            ->status(null)
            ->drafts(null)
            ->provisionalDrafts(null)
            ->relatedTo($entry)
            ->count();

        return 'Entry count: ' . $count;
    }

}

