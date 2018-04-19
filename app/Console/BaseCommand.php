<?php

namespace App\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BaseCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function logInfo($msg)
    {
        return $this->info(substr(strrchr(static::class, '\\'), 1) . ' ['
            . Carbon::now()->toDateTimeString() . '] [INFO] ' . $msg);
    }

    public function logError($msg)
    {
        return $this->error(substr(strrchr(static::class, '\\'), 1) . ' ['
            . Carbon::now()->toDateTimeString() . '] [ERROR] ' . $msg);
    }

    public function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}