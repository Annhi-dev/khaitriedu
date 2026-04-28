<?php

namespace App\Http\Controllers;

use App\Models\Certificate;

class CertificateController extends Controller
{
    public function index()
    {
        $user = $this->sessionUser();

        if (! $user) {
            return redirect()->route('login');
        }

        $certificates = Certificate::where('user_id', $user->id)
            ->with('course')
            ->orderBy('issued_at', 'desc')
            ->get();

        return view('chung_chi.index', compact('user', 'certificates'));
    }

    public function show($id)
    {
        $user = $this->sessionUser();
        $cert = Certificate::with('course')->find($id);

        if (! $user || ! $cert || $cert->user_id !== $user->id) {
            return redirect()->route('certificates.index')->with('error', 'Chứng chỉ không tồn tại.');
        }

        return view('chung_chi.show', compact('cert', 'user'));
    }
}
