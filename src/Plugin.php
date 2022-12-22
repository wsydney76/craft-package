<?php

namespace wsydney76\package;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Fields;
use craft\web\View;
use wsydney76\contentoverview\events\RegisterActionsEvent;
use wsydney76\contentoverview\services\ContentOverviewService;
use wsydney76\package\behaviors\EntryBehavior;
use wsydney76\package\fields\MaintainPackage;
use wsydney76\package\helpers\FileLog;
use wsydney76\package\models\ReleaseAction;
use wsydney76\package\models\Settings;
use wsydney76\package\services\MigrationService;
use wsydney76\package\services\PackageService;
use yii\base\Event;
use const DIRECTORY_SEPARATOR;

/**
 * package plugin
 *
 * @method static Plugin getInstance()
 * @author wsydney76 <wsydney@web.de>
 * @copyright wsydney76
 * @license MIT
 * @property-read PackageService $packageService
 * @property-read MigrationService $migrationService
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'packageService' => PackageService::class,
                'migrationService' => MigrationService::class
            ]
        ];
    }

    public function init()
    {
        parent::init();


        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            FileLog::create('package', 'package/*');
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['package'] = $this->basePath . DIRECTORY_SEPARATOR . 'templates';
            }
        );

        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors[] = EntryBehavior::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = MaintainPackage::class;
            });

        Event::on(
            ContentOverviewService::class,
            ContentOverviewService::EVENT_REGISTER_ACTIONS,
            function(RegisterActionsEvent $event) {
                $event->actions['release'] = ReleaseAction::class;
            }
        );


    }
}
