<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\UserController;     
use App\Http\Controllers\RoleController;      
use App\Http\Controllers\BranchController;    
use App\Http\Controllers\ShiftController; 
use App\Http\Controllers\ShiftChangeRequestController;
use App\Http\Controllers\DeviceTypeController;       
use App\Http\Controllers\SecurityDeviceController;   
use App\Http\Controllers\DeviceCheckController;      
use App\Http\Controllers\MaintenanceRequestController; 
use App\Http\Controllers\PerformanceEvaluationController;
use App\Http\Controllers\EvaluationCriterionController;
use App\Http\Controllers\TrainingCategoryController; 
use App\Http\Controllers\TrainingModuleController;   
use App\Http\Controllers\AiAssistantDocumentController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExclusionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\DailyReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public route for login
Route::post('/login', [AuthController::class, 'login'])->name('login');

// All other routes require authentication
Route::middleware('auth:sanctum')->group(function () {
    // User & Auth
    Route::get('/user', [AuthController::class, 'user'])->name('user.details');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change');

    // CRUD Resources
    Route::apiResource('users', UserController::class);
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('incidents', IncidentController::class);
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('device-types', DeviceTypeController::class);
    Route::apiResource('security-devices', SecurityDeviceController::class);
    Route::apiResource('maintenance-requests', MaintenanceRequestController::class);
    Route::apiResource('performance-evaluations', PerformanceEvaluationController::class);
    Route::apiResource('training-categories', TrainingCategoryController::class);
    Route::apiResource('training-modules', TrainingModuleController::class);
    Route::apiResource('ai-assistant-documents', AiAssistantDocumentController::class);
    Route::apiResource('exclusions', ExclusionController::class)->except(['show', 'update']);
    Route::apiResource('templates', TemplateController::class)->except(['create', 'edit', 'show', 'update']);
    Route::apiResource('asset-types', AssetTypeController::class);
    Route::apiResource('assets', AssetController::class);
    Route::apiResource('evaluation-criteria', EvaluationCriterionController::class);
    Route::apiResource('daily-reports', DailyReportController::class)->only(['index', 'store']);


    // Custom Routes
    Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');
    
    Route::post('/shift-change-requests', [ShiftChangeRequestController::class, 'store'])->name('shift-requests.store');
    Route::get('/my-shift-requests', [ShiftChangeRequestController::class, 'indexMyRequests'])->name('shift-requests.my');
    Route::get('/supervisor/shift-requests', [ShiftChangeRequestController::class, 'indexPendingForSupervisor'])->name('supervisor.shift-requests.pending');
    Route::put('/supervisor/shift-requests/{shiftChangeRequest}', [ShiftChangeRequestController::class, 'processRequest'])->name('supervisor.shift-requests.process');

    Route::post('/device-checks', [DeviceCheckController::class, 'store'])->name('device-checks.store');
    
    Route::get('/academy/modules', [TrainingModuleController::class, 'indexForAcademy'])->name('academy.modules.index');
    Route::get('/academy/modules/{trainingModule}', [TrainingModuleController::class, 'showForAcademy'])->name('academy.modules.show');

    Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai.assistant.ask');
    Route::post('/exclusions/check', [ExclusionController::class, 'check'])->name('exclusions.check');

    Route::post('/assets/{asset}/assign', [AssetAssignmentController::class, 'assign'])->name('assets.assign');
    Route::post('/assets/{asset}/return', [AssetAssignmentController::class, 'returnAsset'])->name('assets.return');
    Route::get('/my-assets', [AssetAssignmentController::class, 'myAssets'])->name('assets.my');
    
    Route::get('/reports/dashboard-summary', [ReportController::class, 'getDashboardSummary'])->name('reports.dashboard');

    // Lookups for forms
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
});

