<?php

namespace Karla\Console\Commands;

use Karla\Console\Command;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karla:setup:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migration files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('migrate', [
            '--path'     => \realpath(__DIR__ . '/../../../database/migrations'),
            '--realpath' => true,
        ]);
    }
}