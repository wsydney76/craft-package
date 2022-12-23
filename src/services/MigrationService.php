<?php

namespace wsydney76\package\services;

use Craft;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Entries;
use craft\helpers\App;
use craft\models\FieldGroup;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\records\FieldGroup as FieldGroupRecord;
use modules\main\helpers\FileHelper;
use wsydney76\contentoverview\Plugin;
use wsydney76\package\fields\MaintainPackage;
use yii\base\Component;
use yii\base\Exception;

/**
 * Migration Service service
 */
class MigrationService extends Component
{

    public function install(): bool
    {

        // Create field group package
        $fieldGroup = $this->getFieldGroup('Package');
        if (!$fieldGroup) {
            $fieldGroup = new FieldGroup([
                'name' => 'Package',
            ]);

            if (!Craft::$app->fields->saveGroup($fieldGroup)) {
                Craft::error('Could not install fieldGroup', 'package/install');
                return false;
            }
        }

        // Create maintain package field
        $maintainPackageField = Craft::$app->fields->getFieldByHandle('paMaintainPackage');
        if (!$maintainPackageField) {
            $maintainPackageField = new MaintainPackage([
                'groupId' => $fieldGroup->id,
                'name' => 'Maintain Package',
                'handle' => 'paMaintainPackage'
            ]);

            if (!Craft::$app->fields->saveField($maintainPackageField)) {
                Craft::error('Could not create mainPackageField', 'package/install');
                return false;
            }
        }


        // Create package section
        $section = Craft::$app->sections->getSectionByHandle('paPackage');
        if (!$section) {
            $section = new Section([
                    'name' => 'Package',
                    'handle' => 'paPackage',
                    'type' => Section::TYPE_CHANNEL,
                    'siteSettings' => collect(Craft::$app->sites->getAllSites())
                        ->map(fn($section) => new Section_SiteSettings([
                            'siteId' => $section->id,
                            'enabledByDefault' => true,
                            'hasUrls' => false
                        ]))
                        ->toArray()
                ]
            );

            if (!Craft::$app->sections->saveSection($section)) {
                Craft::error('Could not create package section', 'package/install');
                return false;
            }

            // Attach maintain package field to field layout
            $type = $section->getEntryTypes()[0];
            $layout = $type->getFieldLayout();
            $tab = $layout->getTabs()[0];

            $tab->setElements(array_merge($tab->getElements(), [
                new CustomField($maintainPackageField)
            ]));


            if (!Craft::$app->fields->saveLayout($layout))
                Craft::error('Could not save fieldlayout', 'package/install');;
        }

        // Create package field
        $packageField = Craft::$app->fields->getFieldByHandle('paPackage');

        if (!$packageField) {
            $packageField = new Entries([
                'groupId' => $fieldGroup->id,
                'name' => 'Package',
                'handle' => 'paPackage',
                'sources' => [
                    "section:$section->uid"
                ]
            ]);

            if (!Craft::$app->fields->saveField($packageField)) {
                Craft::error('Could not save packageField', 'package/install');
                return false;
            }
        }

        // Create package.php plugin settings file
        try {
            $dir = App::parseEnv('@config');
            $dest = $dir . DIRECTORY_SEPARATOR . 'package.php';
            if (!is_file($dest)) {
                $source = App::parseEnv('@wsydney76/package/scaffold/config.txt');
                copy($source, $dest);
            }
        } catch (Exception $e) {
            Craft::error('Could not copy plugin config package.php: ' . $e->getMessage(), 'package/install');
        }

        return true;
    }


    public function uninstall(): bool
    {
        // Remove package section. This also deletes all package entries and all relations to them
        $section = Craft::$app->sections->getSectionByHandle('paPackage');
        if ($section) {
            if (!Craft::$app->sections->deleteSectionById($section->id)) {
                return false;
            }
        }

        foreach (['paPackage', 'paMaintainPackage'] as $fieldHandle) {
            $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
            if ($field) {
                Craft::$app->fields->deleteField($field);
            }
        }

        // Remove field group. This also deletes all fields in it.
        $fieldGroup = $this->getFieldGroup('package');
        if ($fieldGroup) {
            if (!Craft::$app->fields->deleteGroupById($fieldGroup['id'])) {
                return false;
            }
        }

        return true;
    }

    private function getFieldGroup(string $fieldGroup)
    {
        return FieldGroupRecord::findOne(['name' => $fieldGroup]);
    }

}
