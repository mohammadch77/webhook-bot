<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConditionRuleRequest;
use App\Models\Process;
use App\Models\ProcessConditionGroup;
use App\Models\ProcessConditionRule;
use Illuminate\Http\RedirectResponse;

class ProcessConditionRuleController extends Controller
{
    public function store(StoreConditionRuleRequest $request, Process $process, ProcessConditionGroup $group): RedirectResponse
    {
        $group->rules()->create($request->validated());

        return back()->with('success', 'شرط اضافه شد.');
    }

    public function destroy(Process $process, ProcessConditionGroup $group, ProcessConditionRule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('success', 'شرط حذف شد.');
    }
}
