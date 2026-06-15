<?php

namespace App\Http\Controllers;

use App\Models\Role;
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

    public function administratorIndex(Request $request)
    {
        $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => $this->formatAdmin($u));

        return response()->json(['data' => $admins]);
    }

    public function administratorShow(int $id)
    {
        $user = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
            ->with('roles')
            ->findOrFail($id);

        return response()->json($this->formatAdmin($user));
    }

    public function administratorToggleActive(Request $request, int $id)
    {
        if ($request->user()->id === $id) {
            return response()->json(['message' => '無法停用自己的帳號'], 403);
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json(['user' => ['is_active' => $user->is_active]]);
    }

    public function adminUpdate(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,super_admin',
        ]);

        if (request()->user()->id === $id && $request->role === 'admin') {
            return response()->json(['message' => '超級管理員無法修改自己的身分'], 403);
        }

        $user = User::with('roles')->findOrFail($id);
        $user->update(['name' => $request->name]);

        $roleId = Role::where('name', $request->role)->value('id');
        $adminRoleIds = Role::whereIn('name', ['admin', 'super_admin'])->pluck('id');
        $user->roles()->detach($adminRoleIds);
        $user->roles()->attach($roleId);
        $user->load('roles');

        return response()->json(['admin' => $this->formatAdmin($user)]);
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

    public function adminStore(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,super_admin',
        ]);

        $roleId = Role::where('name', $request->role)->value('id');

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ]);

        $user->roles()->attach($roleId);
        $user->load('roles');

        return response()->json(['admin' => $this->formatAdmin($user)], 201);
    }

    private function formatAdmin(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'is_active'  => $user->is_active,
            'role'       => $user->roles->contains('name', 'super_admin') ? 'super_admin' : 'admin',
            'created_at' => $user->created_at,
        ];
    }
}
