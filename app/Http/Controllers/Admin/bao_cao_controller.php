<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\User;
use App\Services\AdminReportService;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, AdminReportService $reportService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('quan_tri.bao_cao.index', array_merge(
            ['current' => $current],
            $reportService->build($request->validated())
        ));
    }
}