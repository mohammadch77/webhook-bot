<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    process: Object,
    groups: Array,
    steps: Array,
    fields: Array,
});

const actionLabels = {
    show_step: 'نمایش مرحله',
    skip_step: 'رد کردن مرحله',
    jump_to_step: 'پرش به مرحله',
    stop: 'توقف',
};

const operators = ['=', '!=', '>', '<', '>=', '<='];

const groupForm = useForm({
    action: 'show_step',
    target_step_id: '',
    stop_message: '',
});

const createGroup = () => {
    groupForm.post(`/processes/${props.process.id}/conditions/groups`, {
        preserveScroll: true,
        onSuccess: () => groupForm.reset(),
    });
};

const destroyGroup = (group) => {
    if (! confirm('این گروه شرط حذف شود؟')) {
        return;
    }
    router.delete(`/processes/${props.process.id}/conditions/groups/${group.id}`, { preserveScroll: true });
};

const ruleForms = reactive({});

const getRuleForm = (groupId) => {
    if (! ruleForms[groupId]) {
        ruleForms[groupId] = useForm({
            field_id: '',
            operator: '=',
            value: '',
        });
    }
    return ruleForms[groupId];
};

const addRule = (group) => {
    const form = getRuleForm(group.id);
    form.post(`/processes/${props.process.id}/conditions/groups/${group.id}/rules`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const destroyRule = (group, rule) => {
    if (! confirm('این شرط حذف شود؟')) {
        return;
    }
    router.delete(`/processes/${props.process.id}/conditions/groups/${group.id}/rules/${rule.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mb-4">
            <h1 class="text-xl font-semibold">شرط‌های فرآیند «{{ process.name }}»</h1>
            <Link href="/processes" class="text-sm text-gray-500 hover:underline">بازگشت به فرآیندها</Link>
        </div>

        <div class="bg-white rounded-md shadow-sm p-6 max-w-2xl mb-6 space-y-3">
            <h2 class="font-medium">افزودن گروه شرط جدید</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm mb-1">اکشن</label>
                    <select v-model="groupForm.action" class="form-select">
                        <option value="show_step">نمایش مرحله</option>
                        <option value="skip_step">رد کردن مرحله</option>
                        <option value="jump_to_step">پرش به مرحله</option>
                        <option value="stop">توقف</option>
                    </select>
                </div>
                <div v-if="groupForm.action !== 'stop'">
                    <label class="block text-sm mb-1">مرحله هدف</label>
                    <select v-model="groupForm.target_step_id" class="form-select">
                        <option value="">انتخاب کنید</option>
                        <option v-for="step in steps" :key="step.id" :value="step.id">{{ step.name }}</option>
                    </select>
                    <div v-if="groupForm.errors.target_step_id" class="text-red-600 text-sm mt-1">
                        {{ groupForm.errors.target_step_id }}
                    </div>
                </div>
                <div v-else class="col-span-2">
                    <label class="block text-sm mb-1">پیام توقف</label>
                    <input v-model="groupForm.stop_message" type="text" class="form-input" />
                    <div v-if="groupForm.errors.stop_message" class="text-red-600 text-sm mt-1">
                        {{ groupForm.errors.stop_message }}
                    </div>
                </div>
            </div>
            <button class="btn-primary" @click="createGroup">افزودن گروه</button>
        </div>

        <div v-for="group in groups" :key="group.id" class="bg-white rounded-md shadow-sm p-6 max-w-3xl mb-4">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm">
                    <span class="font-medium">{{ actionLabels[group.action] }}</span>
                    <span v-if="group.target_step" class="text-gray-500"> → {{ group.target_step.name }}</span>
                    <span v-if="group.stop_message" class="text-gray-500"> — «{{ group.stop_message }}»</span>
                </div>
                <button class="btn-danger" @click="destroyGroup(group)">حذف گروه</button>
            </div>

            <table class="w-full text-sm text-right mb-3">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-1">فیلد</th>
                        <th class="px-3 py-1">عملگر</th>
                        <th class="px-3 py-1">مقدار</th>
                        <th class="px-3 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="rule in group.rules" :key="rule.id" class="border-t">
                        <td class="px-3 py-1">{{ rule.field?.label }}</td>
                        <td class="px-3 py-1">{{ rule.operator }}</td>
                        <td class="px-3 py-1">{{ rule.value }}</td>
                        <td class="px-3 py-1">
                            <button class="btn-danger" @click="destroyRule(group, rule)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="group.rules.length === 0">
                        <td colspan="4" class="px-3 py-2 text-center text-gray-400">بدون شرط (AND بین شروط)</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs mb-1">فیلد</label>
                    <select v-model="getRuleForm(group.id).field_id" class="form-select">
                        <option value="">انتخاب کنید</option>
                        <option v-for="field in fields" :key="field.id" :value="field.id">
                            {{ field.step?.name }} / {{ field.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1">عملگر</label>
                    <select v-model="getRuleForm(group.id).operator" class="form-select">
                        <option v-for="op in operators" :key="op" :value="op">{{ op }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1">مقدار</label>
                    <input v-model="getRuleForm(group.id).value" type="text" class="form-input" />
                </div>
                <button class="btn-secondary" @click="addRule(group)">افزودن شرط</button>
            </div>
        </div>

        <div v-if="groups.length === 0" class="text-gray-400 text-sm">هیچ گروه شرطی ثبت نشده است.</div>
    </AdminLayout>
</template>
