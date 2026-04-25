<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BidangController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $tree = Bidang::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->get()
            ->map(fn (Bidang $b) => $this->serializeNode($b));

        return response()->json(['data' => $tree]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'parent_id' => 'nullable|exists:bidangs,id',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (! empty($data['parent_id'])) {
            $parent = Bidang::query()->find($data['parent_id']);
            if (! $parent || $parent->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Sub-bidang hanya boleh di bawah bidang utama.',
                ]);
            }
        }

        $bidang = Bidang::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Bidang disimpan.',
            'data' => $this->serializeNode($bidang->load('children')),
        ], 201);
    }

    public function update(Request $request, Bidang $bidang): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $data = $request->validate([
            'nama' => 'required|string|max:200',
            'parent_id' => 'nullable|exists:bidangs,id',
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? $bidang->sort_order;

        if ($bidang->children()->exists()) {
            $data['parent_id'] = null;
        }

        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== null) {
            if ((int) $data['parent_id'] === (int) $bidang->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Tidak valid.',
                ]);
            }
            $parent = Bidang::query()->find($data['parent_id']);
            if (! $parent || $parent->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Sub-bidang hanya boleh di bawah bidang utama.',
                ]);
            }
            if ($bidang->children()->exists()) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Bidang yang memiliki sub tidak dapat dijadikan sub.',
                ]);
            }
        }

        $bidang->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Bidang diperbarui.',
            'data' => $this->serializeNode($bidang->fresh()->load('children')),
        ]);
    }

    public function destroy(Bidang $bidang): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        if ($bidang->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Hapus sub-bidang terlebih dahulu.',
            ], 422);
        }

        try {
            $bidang->delete();
        } catch (QueryException) {
            return response()->json([
                'success' => false,
                'message' => 'Bidang tidak dapat dihapus karena masih dipakai pada permohonan.',
            ], 422);
        }

        return response()->json(['success' => true, 'message' => 'Bidang dihapus.']);
    }

    private function serializeNode(Bidang $b): array
    {
        $children = $b->relationLoaded('children')
            ? $b->children->map(fn (Bidang $c) => $this->serializeNode($c))->values()->all()
            : [];

        return [
            'id' => $b->id,
            'nama' => $b->nama,
            'parent_id' => $b->parent_id,
            'sort_order' => $b->sort_order,
            'children' => $children,
        ];
    }
}
