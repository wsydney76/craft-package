<?php

use craft\elements\db\ElementQueryInterface;
use Illuminate\Support\Collection;
use yii\base\Event;

class ModifyEntriesEvent extends Event
{
    public int $packageId;
    public Collection $entries;
}