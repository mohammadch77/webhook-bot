<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    processes: Array,
});

const platformLabels = {
    telegram: 'تلگرام',
    bale: 'بله',
    rubika: 'روبیکا',
};

const destroyProcess = (process) => {
    if (! confirm(`فرآیند «${process.name}» حذف شود؟`)) {
        return;
    }

    router.delete(`/processes/${process.id}`, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">فرآیندها</h1>
            <Link href="/processes/create" class="btn-primary">فرآیند جدید</Link>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">نام</th>
                        <th class="px-4 py-2">نسخه</th>
                        <th class="px-4 py-2">فعال</th>
                        <th class="px-4 py-2">پلتفرم‌ها</th>
                        <th class="px-4 py-2">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="process in processes" :key="process.id" class="border-t">
                        <td class="px-4 py-2">{{ process.name }}</td>
                        <td class="px-4 py-2">v{{ process.version }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="inline-block rounded-full px-2 py-0.5 text-xs"
                                :class="process.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ process.is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-500">
                            <span v-if="process.bots?.length">
                                {{ process.bots.map((b) => `${b.name} (${platformLabels[b.platform]})`).join('، ') }}
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="px-4 py-2 space-x-2 space-x-reverse whitespace-nowrap">
                            <Link :href="`/processes/${process.id}/edit`" class="btn-link">ویرایش</Link>
                            <button class="btn-danger" @click="destroyProcess(process)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="processes.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">هیچ فرآیندی ثبت نشده است.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
