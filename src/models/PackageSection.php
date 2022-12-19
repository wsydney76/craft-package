<?php

namespace wsydney76\package\models;

use Craft;
use craft\db\Paginator;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use wsydney76\contentoverview\models\TableSection;
use wsydney76\contentoverview\Plugin as ContentoverviewPlugin;
use wsydney76\package\Plugin;
use yii\web\NotFoundHttpException;
use function collect;

class PackageSection extends TableSection
{

    public bool $showIndexButton = false;
    public bool $showNewButton = false;
    public bool $showRefreshButton = true;
    public array|string $imageField = 'featuredImage';

    public ?int $packageId;
    public Entry $entry;

    public function init(): void
    {

        $co = ContentoverviewPlugin::getInstance()->contentoverview;

        $this->packageId = Craft::$app->request->getQueryParam('elementId');

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
            'view'
        ];

        if ($this->packageId) {
            $this->entry = Craft::$app->entries->getEntryById($this->packageId);
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
        return Craft::$app->view->renderTemplate('package/actions.twig', ['entry' => $this->entry, 'sectionConfig' => $this]);
    }

    public function getQuery(array $params): ElementQueryInterface
    {
        if (isset($params['queryParams']['packageId'])) {
            $this->packageId = $params['queryParams']['packageId'];
        }

        return Plugin::getInstance()->packageService->getQuery($this->packageId, $this->section);
    }

    public function getSources(): string|array
    {
        if (!$this->section) {
            return '*';
        }
        return collect($this->_normalizeToArray($this->section))->map(fn($section) =>
            'section:' . Craft::$app->sections->getSectionByHandle($section)->uid
        )->toArray();

    }

    public function getSectionOptions(): array
    {
        if (!$this->section) {
            return collect(Craft::$app->sections->getAllSections())
                ->map(fn ($section) => [
                    'label' => $section->name,
                    'value' => $section->handle
                ])->toArray();
        }

        $sections = collect($this->_normalizeToArray($this->section));
        return $sections
            ->map(fn ($section) => [
                'label' => Craft::$app->sections->getSectionByHandle($section),
                'value' => $section
            ])->toArray();

    }


}