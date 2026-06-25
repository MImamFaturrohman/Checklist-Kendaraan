<?php

namespace App\Http\Controllers;

use App\Support\AdminTablePagination;
use App\Support\TableSort;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    const DEFAULT_PASSWORD = 'ADCVMS@2026';

    /** Role yang bisa di-CRUD superadmin — superadmin TIDAK boleh dikelola lewat portal ini. */
    const MANAGED_ROLES = ['driver', 'pic_kendaraan', 'manager', 'admin'];

    public function portal(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        // --- Kendaraan ---
        $kQuery = Kendaraan::orderBy('nomor_kendaraan');
        if ($ks = $request->input('ks')) {
            $kQuery->where(function ($q) use ($ks) {
                $q->where('nomor_kendaraan', 'like', "%{$ks}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$ks}%");
            });
        }
        $kendaraans = $kQuery->paginate(AdminTablePagination::resolvePerPage($request->input('per_page'), 10), ['*'], 'page')->onEachSide(0)->withQueryString();

        // --- Users (semua selain superadmin dalam MANAGED_ROLES) ---
        $uQuery = User::query()->whereIn('role', self::MANAGED_ROLES)->orderBy('name');
        if ($us = $request->input('us')) {
            $uQuery->where(function ($q) use ($us) {
                $q->where('name', 'like', "%{$us}%")
                  ->orWhere('username', 'like', "%{$us}%");
            });
        }
        if (($rf = $request->input('role_filter')) && in_array($rf, self::MANAGED_ROLES, true)) {
            $uQuery->where('role', $rf);
        }
        $users = $uQuery->paginate(AdminTablePagination::resolvePerPage($request->input('per_page'), 10), ['*'], 'page')->onEachSide(0)->withQueryString();

        $stats = [
            'total_kendaraan' => Kendaraan::count(),
            'total_driver'    => User::where('role', 'driver')->count(),
            'total_pic'       => User::where('role', 'pic_kendaraan')->count(),
            'total_manager'   => User::where('role', 'manager')->count(),
            'total_admin'     => User::where('role', 'admin')->count(),
            /** User yang muncul di tab Manajemen User (tanpa superadmin) */
            'total_portal_users' => User::whereIn('role', self::MANAGED_ROLES)->count(),
        ];

        $defaultPassword = self::DEFAULT_PASSWORD;

        return view('admin.portal-manajemen-administrasi',
            compact('kendaraans', 'users', 'stats', 'defaultPassword'));
    }

    private const KENDARAAN_SORT_ALLOWED = [
        'nomor_kendaraan'  => 'nomor_kendaraan',
        'jenis_kendaraan'  => 'jenis_kendaraan',
        'bidang'           => 'bidang',
        'km_saat_ini'      => 'km_saat_ini',
        'status_kendaraan' => 'status_kendaraan',
    ];

    private const USER_SORT_ALLOWED = [
        'name'     => 'name',
        'username' => 'username',
        'role'     => 'role',
    ];

    /* ── API: AJAX list kendaraan ─────────────────────────────────────── */
    public function apiKendaraan(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $q = Kendaraan::query();
        if ($s = $request->input('search')) {
            $q->where(function ($x) use ($s) {
                $x->where('nomor_kendaraan', 'like', "%{$s}%")
                  ->orWhere('jenis_kendaraan', 'like', "%{$s}%");
            });
        }
        TableSort::apply($q, $request, self::KENDARAAN_SORT_ALLOWED, function ($x) {
            $x->orderBy('nomor_kendaraan');
        });
        $page = $q->paginate(AdminTablePagination::resolvePerPage($request->input('per_page'), 10));

        $sortState = TableSort::current($request, self::KENDARAAN_SORT_ALLOWED);

        return response()->json(array_merge(
            ['data' => $page->items(), 'sort' => $sortState['sort'] ?? null, 'dir' => $sortState['dir'] ?? null],
            AdminTablePagination::jsonMeta($page, route('api.admin.portal.kendaraan'))
        ));
    }

    /* ── API: AJAX list users ─────────────────────────────────────────── */
    public function apiUsers(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $q = User::query()->whereIn('role', self::MANAGED_ROLES);
        if ($s = $request->input('search')) {
            $q->where(function ($x) use ($s) {
                $x->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }
        if (($rf = $request->input('role_filter')) && in_array($rf, self::MANAGED_ROLES, true)) {
            $q->where('role', $rf);
        }
        TableSort::apply($q, $request, self::USER_SORT_ALLOWED, function ($x) {
            $x->orderBy('name');
        });
        $page = $q->paginate(AdminTablePagination::resolvePerPage($request->input('per_page'), 10));

        $data = collect($page->items())->map(function (User $user) {
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'username'     => $user->username,
                'role'         => $user->role,
                'is_online'    => $user->isOnline(),
                'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            ];
        })->values();

        $userSortState = TableSort::current($request, self::USER_SORT_ALLOWED);

        return response()->json(array_merge(
            ['data' => $data, 'sort' => $userSortState['sort'] ?? null, 'dir' => $userSortState['dir'] ?? null],
            AdminTablePagination::jsonMeta($page, route('api.admin.portal.users'))
        ));
    }

    /* ── Create user ──────────────────────────────────────────────────── */
    public function storeUser(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $rolesRule = implode(',', self::MANAGED_ROLES);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:'.$rolesRule,
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
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        abort_if($user->role === 'superadmin', 403);
        abort_unless(in_array($user->role, self::MANAGED_ROLES, true), 403);

        $rolesRule = implode(',', self::MANAGED_ROLES);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:'.$rolesRule,
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
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        abort_if($user->role === 'superadmin', 403);
        abort_unless(in_array($user->role, self::MANAGED_ROLES, true), 403);

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
