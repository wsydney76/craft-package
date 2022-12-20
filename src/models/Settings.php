<?php

namespace wsydney76\package\models;

use craft\base\Model;

class Settings extends Model
{
    public array|string $imageField = '';
    public string $relationFieldHandle = 'paPackage';
}