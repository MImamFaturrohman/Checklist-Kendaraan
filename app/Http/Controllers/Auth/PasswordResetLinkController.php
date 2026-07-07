<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): mixed
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Kolom email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        $messages = [
            Password::INVALID_USER => 'Email yang Anda masukkan tidak terdaftar dalam sistem.',
            Password::RESET_THROTTLED => 'Permintaan reset password terlalu sering. Silakan tunggu beberapa saat.',
        ];

        if ($status == Password::RESET_LINK_SENT) {
            $successMessage = 'Tautan reset password telah berhasil dikirim ke email Anda. Silakan periksa kotak masuk (atau spam) email Anda.';
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $successMessage,
                ]);
            }
            return back()->with('status', $successMessage);
        }

        $errorMessage = $messages[$status] ?? 'Gagal memproses permintaan reset password.';

        if ($request->expectsJson()) {
            throw ValidationException::withMessages([
                'email' => [$errorMessage],
            ]);
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => $errorMessage]);
    }
}
