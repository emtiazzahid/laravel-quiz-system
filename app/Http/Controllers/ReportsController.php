<?php

namespace App\Http\Controllers;

use App\Repositories\Report\ReportInterface;

class ReportsController extends Controller
{
    private $report;

    public function __construct(ReportInterface $report)
    {
        $this->report = $report;
    }

    public function dashboard()
    {
        return $this->report->dashboardData();
    }
}
