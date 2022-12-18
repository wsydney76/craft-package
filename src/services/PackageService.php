<?php

namespace wsydney76\package\services;

use Craft;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use Exception;
use wsydney76\package\Plugin;
use yii\base\Component;

/**
 * Package Service service
 */
class PackageService extends Component
{
    public function getQuery(?int $id, string|array $section = null)
    {

        return Entry::find()
            ->section($section)
            ->status(null)
            ->drafts(null)
            ->provisionalDrafts(null)
            ->site('*')
            ->preferSites([Cp::requestedSite()->handle])
            ->unique()
            ->relatedTo([
                'element' => $id,
                'field' => Plugin::getInstance()->getSettings()->relationFieldHandle
            ])
            ->orderBy('title');
    }
}
