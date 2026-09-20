<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
});

const cards = [
    { key: 'processesCount', label: 'تعداد کل فرآیندها' },
    { key: 'botsCount', label: 'تعداد کل ربات‌ها' },
    { key: 'submissionsCount', label: 'تعداد کل ثبت‌ها' },
    { key: 'submissionsTodayCount', label: 'ثبت‌های امروز' },
];
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">داشبورد</h1>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div
                v-for="card in cards"
                :key="card.key"
                class="bg-white rounded-md shadow-sm p-6 text-center"
            >
                <div class="text-3xl font-bold text-indigo-600">{{ stats[card.key] }}</div>
                <div class="text-sm text-gray-500 mt-2">{{ card.label }}</div>
            </div>
        </div>
    </AdminLayout>
</template>
