<?php

namespace App\Http\Controllers;

use App\Repositories\Report\ReportInterface;
use http\Client\Request;
use Illuminate\Support\Facades\Cache;

class ReportsController extends Controller
{
    private $report;

    public function __construct(ReportInterface $report)
    {
        $this->report = $report;
    }

    public function dashboard(Request $request)
    {
        if ($request->refresh) {
            Cache::forget('dashboard-top-stats');
        }

        return Cache::remember('dashboard-top-stats', 3600, function () {
            return $this->report->dashboardData();
        });
    }
}
