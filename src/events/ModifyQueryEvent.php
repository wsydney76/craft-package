<?php

use craft\elements\db\ElementQueryInterface;
use yii\base\Event;

class ModifyQueryEvent extends Event
{
    public int $packageId;
    public ElementQueryInterface $query;
}