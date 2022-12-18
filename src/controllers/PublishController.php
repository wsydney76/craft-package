<?php

namespace wsydney76\package\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use wsydney76\package\Plugin;
use function array_filter;

class PublishController extends Controller
{
    public $defaultAction = 'publish';

    public function actionPublish()
    {
        $elementId = Craft::$app->request->getRequiredBodyParam('elementId');

        // return $this->asFailure("Something went wrong.");

        $entries = Plugin::getInstance()->packageService->getQuery($elementId)->collect();

        return $this->asSuccess(
            "We did something with {$entries->count()} entries.",
            data: [
                'notice' => 'Log goes here!',
                'error' => 'Error messages go here!'
            ],
            notificationSettings: ['details' => '<button class="btn" onclick="window.location.reload()">Refresh</button>']
        );
    }

    public function actionRemoveFromPackage()
    {
        $elementId = Craft::$app->request->getRequiredBodyParam('elementId');
        $extraParams = Json::decodeIfJson(Craft::$app->request->getRequiredBodyParam('extraParams'));

        $packageId = $extraParams['packageId'];

        $entry = Craft::$app->entries->getEntryById($elementId);
        if (!$entry) {
            return $this->asFailure("Entry $elementId not found");
        }

        $fieldHandle = Plugin::getInstance()->getSettings()->relationFieldHandle;

        $oldValues = $entry->getFieldValue($fieldHandle)->ids();
        $newValues = array_filter($oldValues, function($value) use ($packageId) {
           return  $value !== $packageId;
        });

        $entry->setFieldValue($fieldHandle, $newValues);

        if(!Craft::$app->elements->saveElement($entry, false, false)) {
            return $this->asFailure("Entry $elementId could not be saved.");
        }

        return $this->asSuccess(
            "Entry $entry->title removed from package."
        );
    }
}