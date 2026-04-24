<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    const DEFAULT_PASSWORD = 'ADC@2025';
    const MANAGED_ROLES    = ['driver', 'pic_kendaraan'];

    public function portal(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        // --- Kendaraan ---
        $kQuery = Kendaraan::orderBy('nomor_kendaraan');
        if ($ks = $request->input('ks')) {
            $kQuery->where(function ($q) use ($ks) {
                $q->where('nomor_kendaraan', 'like', "%{$ks}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$ks}%");
            });
        }
        $kendaraans = $kQuery->paginate(10, ['*'], 'kp')->withQueryString();

        // --- Users ---
        $uQuery = User::whereIn('role', self::MANAGED_ROLES)->orderBy('name');
        if ($us = $request->input('us')) {
            $uQuery->where(function ($q) use ($us) {
                $q->where('name', 'like', "%{$us}%")
                  ->orWhere('username', 'like', "%{$us}%");
            });
        }
        if ($rf = $request->input('role_filter')) {
            $uQuery->where('role', $rf);
        }
        $users = $uQuery->paginate(15, ['*'], 'up')->withQueryString();

        $stats = [
            'total_kendaraan' => Kendaraan::count(),
            'total_driver'    => User::where('role', 'driver')->count(),
            'total_pic'       => User::where('role', 'pic_kendaraan')->count(),
        ];

        $defaultPassword = self::DEFAULT_PASSWORD;

        return view('admin.portal-manajemen-administrasi',
            compact('kendaraans', 'users', 'stats', 'defaultPassword'));
    }

    /* ── API: AJAX list kendaraan ─────────────────────────────────────── */
    public function apiKendaraan(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $q = Kendaraan::orderBy('nomor_kendaraan');
        if ($s = $request->input('search')) {
            $q->where(function ($x) use ($s) {
                $x->where('nomor_kendaraan', 'like', "%{$s}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$s}%");
            });
        }
        $pp   = min((int) ($request->input('per_page', 10)), 100);
        $page = $q->paginate($pp);

        return response()->json([
            'data'         => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page'    => $page->lastPage(),
            'total'        => $page->total(),
            'per_page'     => $page->perPage(),
        ]);
    }

    /* ── API: AJAX list users ─────────────────────────────────────────── */
    public function apiUsers(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $q = User::whereIn('role', self::MANAGED_ROLES)->orderBy('name');
        if ($s = $request->input('search')) {
            $q->where(function ($x) use ($s) {
                $x->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }
        if ($rf = $request->input('role_filter')) {
            $q->where('role', $rf);
        }
        $pp   = min((int) ($request->input('per_page', 15)), 100);
        $page = $q->paginate($pp);

        return response()->json([
            'data'         => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page'    => $page->lastPage(),
            'total'        => $page->total(),
            'per_page'     => $page->perPage(),
        ]);
    }

    /* ── Create user ──────────────────────────────────────────────────── */
    public function storeUser(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:driver,pic_kendaraan',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'username'          => $request->username,
            'email'             => $request->username . '@internal.adc',
            'password'          => Hash::make($request->password),
            'role'              => $request->role,
            'email_verified_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$user->name} berhasil ditambahkan.",
                'data'    => $user,
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'User berhasil ditambahkan.');
    }

    /* ── Update user ──────────────────────────────────────────────────── */
    public function updateUser(Request $request, User $user)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless(in_array($user->role, self::MANAGED_ROLES), 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:driver,pic_kendaraan',
        ]);

        $data = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->username . '@internal.adc',
            'role'     => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diperbarui.',
                'data'    => $user->fresh(),
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'User diperbarui.');
    }

    /* ── Delete user ──────────────────────────────────────────────────── */
    public function destroyUser(User $user, Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless(in_array($user->role, self::MANAGED_ROLES), 403);

        $name = $user->name;
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.portal-manajemen')->with('success', 'User dihapus.');
    }
}
