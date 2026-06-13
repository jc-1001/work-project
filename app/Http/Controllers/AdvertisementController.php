<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index()
    {
        $ads = Advertisement::orderBy('created_at', 'desc')->get()
            ->map(fn($ad) => $this->format($ad));

        return response()->json(['data' => $ads]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:100',
            'image'             => 'required|image|max:2048',
            'link_url'          => 'nullable|string|max:500',
            'countdown_seconds' => 'required|integer|min:3|max:120',
            'display_start_at'  => 'required|date',
            'display_end_at'    => 'required|date|after:display_start_at',
            'is_active'         => 'nullable|boolean',
        ]);

        $isActive = $request->boolean('is_active', true);
        $path     = $request->file('image')->store('advertisements', 'public');

        try {
            $ad = DB::transaction(function () use ($validated, $path, $isActive) {
                if ($isActive) {
                    Advertisement::where('is_active', true)->lockForUpdate()->update(['is_active' => false]);
                }
                return Advertisement::create([
                    'title'             => $validated['title'],
                    'image_path'        => $path,
                    'link_url'          => $validated['link_url'] ?? '/shop',
                    'countdown_seconds' => $validated['countdown_seconds'],
                    'display_start_at'  => $validated['display_start_at'],
                    'display_end_at'    => $validated['display_end_at'],
                    'is_active'         => $isActive,
                ]);
            });
        } catch (\Exception $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return response()->json(['data' => $this->format($ad)], 201);
    }

    public function show(int $id)
    {
        $ad = Advertisement::findOrFail($id);

        return response()->json(['data' => $this->format($ad)]);
    }

    public function update(Request $request, int $id)
    {
        $ad = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title'             => 'sometimes|required|string|max:100',
            'image'             => 'sometimes|image|max:2048',
            'link_url'          => 'nullable|string|max:500',
            'countdown_seconds' => 'sometimes|required|integer|min:3|max:120',
            'display_start_at'  => 'sometimes|required|date',
            'display_end_at'    => 'sometimes|required|date|after:display_start_at',
            'is_active'         => 'nullable|boolean',
        ]);

        $oldPath = $ad->image_path;
        $newPath = $request->hasFile('image')
            ? $request->file('image')->store('advertisements', 'public')
            : null;

        unset($validated['image']);

        try {
            DB::transaction(function () use ($ad, $validated, $newPath, $id, $request) {
                if ($newPath) {
                    $validated['image_path'] = $newPath;
                }
                if ($request->has('is_active')) {
                    $newActive = $request->boolean('is_active');
                    if ($newActive) {
                        Advertisement::where('id', '!=', $id)
                            ->where('is_active', true)
                            ->lockForUpdate()
                            ->update(['is_active' => false]);
                    }
                    $validated['is_active'] = $newActive;
                }
                $ad->update($validated);
            });
        } catch (\Exception $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        if ($newPath && $oldPath && str_starts_with($oldPath, 'advertisements/')) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json(['data' => $this->format($ad->fresh())]);
    }

    public function destroy(int $id)
    {
        $ad = Advertisement::findOrFail($id);
        if ($ad->image_path && str_starts_with($ad->image_path, 'advertisements/')) {
            Storage::disk('public')->delete($ad->image_path);
        }
        $ad->delete();

        return response()->json(['message' => '已刪除']);
    }

    public function active()
    {
        $ad = Advertisement::where('is_active', true)
            ->where('display_start_at', '<=', now())
            ->where('display_end_at', '>=', now())
            ->latest()
            ->first();

        return response()->json(['data' => $ad ? $this->format($ad) : null]);
    }

    private function format(Advertisement $ad): array
    {
        return array_merge($ad->toArray(), ['image_url' => $ad->image_url]);
    }
}
