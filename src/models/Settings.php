<?php

namespace wsydney76\package\models;

use craft\base\Model;

class Settings extends Model
{
    public array|string $imageField = '';
    public string|array $relationFieldHandle = 'paPackage';
    public ?array $sections = null;
    public bool $addIdsColumn = false;
    public bool $addDraftInfoColumn = true;
    public bool $addWorkflowColumn = false;
    public array $formTemplates = [
        '/package/forms/release.twig',
        '/package/forms/attach-drafts.twig',
        '/package/forms/attach-new.twig'
    ];
}