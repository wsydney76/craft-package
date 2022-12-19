<?php

namespace wsydney76\package\services;

use Craft;
use craft\elements\Entry;
use craft\helpers\Cp;
use Illuminate\Support\Collection;
use ModifyQueryEvent;
use wsydney76\package\Plugin;
use yii\base\Component;
use yii\web\NotFoundHttpException;

/**
 * Package Service service
 */
class PackageService extends Component
{

    public const EVENT_MODIFY_QUERY = 'eventModifyQuery';

    public function getQuery(?int $id, string|array $section = null)
    {

        $query = Entry::find()
            ->section($section)
            ->status(null)
            ->drafts(null)
            ->provisionalDrafts(null)
            ->site('*')
            ->preferSites([Cp::requestedSite()->handle])
            ->unique()
            ->orderBy('title');

        if ($this->hasEventHandlers(self::EVENT_MODIFY_QUERY)) {
            $this->trigger(self::EVENT_MODIFY_QUERY, new ModifyQueryEvent([
                'query' => $query
            ]));
        } else {
            $query->relatedTo([
                'element' => $id,
                'field' => Plugin::getInstance()->getSettings()->relationFieldHandle
            ]);
        }

        return $query;
    }

    public function checkAllValid(Collection $entries)
    {
        $currentUser = Craft::$app->user->identity;
        foreach ($entries as $entry) {
            if (!$currentUser->can("saveentries:$entry->section->uid")) {
                return false;
            }

            if ($entry->validationResults()['hasErrors']) {
                return false;
            }
        }

        return true;
    }

    public function releaseEntry(Entry $entry, int $packageId, array $options = []): array
    {
        $currentUser = Craft::$app->user->identity;
        if (!$currentUser->can("saveentries:$entry->section->uid")) {
            return [false, "No permission to save '$entry'."];
        }

        if ($entry->validationResults()['hasErrors']) {
            return [false, "'$entry' has errors."];
        }

        if ((bool)$options['enableEntries']) {
            if (!$this->ensureEnabled($entry)) {
                return [false, "Could not enable '$entry'."];
            }
        }

        switch ($entry->releaseStatus()) {
            case 'released':
            {
                // Nothing to do here...
                break;
            }
            case 'unreleased':
            case 'regularDraft':
            case 'provisionalDraft':
            {
                if (!Craft::$app->drafts->applyDraft($entry)) {
                    return [false, "Could not apply draft '$entry'."];
                }
                break;
            }
        }

        if ((bool)$options['removeFromPackage']) {
            $this->removeFromPackage($entry->canonicalId, $packageId);
        }

        return [true, "'$entry' released."];
    }

    protected function ensureEnabled(Entry $entry): bool
    {

        $entry->enabled = true;
        if (!$entry->enabledForSite) {
            $entry->setEnabledForSite(true);
            if (!Craft::$app->elements->saveElement($entry, false, false)) {
                return false;
            }
        }

        foreach ($entry->getLocalized()->status(null)->all() as $localizedEntry) {
            $localizedEntry->enabled = true;

            if (!$localizedEntry->enabledForSite) {
                $localizedEntry->setEnabledForSite([$localizedEntry->siteId]);
                if (!Craft::$app->elements->saveElement($localizedEntry, false, false)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function removeFromPackage(int $id, int $packageId): bool
    {

        $entry = Craft::$app->entries->getEntryById($id);
        if (!$entry) {
            throw new NotFoundHttpException();
        }

        $fieldHandle = Plugin::getInstance()->getSettings()->relationFieldHandle;

        $oldValues = $entry->getFieldValue($fieldHandle)->ids();
        $newValues = array_filter($oldValues, function($value) use ($packageId) {
            return (int)$value !== $packageId;
        });


        $entry->setFieldValue($fieldHandle, $newValues);

        if (!Craft::$app->elements->saveElement($entry, false, false)) {
            return false;
        }

        return true;
    }

    public function attachNewDraft(int $id, int $packageId, array $options): array
    {
        $entry = Craft::$app->entries->getEntryById($id);
        if (!$entry) {
            throw new NotFoundHttpException("Entry with $id not found.");
        }

        $packageEntry = Craft::$app->entries->getEntryById($packageId);
        if (!$packageEntry) {
            throw new NotFoundHttpException("Entry with $packageId not found.");
        }

        if (!$options['ignoreExistingDrafts']) {
            if (Entry::find()->draftOf($entry)->status(null)->exists()) {
                return [false, "There is already a draft for $entry->title"];
            }

            if (Entry::find()->draftOf($entry)->provisionalDrafts(true)->status(null)->exists()) {
                return [false, "There is already a provisional draft for $entry->title"];
            }
        }

        $currentUser = Craft::$app->user->identity;
        if (!$entry->canCreateDrafts($currentUser)) {
            return [false, "No permission for $entry->title"];
        }

        $draft = Craft::$app->drafts->createDraft(
            $entry,
            $currentUser->id,
            notes: "Created for package $packageEntry->title",
            provisional: $options['createProvisionalDraft'],
        );

        if (!$draft) {
            return [false, "Could not create $entry->title"];
        }

        $draft->setCanonical($entry);
        $draft->setFieldValue(Plugin::getInstance()->getSettings()->relationFieldHandle, [$packageId]);
        $draft->scenario = Entry::SCENARIO_ESSENTIALS;

        if (!Craft::$app->elements->saveElement($draft)) {
            return [false, "Could not resave new draft for $entry->title"];
        }

        return [true, "New draft for '$entry' created and attached to package."];
    }


}
