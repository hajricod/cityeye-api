<?php

namespace Database\Factories;

use App\Models\Cases;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evidence>
 */
class EvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['text', 'image']);

        $textDescriptions = [
            'Suspect fingerprints found on the window.',
            'CCTV footage retrieved from nearby store.',
            'Blood sample collected from the scene.',
            'Footprints leading away from the location.',
            'Weapon recovered near the crime scene.'
        ];

        // Generate image if type is 'image'
        $filePath = null;
        if ($type === 'image') {
            // Generate unique image name
            $imageName = Str::random(40) . '.jpg';

            // Correct Windows path handling
            $storagePath = storage_path('app/public/evidences/' . $imageName);

            // Create directory if missing
            if (!File::exists(dirname($storagePath))) {
                File::makeDirectory(dirname($storagePath), 0755, true);
            }

            // Create dummy image using GD
            $image = imagecreatetruecolor(640, 480);
            $bgColor = imagecolorallocate($image, rand(100,255), rand(100,255), rand(100,255));
            imagefill($image, 0, 0, $bgColor);
            imagejpeg($image, $storagePath, 90);
            imagedestroy($image);

            // Confirm file created
            if (file_exists($storagePath)) {
                $filePath = 'evidences/' . $imageName;
            } else {
                Log::error("Failed to create image at: $storagePath");
            }
        }

        return [
            'case_id' => Cases::factory(),
            'type' => $type,
            'description' => $type === 'text' ? $this->faker->randomElement($textDescriptions) : null,
            'file_path' => $filePath,
            'remarks' => $this->faker->sentence(8),
            'uploaded_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
