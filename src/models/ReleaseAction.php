<?php

namespace wsydney76\package\models;

use Craft;
use craft\elements\Entry;
use wsydney76\contentoverview\models\Action;

class ReleaseAction extends Action
{
    public string $label = 'Release';
    public string $icon = '@wsydney76/package/icons/publish.svg';
    public string $cpAction = 'package/package/release-entry';
    public string $handle = 'releaseAction';

    public function isActiveForEntry(Entry $entry): bool
    {
        if (!$entry->canSave(Craft::$app->user->identity)) {
            return false;
        }

        return $entry->releaseStatus() !== 'released';
    }
}