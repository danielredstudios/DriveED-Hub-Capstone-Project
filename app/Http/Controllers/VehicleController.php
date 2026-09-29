<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Branch;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request, School $school)
    {
        $vehicles = Vehicle::where('school_id', $school->id)
            ->with(['category', 'branch'])
            ->get();
            
        $categories = VehicleCategory::where('school_id', $school->id)->get();
        $branches = Branch::where('school_id', $school->id)->get();

        return view('school.admin.vehicles.index', compact('vehicles', 'categories', 'branches', 'school'));
    }

    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                Rule::unique('vehicles', 'license_plate')->where('school_id', $school->id)
            ],
            'category_id' => [
                'required',
                Rule::exists('vehicle_categories', 'id')->where('school_id', $school->id)
            ],
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('school_id', $school->id)
            ],
            'transmission' => 'required|in:manual,automatic',
            'status' => 'required|in:active,maintenance,out_of_service',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $validated['school_id'] = $school->id;

        $vehicle = Vehicle::create($validated);

        if ($request->hasFile('images')) {
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            foreach (array_slice($files, 0, 5) as $image) {
                $path = $image->store('vehicles/images', 'local');
                $vehicle->images()->create([
                    'school_id' => $school->id,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Vehicle added successfully.');
    }

    public function storeCategory(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['school_id'] = $school->id;
        VehicleCategory::create($validated);

        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function updateCategory(Request $request, School $school, VehicleCategory $category)
    {
        // Enforce ownership
        if ($category->school_id !== $school->id) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }
    public function update(Request $request, School $school, Vehicle $vehicle)
    {
        // Ensure vehicle belongs to this school
        if ($vehicle->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'license_plate' => [
                'required',
                'string',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle->id)->where('school_id', $school->id)
            ],
            'category_id' => [
                'required',
                Rule::exists('vehicle_categories', 'id')->where('school_id', $school->id)
            ],
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('school_id', $school->id)
            ],
            'transmission' => 'required|in:manual,automatic',
            'status' => 'required|in:active,maintenance,out_of_service',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('images')) {
            $currentCount = $vehicle->images()->count();
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            
            if ($currentCount + count($files) > 5) {
                return redirect()->back()->with('error', 'Maximum 5 images allowed per vehicle.');
            }

            foreach ($files as $image) {
                $path = $image->store('vehicles/images', 'local');
                $vehicle->images()->create([
                    'school_id' => $school->id,
                    'image_path' => $path,
                ]);
            }
        }

        $vehicle->update($validated);

        return redirect()->back()->with('success', 'Vehicle updated successfully.');
    }

    public function toggleStatus(Request $request, School $school, Vehicle $vehicle)
    {
        if ($vehicle->school_id !== $school->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:active,maintenance,out_of_service',
        ]);

        $vehicle->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function destroy(School $school, Vehicle $vehicle)
    {
        if ($vehicle->school_id !== $school->id) {
            abort(403);
        }

        $vehicle->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully.');
    }

    public function destroyCategory(School $school, VehicleCategory $category)
    {
        if ($category->school_id !== $school->id) {
            abort(403);
        }

        if ($category->vehicles()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete category that has vehicles assigned to it.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
    public function deleteImage(School $school, Vehicle $vehicle, \App\Models\VehicleImage $image)
    {
        if ($image->vehicle_id !== $vehicle->id || $vehicle->school_id !== $school->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($image->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
    }
}
