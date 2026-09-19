<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    process: Object,
    steps: Array,
});

const moveUp = (step) => {
    router.post(`/processes/${props.process.id}/steps/${step.id}/move-up`, {}, { preserveScroll: true });
};

const moveDown = (step) => {
    router.post(`/processes/${props.process.id}/steps/${step.id}/move-down`, {}, { preserveScroll: true });
};

const destroyStep = (step) => {
    if (! confirm(`مرحله «${step.name}» حذف شود؟`)) {
        return;
    }

    router.delete(`/processes/${props.process.id}/steps/${step.id}`, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-semibold">مراحل فرآیند «{{ process.name }}»</h1>
                <Link :href="`/processes`" class="text-sm text-gray-500 hover:underline">بازگشت به فرآیندها</Link>
            </div>
            <Link :href="`/processes/${process.id}/steps/create`" class="btn-primary">مرحله جدید</Link>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">ترتیب</th>
                        <th class="px-4 py-2">نام</th>
                        <th class="px-4 py-2">step_key</th>
                        <th class="px-4 py-2">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(step, index) in steps" :key="step.id" class="border-t">
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1">
                                <button
                                    class="text-gray-500 hover:text-gray-800 disabled:opacity-30"
                                    :disabled="index === 0"
                                    @click="moveUp(step)"
                                >
                                    ▲
                                </button>
                                <button
                                    class="text-gray-500 hover:text-gray-800 disabled:opacity-30"
                                    :disabled="index === steps.length - 1"
                                    @click="moveDown(step)"
                                >
                                    ▼
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ step.name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ step.step_key }}</td>
                        <td class="px-4 py-2 space-x-2 space-x-reverse whitespace-nowrap">
                            <Link :href="`/processes/${process.id}/steps/${step.id}/fields`" class="btn-link">فیلدها</Link>
                            <Link :href="`/processes/${process.id}/steps/${step.id}/edit`" class="btn-link">ویرایش</Link>
                            <button class="btn-danger" @click="destroyStep(step)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="steps.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">هیچ مرحله‌ای ثبت نشده است.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
