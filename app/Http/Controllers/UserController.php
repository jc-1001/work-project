<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function adminIndex(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $paginated = User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->when(
                $request->search,
                fn($q, $s) =>
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                })
            )
            ->when(
                $request->has('is_active') && $request->is_active !== null,
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)->through(fn($u) => $u->only(['id', 'name', 'email', 'is_active', 'created_at']));

        return response()->json([
            'data'         => $paginated->items(),
            'total'        => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
        ]);
    }

    public function adminShow(int $id)
    {
        return response()->json([
            'user' => User::findOrFail($id)->only(['id', 'name', 'email', 'is_active', 'created_at']),
        ]);
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'is_active' => $user->is_active,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => '資料更新成功',
            'user'    => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }
}
