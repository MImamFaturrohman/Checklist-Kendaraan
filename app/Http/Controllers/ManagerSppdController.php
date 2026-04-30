<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ManagerSppdController extends Controller
{
    private const PDF_GENERATION_FAILED = '__sppd_pdf_generation_failed__';

    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private function resolveManagerSppdPerPage(Request $request): int
    {
        $n = (int) $request->query('per_page', 12);

        return in_array($n, self::PER_PAGE_OPTIONS, true) ? $n : 12;
    }

    public function index(Request $request): View|Response
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        $perPage = $this->resolveManagerSppdPerPage($request);
        $q = trim((string) $request->query('q', ''));

        $pendingQuery = Sppd::query()
            ->where('status', Sppd::STATUS_PENDING_MANAGER)
            ->with(['user:id,name,username', 'tolls', 'fuels'])
            ->orderByDesc('created_at');

        $historyQuery = Sppd::query()
            ->whereIn('status', [Sppd::STATUS_APPROVED, Sppd::STATUS_REJECTED, Sppd::STATUS_COMPLETED])
            ->with(['user:id,name,username', 'approver:id,name'])
            ->orderByDesc('updated_at');

        if ($q !== '') {
            $like = '%'.$q.'%';
            $pendingQuery->where(function ($w) use ($like) {
                $w->where('nama_driver', 'like', $like)
                    ->orWhere('keperluan_dinas', 'like', $like)
                    ->orWhere('no_kendaraan', 'like', $like);
            });
            $historyQuery->where(function ($w) use ($like) {
                $w->where('nama_driver', 'like', $like)
                    ->orWhere('keperluan_dinas', 'like', $like)
                    ->orWhere('no_kendaraan', 'like', $like);
            });
        }

        $pending = $pendingQuery
            ->paginate($perPage, ['*'], 'pending_page')
            ->onEachSide(0)
            ->withQueryString();

        $history = $historyQuery
            ->paginate($perPage, ['*'], 'history_page')
            ->onEachSide(0)
            ->withQueryString();

        $view = view('manager.sppd', compact('pending', 'history', 'q', 'perPage'));

        if ($request->header('X-VMS-SPPD-Fragment') === '1') {
            return response($view->fragment('manager-sppd-body'));
        }

        return $view;
    }

    public function show(Sppd $sppd): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);

        $sppd->load(['user:id,name,username', 'tolls', 'fuels', 'approver:id,name', 'adminVerifier:id,name', 'rejector:id,name']);

        return response()->json([
            'sppd' => $sppd->toDetailArray(),
        ]);
    }

    public function approve(Sppd $sppd): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        abort_unless($sppd->status === Sppd::STATUS_PENDING_MANAGER, 422);

        try {
            DB::transaction(function () use ($sppd) {
                $sppd->update([
                    'status' => Sppd::STATUS_APPROVED,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'rejection_note' => null,
                    'rejected_at' => null,
                    'rejected_by' => null,
                ]);
                $sppd->refresh()->load(['tolls', 'fuels', 'user', 'approver', 'adminVerifier']);
                $pdfPath = $this->buildAndStorePdf($sppd);
                if (! $pdfPath) {
                    throw new \RuntimeException(self::PDF_GENERATION_FAILED);
                }
                if ($sppd->pdf_path && $sppd->pdf_path !== $pdfPath) {
                    Storage::disk('public')->delete($sppd->pdf_path);
                }
                $sppd->update(['pdf_path' => $pdfPath]);
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === self::PDF_GENERATION_FAILED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembuatan PDF gagal, penyetujuan dibatalkan. Pastikan ekstensi PHP gd aktif, jalankan php artisan storage:link, dan folder storage/app/public dapat ditulis. Cek juga log aplikasi.',
                ], 422);
            }
            Log::error('SPPD manager approve failed', ['e' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui laporan.',
            ], 500);
        } catch (\Throwable $e) {
            Log::error('SPPD manager approve failed', ['e' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui laporan.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekap SPPD disetujui dan PDF telah dibuat.',
        ]);
    }

    /**
     * Untuk rekaman lama: disetujui tetapi pdf_path kosong karena generate gagal saat itu.
     */
    public function regeneratePdf(Sppd $sppd): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        abort_unless(
            in_array($sppd->status, [Sppd::STATUS_APPROVED, Sppd::STATUS_COMPLETED], true),
            422
        );
        abort_if($sppd->pdf_path && Storage::disk('public')->exists($sppd->pdf_path), 422);

        $sppd->load(['tolls', 'fuels', 'user', 'approver', 'adminVerifier']);
        $pdfPath = $this->buildAndStorePdf($sppd);
        if (! $pdfPath) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PDF. Pastikan ekstensi PHP gd aktif dan storage dapat ditulis.',
            ], 422);
        }
        if ($sppd->pdf_path && $sppd->pdf_path !== $pdfPath) {
            Storage::disk('public')->delete($sppd->pdf_path);
        }
        $sppd->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'success' => true,
            'message' => 'PDF berhasil dibuat. Driver dapat mengunduh dari daftar rekap SPPD.',
        ]);
    }

    public function reject(Request $request, Sppd $sppd): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'manager', 403);
        abort_unless($sppd->status === Sppd::STATUS_PENDING_MANAGER, 422);

        $data = $request->validate([
            'rejection_note' => 'required|string|max:5000',
        ]);

        $sppd->update([
            'status' => Sppd::STATUS_REJECTED,
            'rejection_note' => $data['rejection_note'],
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rekap SPPD ditolak.',
        ]);
    }

    private function buildAndStorePdf(Sppd $sppd): ?string
    {
        try {
            $sppd->load(['tolls', 'fuels', 'user', 'approver', 'adminVerifier']);
            $pdf = Pdf::loadView('sppd.pdf', ['sppd' => $sppd]);
            $fileName = 'sppd_'.$sppd->id.'_'.now()->format('Ymd_His').'.pdf';
            $path = 'sppd/pdf/'.$fileName;
            Storage::disk('public')->put($path, $pdf->output());

            return $path;
        } catch (\Throwable $e) {
            Log::warning('SPPD PDF generation failed', [
                'message' => $e->getMessage(),
                'sppd_id' => $sppd->id,
            ]);

            return null;
        }
    }
}
