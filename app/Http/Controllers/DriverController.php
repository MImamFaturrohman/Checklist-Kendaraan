<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $query = User::where('role', 'driver')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $drivers = $query->paginate(15)->withQueryString();

        return view('admin.drivers', compact('drivers'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'               => $request->name,
            'username'           => $request->username,
            'email'              => $request->username . '@driver.internal',
            'password'           => Hash::make($request->password),
            'role'               => 'driver',
            'email_verified_at'  => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Driver {$user->name} berhasil ditambahkan.",
                'data'    => $user,
            ]);
        }

        return redirect()->route('admin.drivers')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless($user->role === 'driver', 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->username . '@driver.internal',
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data driver berhasil diperbarui.',
                'data'    => $user,
            ]);
        }

        return redirect()->route('admin.drivers')->with('success', 'Data driver diperbarui.');
    }

    public function destroy(User $user, Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        abort_unless($user->role === 'driver', 403);

        $name = $user->name;
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Driver {$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.drivers')->with('success', 'Driver berhasil dihapus.');
    }
}
