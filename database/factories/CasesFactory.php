<?php

namespace Database\Factories;

use App\Enums\AuthorizationLevel;
use App\Enums\CaseType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cases>
 */
class CasesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $caseDetails = [
            [
                'name' => 'Theft Investigation',
                'description' => 'An investigation into a reported theft at a commercial property where several high-value items were stolen under suspicious circumstances. The case involves www.xyz.com analyzing surveillance footage and interviewing potential witnesses.'
            ],
            [
                'name' => 'Robbery Case',
                'description' => 'A robbery case involving forced entry into a retail establishment. The suspects fled with cash and goods, and multiple eyewitnesses have come forward. The police are working to identify and apprehend the offenders.'
            ],
            [
                'name' => 'Assault Incident',
                'description' => 'A violent altercation reported in the downtown area where the victim sustained minor injuries. The suspect is known to the victim, and authorities are gathering statements and medical reports. www.example.com'
            ],
            [
                'name' => 'Fraud Detection',
                'description' => 'A case involving financial fraud where unauthorized transactions were conducted across multiple bank accounts. Investigators are tracing the money trail and analyzing electronic communication records.'
            ],
            [
                'name' => 'Missing Person Report',
                'description' => 'A citizen has been reported missing after last being seen samsam.net near the city center. Search efforts are underway, and authorities are appealing to the public for information that may assist in locating the individual.'
            ],
            [
                'name' => 'Drug Trafficking Investigation',
                'description' => 'Authorities are investigating a suspected drug trafficking ring operating in the region. Surveillance and undercover operations are ongoing to identify distributors and gather evidence for prosecution.'
            ],
            [
                'name' => 'Burglary Analysis',
                'description' => 'A residential burglary occurred resulting in the loss of personal property and damages to the premises. Investigators are reviewing neighborhood security footage and forensic evidence from the scene.'
            ],
            [
                'name' => 'Cybercrime Investigation',
                'description' => 'A local business reported a data breach and financial loss due to cyber fraud. Digital forensics teams are analyzing system logs and tracing the origin of the attack to identify the perpetrators.'
            ],
            [
                'name' => 'Homicide Case',
                'description' => 'A homicide is under investigation following the discovery of a deceased individual in a private residence. The forensic team is processing the scene, and investigators are compiling a list of potential suspects.'
            ],
            [
                'name' => 'Vandalism Report',
                'description' => 'Public property was vandalized in a downtown park, including damage to community structures. Authorities are reviewing surveillance footage and seeking assistance from the public to identify the responsible parties.'
            ],
        ];

        $cities = [
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
            'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'
        ];

        $areas = [
            'Downtown', 'Uptown', 'Suburbs', 'Industrial Zone', 'Financial District',
            'Harbor Area', 'Residential Block A', 'Market Street', 'East End', 'West Side'
        ];

        $selected = Arr::random($caseDetails);

        $creator = User::whereIn('role', ['admin', 'investigator'])->inRandomOrder()->first();

        return [
            'case_number' => Str::upper(uniqid('CAS-')),
            'case_name' => $selected['name'],
            'description' => $selected['description'],
            'area' => Arr::random($areas),
            'city' => Arr::random($cities),
            'case_type' => $this->faker->randomElement(CaseType::cases())->value,
            'authorization_level' => $this->faker->randomElement(AuthorizationLevel::cases())->value,
            'created_by' => $creator?->id,
            'created_at' => now(),
        ];
    }
}
