<?php

namespace wsydney76\package\models;

use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use wsydney76\contentoverview\models\TableSection;
use wsydney76\contentoverview\Plugin as ContentoverviewPlugin;
use wsydney76\package\Plugin;
use yii\web\NotFoundHttpException;
use function collect;
use function is_string;

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

        // Set columns

        $columns = collect([]);

        if (Plugin::getInstance()->getSettings()->addIdsColumn) {
            $columns = $columns->push(
                $co->createTableColumn()
                    ->label('Ids')
                    ->template('package/ids.twig')
            );
        }

        $columns = $columns->push(
            $co->createTableColumn()
                ->label('Status')
                ->template('package/status.twig'),
        );

        if (Plugin::getInstance()->getSettings()->addWorkflowColumn) {
            $columns = $columns->push(
                $co->createTableColumn()
                    ->label('Draft info')
                    ->template('package/draftinfo.twig')
            );
        }

        if (Plugin::getInstance()->getSettings()->addWorkflowColumn) {
            $columns = $columns->push(
                $co->createTableColumn()
                    ->label('Workflow')
                    ->template('package/workflow.twig')
            );
        }

        $columns = $columns->push(
            $co->createTableColumn()
                ->label('Validation')
                ->template('package/validation.twig')
        );


        $this->columns = $columns->toArray();


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
        if (isset($params['queryParams']['elementId'])) {
            $this->packageId = $params['queryParams']['elementId'];
        }

        return Plugin::getInstance()->packageService->getQuery($this->packageId);
    }

    public function getSections(Entry $package): string|array
    {
        $settings = Plugin::getInstance()->getSettings();
        if (isset($settings->sections[$package->section->handle])) {
            return $settings->sections[$package->section->handle];
        }

        if (!$this->section) {
            return '*';
        }
        return $this->_normalizeToArray($this->section);
    }

    public function getSources(Entry $package): string|array
    {
        $sections = $this->getSections($package);
        if (is_string($sections) && $sections === '*' ) {
            return $sections;
        }

        return collect($sections)->map(fn($section) =>
            'section:' . Craft::$app->sections->getSectionByHandle($section)->uid
        )->toArray();
    }

    public function getSectionOptions(Entry $package): array
    {
        $sections = $this->getSections($package);

        if (is_string($sections) && $sections === '*' ) {
            return collect(Craft::$app->sections->getAllSections())
                ->map(fn ($section) => [
                    'label' => $section->name,
                    'value' => $section->handle
                ])->toArray();
        }

        return collect($sections)
            ->map(fn ($section) => [
                'label' => Craft::$app->sections->getSectionByHandle($section),
                'value' => $section
            ])->toArray();

    }


}