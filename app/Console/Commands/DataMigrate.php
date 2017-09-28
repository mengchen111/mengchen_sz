<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\DataMigrator;

class DataMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:data-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from the old platform to this one';

    protected $dataMigrator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DataMigrator $dataMigrator)
    {
        parent::__construct();

        $this->dataMigrator = $dataMigrator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dataMigrator->migrateAgentListData();
        $this->info('migrate agent list done.');
        $this->dataMigrator->migrateTopUp2AgentHistory();
        $this->info('migrate top up to agent history done.');
        $this->dataMigrator->migrateTopUp2PlayerHistory();
        $this->info('migrate top up to player history done.');
    }
}
