<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Category, Department, Faq, Log, Priority, Status, Ticket, TicketMessage, User};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Category::factory(10)->create();
        Department::factory(10)->create();
        Faq::factory(30)->create();
        Priority::factory(3)->create();
        Status::factory(5)->create();
        User::factory(30)->create();
        Ticket::factory(1000)->create();
        TicketMessage::factory(3000)->create();
        Log::factory(500)->create();
    }
}
