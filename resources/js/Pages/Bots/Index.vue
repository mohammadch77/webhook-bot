<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    bots: Array,
});

const platformLabels = {
    telegram: 'تلگرام',
    bale: 'بله',
    rubika: 'روبیکا',
};

const testConnection = (bot) => {
    router.post(`/bots/${bot.id}/test-connection`, {}, { preserveScroll: true });
};

const setWebhook = (bot) => {
    router.post(`/bots/${bot.id}/set-webhook`, {}, { preserveScroll: true });
};

const destroyBot = (bot) => {
    if (! confirm(`ربات «${bot.name}» حذف شود؟`)) {
        return;
    }

    router.delete(`/bots/${bot.id}`, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Bots</h1>
            <Link href="/bots/create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                ربات جدید
            </Link>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">نام</th>
                        <th class="px-4 py-2">پلتفرم</th>
                        <th class="px-4 py-2">وضعیت</th>
                        <th class="px-4 py-2">آدرس webhook</th>
                        <th class="px-4 py-2">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="bot in bots" :key="bot.id" class="border-t">
                        <td class="px-4 py-2">{{ bot.name }}</td>
                        <td class="px-4 py-2">{{ platformLabels[bot.platform] }}</td>
                        <td class="px-4 py-2">
                            <span
                                class="inline-block rounded-full px-2 py-0.5 text-xs"
                                :class="bot.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ bot.status === 'active' ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-500 truncate max-w-xs">{{ bot.webhook_url || '-' }}</td>
                        <td class="px-4 py-2 space-x-2 space-x-reverse whitespace-nowrap">
                            <button class="text-blue-600 hover:underline" @click="testConnection(bot)">تست اتصال</button>
                            <button
                                v-if="bot.platform === 'telegram' || bot.platform === 'bale'"
                                class="text-teal-600 hover:underline"
                                @click="setWebhook(bot)"
                            >
                                تنظیم Webhook
                            </button>
                            <Link :href="`/bots/${bot.id}/edit`" class="text-indigo-600 hover:underline">ویرایش</Link>
                            <button class="text-red-600 hover:underline" @click="destroyBot(bot)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="bots.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">هیچ رباتی ثبت نشده است.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
