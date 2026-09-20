<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\ProcessConditionGroupController;
use App\Http\Controllers\ProcessConditionRuleController;
use App\Http\Controllers\ProcessFieldController;
use App\Http\Controllers\ProcessStepController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\WebhookController;
use App\Models\Bot;
use App\Models\Process;
use App\Models\Submission;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/webhook/{platform}/{botId}', WebhookController::class)->name('webhook');

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'stats' => [
                'processesCount' => Process::where('is_current_version', true)->count(),
                'botsCount' => Bot::count(),
                'submissionsCount' => Submission::count(),
                'submissionsTodayCount' => Submission::whereDate('started_at', today())->count(),
            ],
        ]);
    })->name('dashboard');

    Route::resource('bots', BotController::class)->except(['show']);
    Route::post('/bots/{bot}/test-connection', [BotController::class, 'testConnection'])->name('bots.test-connection');
    Route::post('/bots/{bot}/set-webhook', [BotController::class, 'setWebhook'])->name('bots.set-webhook');

    Route::resource('processes', ProcessController::class)->except(['show']);

    Route::prefix('processes/{process}/steps')->name('processes.steps.')->group(function () {
        Route::get('/', [ProcessStepController::class, 'index'])->name('index');
        Route::get('/create', [ProcessStepController::class, 'create'])->name('create');
        Route::post('/', [ProcessStepController::class, 'store'])->name('store');
        Route::get('/{step}/edit', [ProcessStepController::class, 'edit'])->name('edit');
        Route::put('/{step}', [ProcessStepController::class, 'update'])->name('update');
        Route::delete('/{step}', [ProcessStepController::class, 'destroy'])->name('destroy');
        Route::post('/{step}/move-up', [ProcessStepController::class, 'moveUp'])->name('move-up');
        Route::post('/{step}/move-down', [ProcessStepController::class, 'moveDown'])->name('move-down');
    });

    Route::prefix('processes/{process}/steps/{step}/fields')->name('processes.steps.fields.')->group(function () {
        Route::get('/', [ProcessFieldController::class, 'index'])->name('index');
        Route::get('/create', [ProcessFieldController::class, 'create'])->name('create');
        Route::post('/', [ProcessFieldController::class, 'store'])->name('store');
        Route::get('/{field}/edit', [ProcessFieldController::class, 'edit'])->name('edit');
        Route::put('/{field}', [ProcessFieldController::class, 'update'])->name('update');
        Route::delete('/{field}', [ProcessFieldController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('processes/{process}/conditions')->name('processes.conditions.')->group(function () {
        Route::get('/', [ProcessConditionGroupController::class, 'index'])->name('index');
        Route::post('/groups', [ProcessConditionGroupController::class, 'store'])->name('groups.store');
        Route::delete('/groups/{group}', [ProcessConditionGroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{group}/rules', [ProcessConditionRuleController::class, 'store'])->name('groups.rules.store');
        Route::delete('/groups/{group}/rules/{rule}', [ProcessConditionRuleController::class, 'destroy'])->name('groups.rules.destroy');
    });

    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
