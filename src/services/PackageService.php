<?php

namespace wsydney76\package\services;

use Craft;
use craft\elements\Entry;
use craft\helpers\Cp;
use Illuminate\Support\Collection;
use ModifyEntriesEvent;
use ModifyQueryEvent;
use wsydney76\package\Plugin;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use function extract;
use function is_string;
use const EXTR_OVERWRITE;
use const EXTR_PREFIX_ALL;

/**
 * Package Service service
 */
class PackageService extends Component
{

    public const EVENT_MODIFY_QUERY = 'eventModifyQuery';
    public const EVENT_MODIFY_ENTRIES = 'eventModifyEntries';

    public function getQuery(?int $packageId)
    {

        $query = Entry::find()
            ->status(null)
            ->drafts(null)
            ->provisionalDrafts(null)
            ->site('*')
            ->preferSites([Cp::requestedSite()->handle])
            ->unique()
            ->orderBy('title');

        if ($this->hasEventHandlers(self::EVENT_MODIFY_QUERY)) {
            $this->trigger(self::EVENT_MODIFY_QUERY, new ModifyQueryEvent([
                'packageId' => $packageId,
                'query' => $query
            ]));
        } else {
            $query->relatedTo([
                'element' => $packageId,
                'field' => $this->getRelationFieldHandle($packageId)
            ]);
        }

        return $query;
    }

    public function checkAllValid(Collection $entries)
    {
        // TODO: Cache results for releaseEntry method
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

    public function releaseEntry(Entry $entry, ?int $packageId = null, array $options = []): array
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

        if ($packageId && (bool)$options['removeFromPackage']) {
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

        $fieldHandle = $this->getRelationFieldHandle($packageId);

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

        extract($options, EXTR_PREFIX_ALL, 'options');

        $currentUser = Craft::$app->user->identity;

        $provisionalDrafts = Entry::find()->draftOf($entry)->provisionalDrafts(true)->status(null)->collect();

        if ($options_attachAs === 'attachReleased') {

            $entry->setFieldValue($this->getRelationFieldHandle($packageId), [$packageId]);
            $entry->scenario = Entry::SCENARIO_ESSENTIALS;

            if (!Craft::$app->elements->saveElement($entry, updateSearchIndex: false)) {
                return [false, "Could not resave new draft for $entry->title"];
            }

            return [true, "Attached $entry->title"];
        }

        if ($options_ignoreExistingDrafts) {
            // Never create a second provisional draft for a user
            foreach ($provisionalDrafts as $provisionalDraft) {
                if ($provisionalDraft->creatorId === $currentUser->id) {
                    return [false, "Your have already a provisional draft for $entry->title"];
                }
            }
        } else {
            if (Entry::find()->draftOf($entry)->status(null)->exists()) {
                return [false, "There is already a draft for $entry->title"];
            }

            if ($provisionalDrafts->count()) {
                return [false, "There is already a provisional draft for $entry->title"];
            }
        }

        if (!$entry->canCreateDrafts($currentUser)) {
            return [false, "No permission for $entry->title"];
        }


        $draft = Craft::$app->drafts->createDraft(
            $entry,
            $currentUser->id,
            notes: "Created for package $packageEntry->title",
            provisional: $options_attachAs === 'createProvisionalDraft',
        );

        if (!$draft) {
            return [false, "Could not create $entry->title"];
        }

        $draft->setCanonical($entry);
        $draft->setFieldValue($this->getRelationFieldHandle($packageId), [$packageId]);
        $draft->scenario = Entry::SCENARIO_ESSENTIALS;

        if (!Craft::$app->elements->saveElement($draft, updateSearchIndex: false)) {
            return [false, "Could not resave new draft for $entry->title"];
        }

        return [true, "New draft for '$entry' created and attached to package."];
    }

    public function createEntry(int $packageId, array $options): array
    {
        $currentUser = Craft::$app->user->identity;
        $section = Craft::$app->sections->getSectionByHandle($options['section']);
        if (!$section) {
            throw new InvalidConfigException();
        }

        if (!$currentUser->can("saveentries:$section->uid")) {
            return [false, "Invalid permission for $section->name"];
        }

        $packageEntry = Craft::$app->entries->getEntryById($packageId);
        if (!$packageEntry) {
            throw new NotFoundHttpException("Entry with $packageId not found.");
        }

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->authorId = $currentUser->id;
        $entry->title = $options['title'];
        $entry->setFieldValue($this->getRelationFieldHandle($packageId), [$packageId]);

        if (!Craft::$app->drafts->saveElementAsDraft(
            $entry,
            $currentUser->id,
        )) {
            return [false, "Could not save draft"];
        }

        return [true, $options['title'] . " created"];
    }

    public function getEntries(int $packageId): Collection
    {
        $entries = $this->getQuery($packageId)->collect();
        if ($this->hasEventHandlers(self::EVENT_MODIFY_ENTRIES)) {
            $event = new ModifyEntriesEvent([
                'packageId' => $packageId,
                'entries' => $entries
            ]);
            $this->trigger(self::EVENT_MODIFY_ENTRIES, $event);
            $entries = $event->entries;
        }

        return $entries;
    }

    private function getRelationFieldHandle(?int $packageId): string
    {
        $settings = Plugin::getInstance()->getSettings()->relationFieldHandle;

        if (is_string($settings)) {
            return $settings;
        }

        $sectionHandle = Craft::$app->entries->getEntryById($packageId)->section->handle;

        if (isset($settings[$sectionHandle])) {
            return $settings[$sectionHandle];
        }

        return $settings['*'];
    }


}
