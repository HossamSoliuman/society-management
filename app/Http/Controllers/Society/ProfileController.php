<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class ProfileController extends Controller
{
    public function show()
    {
        $society = Society::with('documents')->firstOrFail();

        return view('society.profile.show', compact('society'));
    }

    public function edit()
    {
        $society = Society::firstOrFail();

        return view('society.profile.edit', compact('society'));
    }

    public function update(Request $request)
    {
        $society = Society::firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'society_code' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'rera_number' => 'nullable|string|max:255',
            'building_type' => 'nullable|string|max:255',
            'year_established' => 'nullable|integer|min:1900|max:2100',
            'wings_count' => 'nullable|integer|min:0',
            'blocks_count' => 'nullable|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'primary_mobile' => 'nullable|string|max:20',
            'primary_email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'office_timings' => 'nullable|string|max:255',
            'management_type' => 'nullable|string|max:255',
            'committee_members_count' => 'nullable|integer|min:0',
            'audit_type' => 'nullable|string|max:255',
            'financial_year' => 'nullable|string|max:255',
            'maintenance_collection_day' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
            'about' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $this->storeImage($request->file('logo'), 'logo', 200, 200);
        }

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->storeImage($request->file('photo'), 'photo', 800, 600);
        }

        unset($validated['logo'], $validated['photo']);

        $society->update($validated);

        return redirect()->route('society.profile')->with('success', 'Society profile updated successfully.');
    }

    /**
     * Store an uploaded image, resizing with Intervention Image when the
     * package is available, otherwise persisting the original file as-is.
     */
    private function storeImage($file, string $prefix, int $width, int $height): string
    {
        if (class_exists(ImageManager::class)) {
            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                $image->cover($width, $height);

                $filename = 'society/'.$prefix.'_'.uniqid().'.jpg';
                Storage::disk('public')->put($filename, (string) $image->toJpeg(85));

                return $filename;
            } catch (Throwable $e) {
                // Fall through to storing the original file.
            }
        }

        return $file->store('society', 'public');
    }
}
