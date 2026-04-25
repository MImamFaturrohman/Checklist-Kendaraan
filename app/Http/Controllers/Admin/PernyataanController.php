<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pernyataan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PernyataanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $rows = Pernyataan::query()->orderBy('urutan')->orderBy('id')->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $data = $request->validate([
            'isi_pernyataan' => 'required|string|max:5000',
            'urutan' => 'nullable|integer|min:0|max:65535',
        ]);

        if (! isset($data['urutan'])) {
            $data['urutan'] = (int) (Pernyataan::query()->max('urutan') ?? 0) + 1;
        }

        $row = Pernyataan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pernyataan ditambahkan.',
            'data' => $row,
        ], 201);
    }

    public function update(Request $request, Pernyataan $pernyataan): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $data = $request->validate([
            'isi_pernyataan' => 'required|string|max:5000',
            'urutan' => 'required|integer|min:0|max:65535',
        ]);

        $pernyataan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Pernyataan diperbarui.',
            'data' => $pernyataan->fresh(),
        ]);
    }

    public function destroy(Pernyataan $pernyataan): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $pernyataan->delete();

        return response()->json(['success' => true, 'message' => 'Pernyataan dihapus.']);
    }
}
