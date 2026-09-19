<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    submission: Object,
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
</script>

<template>
    <AdminLayout>
        <div class="mb-4">
            <h1 class="text-xl font-semibold">جزئیات ثبت</h1>
            <Link href="/submissions" class="text-sm text-gray-500 hover:underline">بازگشت به ثبت‌ها</Link>
        </div>

        <div class="bg-white rounded-md shadow-sm p-6 max-w-2xl mb-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">فرآیند</dt>
                    <dd>{{ submission.process?.name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">ربات</dt>
                    <dd>{{ submission.bot?.name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">پلتفرم</dt>
                    <dd>{{ platformLabels[submission.platform] }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">کاربر</dt>
                    <dd>{{ submission.username || '-' }} ({{ submission.external_user_id }})</dd>
                </div>
                <div>
                    <dt class="text-gray-500">وضعیت</dt>
                    <dd>{{ statusLabels[submission.status] }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">شروع</dt>
                    <dd>{{ submission.started_at }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">پایان</dt>
                    <dd>{{ submission.completed_at || '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden max-w-2xl">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">فیلد</th>
                        <th class="px-4 py-2">مقدار</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="value in submission.values" :key="value.id" class="border-t">
                        <td class="px-4 py-2">
                            <span class="text-gray-400">{{ value.field?.step?.name }} /</span>
                            {{ value.field?.label }}
                        </td>
                        <td class="px-4 py-2">{{ value.value ?? '-' }}</td>
                    </tr>
                    <tr v-if="submission.values.length === 0">
                        <td colspan="2" class="px-4 py-6 text-center text-gray-400">مقداری ثبت نشده است.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
