<?php

namespace wsydney76\package\models;

use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use wsydney76\contentoverview\models\TableSection;
use wsydney76\contentoverview\Plugin as ContentoverviewPlugin;
use wsydney76\package\Plugin;
use yii\web\NotFoundHttpException;

class PackageSection extends TableSection
{

    public bool $showIndexButton = false;
    public bool $showNewButton = false;
    public bool $showRefreshButton = true;
    public array|string $imageField = 'featuredImage';

    public ?int $elementId;
    public Entry $entry;

    public function init(): void
    {

        $co = ContentoverviewPlugin::getInstance()->contentoverview;

        $this->elementId = Craft::$app->request->getQueryParam('elementId');

        $this->imageField = Plugin::getInstance()->getSettings()->imageField;

        $this->columns = [
            $co->createTableColumn()
                ->label('IDs')
                ->template('package/ids.twig'),
            $co->createTableColumn()
                ->label('Status')
                ->template('package/status.twig'),
            $co->createTableColumn()
                ->label('Draft info')
                ->template('package/draftinfo.twig'),
            $co->createTableColumn()
                ->label('Validation')
                ->template('package/validation.twig')
        ];

        $this->actions = [
            'slideout',
            'compare',
            'relationships',
            $co->createAction()
                ->label('Remove from package')
                ->icon('@wsydney76/package/templates/icons/x-circle.svg')
                ->cpAction('package/publish/remove-from-package')
                ->extraParams(['packageId' => $this->elementId])
            ,
            'view'
        ];

        if ($this->elementId) {
            $this->entry = Craft::$app->entries->getEntryById($this->elementId);
            if (!$this->entry) {
                throw new NotFoundHttpException();
            }
            $this->heading = $this->entry->title;
        }

        Craft::$app->view->registerAssetBundle('wsydney76\\package\\assets\\PackageAssetBundle');
    }


    public function getPermittedSections(string $permission): array
    {
        return ['*'];
    }


    public function getHelp(): array|string
    {
        return Craft::$app->view->renderTemplate('package/action.twig', ['entry' => $this->entry]);
    }

    public function getQuery(array $params): ElementQueryInterface
    {
        if (isset($params['queryParams']['elementId'])) {
            $this->elementId = $params['queryParams']['elementId'];
        }

        return Plugin::getInstance()->packageService->getQuery($this->elementId, $this->section);
    }

}