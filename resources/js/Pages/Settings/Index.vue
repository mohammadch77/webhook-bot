<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    admin_telegram_chat_id: props.settings?.admin_telegram_chat_id ?? '',
});

const submit = () => {
    form.put('/settings');
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">تنظیمات</h1>

        <form class="bg-white rounded-md shadow-sm p-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium mb-1">آیدی تلگرام ادمین برای دریافت اعلان‌ها</label>
                <input v-model="form.admin_telegram_chat_id" type="text" class="form-input" />
                <p class="text-gray-500 text-sm mt-1">برای پیدا کردن آیدی خود به @userinfobot پیام بدید</p>
                <div v-if="form.errors.admin_telegram_chat_id" class="text-red-600 text-sm mt-1">
                    {{ form.errors.admin_telegram_chat_id }}
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    ذخیره
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
