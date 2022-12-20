<?php

namespace wsydney76\package\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use wsydney76\package\Plugin;

/**
 * Package Section field type
 */
class MaintainPackage extends Field
{

    public static function displayName(): string
    {
        return Craft::t('package', 'Package Section');
    }

    public static function valueType(): string
    {
        return 'string';
    }

    public static function hasContentColumn(): bool
    {
        return false;
    }


    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        return $value;
    }

    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {

        $query = Plugin::getInstance()->packageService->getQuery($element->id);

        return Craft::$app->view->renderTemplate('package/maintainpackage.twig', [
            'packageId' => $element->id,
            'query' => $query
        ]);
    }

}
