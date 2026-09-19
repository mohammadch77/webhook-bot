<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessRequest;
use App\Http\Requests\UpdateProcessRequest;
use App\Models\Bot;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProcessController extends Controller
{
    public function index(): Response
    {
        $processes = Process::query()
            ->where('is_current_version', true)
            ->withCount('submissions')
            ->with('bots:id,name,platform')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Processes/Index', [
            'processes' => $processes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Processes/Form', [
            'process' => null,
            'bots' => Bot::orderBy('name')->get(['id', 'name', 'platform']),
            'selectedBotIds' => [],
        ]);
    }

    public function store(StoreProcessRequest $request): RedirectResponse
    {
        $process = DB::transaction(function () use ($request) {
            $process = Process::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'created_by_admin_id' => Auth::id(),
            ]);

            $process->syncBots($request->input('bot_ids', []));

            return $process;
        });

        return redirect()->route('processes.index')->with('success', 'فرآیند با موفقیت ساخته شد.');
    }

    public function edit(Process $process): Response
    {
        return Inertia::render('Processes/Form', [
            'process' => $process,
            'bots' => Bot::orderBy('name')->get(['id', 'name', 'platform']),
            'selectedBotIds' => $process->bots()->pluck('bots.id'),
            'hasSubmissions' => $process->submissions()->exists(),
        ]);
    }

    public function update(UpdateProcessRequest $request, Process $process): RedirectResponse
    {
        $hasSubmissions = $process->submissions()->exists();

        if (! $hasSubmissions) {
            $process->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
            ]);

            $process->syncBots($request->input('bot_ids', []));

            return redirect()->route('processes.index')->with('success', 'فرآیند با موفقیت به‌روزرسانی شد.');
        }

        DB::transaction(function () use ($request, $process) {
            $process->update(['is_current_version' => false]);

            $newProcess = Process::create([
                'name' => $request->name,
                'process_key' => $process->process_key,
                'version' => $process->version + 1,
                'is_current_version' => true,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'created_by_admin_id' => Auth::id(),
            ]);

            $newProcess->syncBots($request->input('bot_ids', []));
        });

        return redirect()->route('processes.index')->with('success', 'نسخه جدید ساخته شد.');
    }

    public function destroy(Process $process): RedirectResponse
    {
        if ($process->submissions()->exists()) {
            $process->delete();

            return back()->with('success', 'فرآیند حذف شد (soft delete به دلیل وجود ثبت‌ها).');
        }

        DB::transaction(function () use ($process) {
            $stepIds = $process->steps()->withTrashed()->pluck('id');

            \App\Models\ProcessConditionRule::whereIn(
                'group_id',
                $process->conditionGroups()->pluck('id')
            )->delete();
            $process->conditionGroups()->delete();

            \App\Models\ProcessField::whereIn('step_id', $stepIds)->withTrashed()->forceDelete();
            $process->steps()->withTrashed()->forceDelete();

            $process->processPlatforms()->delete();
            $process->forceDelete();
        });

        return back()->with('success', 'فرآیند به‌طور کامل حذف شد.');
    }
}
