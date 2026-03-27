<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReportFilterRequest;
use App\Models\User;
use App\Services\AdminReportService;

class AdminReportController extends Controller
{
    public function index(ReportFilterRequest $request, AdminReportService $reportService)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        return view('admin.report', array_merge(
            ['current' => $current],
            $reportService->build($request->validated())
        ));
    }
}