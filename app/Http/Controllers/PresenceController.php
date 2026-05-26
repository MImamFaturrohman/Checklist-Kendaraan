<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $request->user()->markOnline();

        return response()->json([
            'online'       => true,
            'last_seen_at' => now()->toIso8601String(),
        ]);
    }

    public function offline(Request $request): JsonResponse
    {
        User::markOfflineById($request->user()?->id);

        return response()->json([
            'online' => false,
        ]);
    }
}
