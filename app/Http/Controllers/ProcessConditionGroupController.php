<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConditionGroupRequest;
use App\Models\Process;
use App\Models\ProcessConditionGroup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProcessConditionGroupController extends Controller
{
    public function index(Process $process): Response
    {
        $groups = $process->conditionGroups()
            ->with(['targetStep', 'rules.field'])
            ->orderBy('display_order')
            ->get();

        return Inertia::render('Conditions/Index', [
            'process' => $process,
            'groups' => $groups,
            'steps' => $process->steps()->orderBy('display_order')->get(['id', 'name']),
            'fields' => \App\Models\ProcessField::whereIn('step_id', $process->steps()->pluck('id'))
                ->with('step:id,name')
                ->get(['id', 'step_id', 'label']),
        ]);
    }

    public function store(StoreConditionGroupRequest $request, Process $process): RedirectResponse
    {
        $maxOrder = $process->conditionGroups()->max('display_order');

        $process->conditionGroups()->create([
            'action' => $request->action,
            'target_step_id' => $request->target_step_id,
            'stop_message' => $request->stop_message,
            'display_order' => $maxOrder === null ? 0 : $maxOrder + 1,
        ]);

        return back()->with('success', 'گروه شرط ساخته شد.');
    }

    public function destroy(Process $process, ProcessConditionGroup $group): RedirectResponse
    {
        $group->delete();

        return back()->with('success', 'گروه شرط حذف شد.');
    }
}
