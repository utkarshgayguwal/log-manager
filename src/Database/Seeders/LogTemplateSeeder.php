<?php

namespace UtkarshGayguwal\LogManagement\Database\Seeders;

use UtkarshGayguwal\LogManagement\Models\LogTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LogTemplateSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        LogTemplate::truncate();
        Schema::enableForeignKeyConstraints();

        $logTemplates = [
            [
                'name' => LogTemplate::GENERAL,
                'body' => '{module_name} {action} by {staff_name} on {date} at {time}.',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add project-specific templates here
            // Example:
            // [
            //     'name' => 'user_action',
            //     'body' => 'User {staff_name} {action} {module_name} on {date} at {time}.',
            //     'created_by' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ];

        LogTemplate::insert($logTemplates);
    }
}