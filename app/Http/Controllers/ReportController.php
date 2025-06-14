<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SecurityDevice;
use App\Models\PerformanceEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     * Apply middleware to ensure only admins can access these methods.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role->name !== 'admin') {
                return response()->json(['message' => 'غير مصرح لك بعرض هذه البيانات.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get a summary of all key metrics for the main reports dashboard.
     * GET /api/reports/dashboard-summary
     */
    public function getDashboardSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $incidentSummary = $this->getIncidentsSummary($startDate, $endDate);
        $deviceStatusSummary = $this->getDeviceStatusSummary();
        $guardPerformanceSummary = $this->getGuardPerformanceSummary($startDate, $endDate);
        
        return response()->json([
            'stats' => [
                'total_incidents' => $incidentSummary['total_incidents'],
                'devices_needing_maintenance' => $deviceStatusSummary['needs_maintenance'] + $deviceStatusSummary['out_of_service'],
                'average_evaluation_score' => $guardPerformanceSummary['overall_average_score'],
                'attendance_adherence' => 94, // Placeholder for future implementation
            ],
            'charts' => [
                'incidents_by_type' => $incidentSummary['incidents_by_type'],
                'device_status' => $deviceStatusSummary,
                'top_guards' => $guardPerformanceSummary['top_guards'],
            ]
        ]);
    }

    /**
     * Private helper to get a summary of incidents.
     */
    private function getIncidentsSummary(Carbon $startDate, Carbon $endDate): array
    {
        $incidentsQuery = Incident::whereBetween('created_at', [$startDate, $endDate]);
        $totalIncidents = $incidentsQuery->count();
        $incidentsByType = $incidentsQuery->clone()
            ->join('incident_types', 'incidents.incident_type_id', '=', 'incident_types.id')
            ->select('incident_types.name_ar as type_name', DB::raw('count(incidents.id) as count'))
            ->groupBy('incident_types.name_ar')
            ->orderBy('count', 'desc')
            ->get();
        return ['total_incidents' => $totalIncidents, 'incidents_by_type' => $incidentsByType];
    }

    /**
     * Private helper to get a summary of security device statuses (real-time).
     */
    private function getDeviceStatusSummary(): array
    {
        $statuses = SecurityDevice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');
        return [
            'operational' => $statuses->get('operational', 0),
            'needs_maintenance' => $statuses->get('needs_maintenance', 0),
            'out_of_service' => $statuses->get('out_of_service', 0),
            'under_maintenance' => $statuses->get('under_maintenance', 0),
        ];
    }

    /**
     * Private helper to get a summary of guard performance.
     */
    private function getGuardPerformanceSummary(Carbon $startDate, Carbon $endDate): array
    {
        $evaluationsQuery = PerformanceEvaluation::where('status', 'finalized')
                                                 ->whereBetween('evaluation_date', [$startDate, $endDate]);
        $overallAverageScore = $evaluationsQuery->clone()->avg('overall_score');
        $topGuards = $evaluationsQuery->clone()
            ->join('users', 'performance_evaluations.guard_user_id', '=', 'users.id')
            ->select('users.name as guard_name', DB::raw('avg(performance_evaluations.overall_score) as average_score'))
            ->groupBy('users.name')->orderBy('average_score', 'desc')->limit(5)->get();
        return ['overall_average_score' => round($overallAverageScore, 2), 'top_guards' => $topGuards];
    }
}
