<?php

namespace wsydney76\package\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Console;
use wsydney76\package\Plugin;
use function aws_crt_log_to_stdout;


class Install extends Migration
{
    public function safeUp(): bool
    {
        return Plugin::getInstance()->migrationService->install();
    }

    public function safeDown(): bool
    {
        return Plugin::getInstance()->migrationService->uninstall();
    }
}