<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessRequest;
use App\Http\Requests\UpdateProcessRequest;
use App\Models\Bot;
use App\Models\Process;
use App\Models\ProcessConditionRule;
use App\Models\ProcessField;
use App\Models\ProcessStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'steps' => [],
        ]);
    }

    public function store(StoreProcessRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $process = Process::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'created_by_admin_id' => Auth::id(),
            ]);

            $process->syncBots($request->input('bot_ids', []));

            $this->saveSteps($process, $request->input('steps', []));
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
            'steps' => $process->steps()->with('fields')->orderBy('display_order')->get(),
        ]);
    }

    public function update(UpdateProcessRequest $request, Process $process): RedirectResponse
    {
        $hasSubmissions = $process->submissions()->exists();

        if (! $hasSubmissions) {
            DB::transaction(function () use ($request, $process) {
                $process->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'is_active' => $request->boolean('is_active'),
                ]);

                $process->syncBots($request->input('bot_ids', []));

                $stepIds = $process->steps()->pluck('id');
                $fieldIds = ProcessField::whereIn('step_id', $stepIds)->pluck('id');

                ProcessConditionRule::whereIn('field_id', $fieldIds)->delete();
                ProcessField::whereIn('step_id', $stepIds)->forceDelete();
                $process->steps()->forceDelete();

                $this->saveSteps($process, $request->input('steps', []));
            });

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

            foreach ($process->processPlatforms as $platform) {
                $newProcess->processPlatforms()->create(['bot_id' => $platform->bot_id]);
            }

            $newProcess->syncBots($request->input('bot_ids', []));

            $this->saveSteps($newProcess, $request->input('steps', []));
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

            ProcessConditionRule::whereIn(
                'group_id',
                $process->conditionGroups()->pluck('id')
            )->delete();
            $process->conditionGroups()->delete();

            ProcessField::whereIn('step_id', $stepIds)->withTrashed()->forceDelete();
            $process->steps()->withTrashed()->forceDelete();

            $process->processPlatforms()->delete();
            $process->forceDelete();
        });

        return back()->with('success', 'فرآیند به‌طور کامل حذف شد.');
    }

    /**
     * Create steps and their fields for a process, generating unique
     * step_key/field_key values from the submitted names/labels.
     */
    private function saveSteps(Process $process, array $steps): void
    {
        $usedStepKeys = [];

        foreach ($steps as $stepIndex => $stepData) {
            $stepKey = $this->uniqueKey(Str::slug($stepData['name']), $usedStepKeys);
            $usedStepKeys[] = $stepKey;

            $step = $process->steps()->create([
                'step_key' => $stepKey,
                'name' => $stepData['name'],
                'display_order' => $stepIndex,
            ]);

            $usedFieldKeys = [];

            foreach ($stepData['fields'] ?? [] as $fieldIndex => $fieldData) {
                $fieldKey = $this->uniqueKey(Str::slug($fieldData['label']), $usedFieldKeys);
                $usedFieldKeys[] = $fieldKey;

                $step->fields()->create([
                    'field_key' => $fieldKey,
                    'label' => $fieldData['label'],
                    'field_type' => $fieldData['field_type'],
                    'is_required' => $fieldData['field_type'] === 'boolean'
                        ? false
                        : (bool) ($fieldData['is_required'] ?? false),
                    'options' => $fieldData['field_type'] === 'select'
                        ? ($fieldData['options'] ?? [])
                        : null,
                    'display_order' => $fieldIndex,
                ]);
            }
        }
    }

    private function uniqueKey(string $base, array $used): string
    {
        $key = $base;
        $suffix = 1;

        while (in_array($key, $used, true)) {
            $key = "{$base}-{$suffix}";
            $suffix++;
        }

        return $key;
    }
}
