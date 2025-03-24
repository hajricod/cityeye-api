<?php

namespace Database\Seeders;

use App\Models\Cases;
use App\Models\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = Cases::all();

        foreach ($cases as $case) {
            Report::factory(rand(1, 5))->create([
                'case_id' => $case->id,
            ]);
        }
    }
}
