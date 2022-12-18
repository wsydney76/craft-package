<?php

namespace wsydney76\package\behaviors;

use Craft;
use craft\elements\Entry;
use craft\helpers\ElementHelper;
use yii\base\Behavior;
use function in_array;

class EntryBehavior extends Behavior
{
    public function validationResults()
    {
        /** @var Entry $entry */
        $entry = $this->owner;

        $siteErrors = [];
        $hasErrors = false;

        $entry->scenario = Entry::SCENARIO_LIVE;

        $errorKeysFound = [];

        if (!$entry->validate()) {
            $siteErrors[$entry->site->handle] = [
                'siteName' => $entry->site->name,
                'errors' => $entry->errors
            ];
            $hasErrors = true;
            foreach ($entry->errors as $key => $error) {
                $errorKeysFound[] = $key;
            }
        }
        foreach ($entry->getLocalized()->all() as $localizedItem) {
            $localizedItem->scenario = Entry::SCENARIO_LIVE;
            if (!$localizedItem->validate()) {

                $errors = [];
                foreach ($localizedItem->errors as $key => $error) {
                    if (!in_array($key, $errorKeysFound)) {
                        $errorKeysFound[] = $key;
                        $errors[$key] = $error;
                    }
                }

                if ($errors) {
                    $siteErrors[$localizedItem->site->handle] = [
                        'siteName' => $localizedItem->site->name,
                        'errors' => $errors
                    ];
                    $hasErrors = true;
                }
            }
        }

        return ['hasErrors' => $hasErrors, 'errors' => $siteErrors];
    }

    public function releaseStatus()
    {
        /** @var Entry $entry */
        $entry = $this->owner;

        if ($entry->isCanonical && $entry->isDraft) {
            return 'unreleased';
        }
        if ($entry->isProvisionalDraft) {
            return 'provisionalDraft';
        }
        if ($entry->isDraft) {
            return 'regularDraft';
        }

        return 'released';
    }
}