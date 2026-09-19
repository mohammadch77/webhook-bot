<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    submissions: Array,
    processes: Array,
    filters: Object,
});

const platformLabels = {
    telegram: 'تلگرام',
    bale: 'بله',
    rubika: 'روبیکا',
};

const statusLabels = {
    in_progress: 'در حال انجام',
    completed: 'تکمیل‌شده',
    stopped: 'متوقف‌شده',
    expired: 'منقضی‌شده',
};

const filters = reactive({
    process_id: props.filters?.process_id ?? '',
    platform: props.filters?.platform ?? '',
    status: props.filters?.status ?? '',
});

watch(filters, () => {
    router.get('/submissions', filters, { preserveState: true, replace: true });
}, { deep: true });
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">ثبت‌ها</h1>

        <div class="bg-white rounded-md shadow-sm p-4 mb-4 flex gap-3">
            <select v-model="filters.process_id" class="form-select">
                <option value="">همه فرآیندها</option>
                <option v-for="process in processes" :key="process.id" :value="process.id">{{ process.name }}</option>
            </select>
            <select v-model="filters.platform" class="form-select">
                <option value="">همه پلتفرم‌ها</option>
                <option value="telegram">تلگرام</option>
                <option value="bale">بله</option>
                <option value="rubika">روبیکا</option>
            </select>
            <select v-model="filters.status" class="form-select">
                <option value="">همه وضعیت‌ها</option>
                <option value="in_progress">در حال انجام</option>
                <option value="completed">تکمیل‌شده</option>
                <option value="stopped">متوقف‌شده</option>
                <option value="expired">منقضی‌شده</option>
            </select>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">فرآیند</th>
                        <th class="px-4 py-2">پلتفرم</th>
                        <th class="px-4 py-2">کاربر</th>
                        <th class="px-4 py-2">وضعیت</th>
                        <th class="px-4 py-2">شروع</th>
                        <th class="px-4 py-2">پایان</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="submission in submissions"
                        :key="submission.id"
                        class="border-t hover:bg-gray-50 cursor-pointer"
                        @click="router.visit(`/submissions/${submission.id}`)"
                    >
                        <td class="px-4 py-2">{{ submission.process?.name }}</td>
                        <td class="px-4 py-2">{{ platformLabels[submission.platform] }}</td>
                        <td class="px-4 py-2">{{ submission.username || '-' }}</td>
                        <td class="px-4 py-2">{{ statusLabels[submission.status] }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ submission.started_at }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ submission.completed_at || '-' }}</td>
                    </tr>
                    <tr v-if="submissions.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">هیچ ثبتی یافت نشد.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
