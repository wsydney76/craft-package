<?php

namespace wsydney76\package\models;

use craft\elements\Entry;
use wsydney76\contentoverview\models\Section;
use wsydney76\contentoverview\Plugin;

class PackagesSection extends Section
{
    public array|string $section = 'package';
    public array|string $imageField = 'featuredImage';
    public ?string $layout = 'cards';
    public array|string $help = 'HILFE!!!';


    public function init(): void
    {
        $this->actions = [
            Plugin::getInstance()->contentoverview->createAction()
                ->label('Details')
                ->icon('@appicons/wand.svg')
                ->cpUrl('contentoverview/package')
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

