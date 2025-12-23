<?php

namespace UtkarshGayguwal\LogManagement\Database\Seeders;

use UtkarshGayguwal\LogManagement\Models\RedirectTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RedirectTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        RedirectTemplate::truncate();
        Schema::enableForeignKeyConstraints();

        // Add project-specific redirect templates here
        // Example templates:
        $data = [
            // Example: [
            //     'name' => 'user_profile',
            //     'path' => '/users/{id}/profile?prev_page=system-logs',
            // ],
            // [
            //     'name' => 'post_edit',
            //     'path' => '/posts/{id}/edit?prev_page=system-logs',
            // ],
            // [
            //     'name' => 'category_list',
            //     'path' => '/categories?page=1&limit=20&slug={slug}',
            // ],
        ];

        foreach ($data as $item) {
            RedirectTemplate::create($item);
        }
    }
}