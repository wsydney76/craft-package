<?php

namespace wsydney76\package\controllers;

use Craft;
use craft\web\Controller;
use wsydney76\package\Plugin;
use function implode;

class PackageController extends Controller
{
    public $defaultAction = 'publish';

    public function actionRelease()
    {
        $packageId = Craft::$app->request->getRequiredBodyParam('packageId');
        $options = Craft::$app->request->getRequiredBodyParam('options');

        // return $this->asFailure("Something went wrong.");

        $messages = [];
        $errors = [];

        $entries = Plugin::getInstance()->packageService->getQuery($packageId)->collect();

        if ($options['validAllOnly']) {
            if (!Plugin::getInstance()->packageService->checkAllValid($entries)) {
                return $this->asFailure("Could not run: Some entries contain errors.");
            }
        }

        foreach ($entries as $entry) {
            [$success, $message] = Plugin::getInstance()->packageService->releaseEntry($entry, $packageId, $options);
            if ($success) {
                $messages[] = $message;
            } else {
                $errors[] = $message;
            }
        }

        $errorsCount = count($errors);
        return $this->asSuccess(
            "Released entries with $errorsCount error(s) .",
            data: [
                'notice' => implode('<br>', $messages),
                'error' => implode('<br>', $errors)
            ],
            notificationSettings: [
                'details' => '<button class="btn" onclick="window.location.reload()">Refresh</button>'
            ]
        );
    }

    public function actionAttachNewDrafts()
    {
        $packageId = Craft::$app->request->getRequiredBodyParam('packageId');
        $ids = Craft::$app->request->getRequiredBodyParam('ids');
        $options = Craft::$app->request->getRequiredBodyParam('options');

        $messages = [];
        $errors = [];

        if (!$ids) {
            return $this->asFailure("No entries selected.");
        }

        foreach ($ids as $id) {
            [$success, $message] = Plugin::getInstance()->packageService->attachNewDraft($id, $packageId, $options);
            if ($success) {
                $messages[] = $message;
            } else {
                $errors[] = $message;
            }
        }

        $errorsCount = count($errors);
        return $this->asSuccess(
            "Executed task with $errorsCount error(s).",
            data: [
                'notice' => implode('<br>', $messages),
                'error' => implode('<br>', $errors)
            ],
            notificationSettings: [
                'details' => '<button class="btn" onclick="window.location.reload()">Refresh</button>'
            ]
        );
    }

    public function actionAttachNewEntry()
    {
        $packageId = Craft::$app->request->getRequiredBodyParam('packageId');
        $options = Craft::$app->request->getRequiredBodyParam('options');

        // return $this->asFailure("Something went wrong.");

        if (!$options['title']) {
            return $this->asFailure("Title is empty.");
        }

        [$success, $message] = Plugin::getInstance()->packageService->createEntry($packageId, $options);
        if (!$success) {
            return $this->asFailure($message);
        }

        return $this->asSuccess(
            $message,
            // Reset messages
            data: [
                'notice' => '',
                'error' => ''
            ],
            notificationSettings: [
                'details' => '<button class="btn" onclick="window.location.reload()">Refresh</button>'
            ]
        );
    }

}