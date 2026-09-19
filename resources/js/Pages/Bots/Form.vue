<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    bot: Object,
});

const isEdit = computed(() => !! props.bot);

const form = useForm({
    name: props.bot?.name ?? '',
    platform: props.bot?.platform ?? 'telegram',
    token: props.bot?.token ?? '',
});

const submit = () => {
    if (isEdit.value) {
        form.put(`/bots/${props.bot.id}`);
    } else {
        form.post('/bots');
    }
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">{{ isEdit ? 'ویرایش ربات' : 'ربات جدید' }}</h1>

        <form class="bg-white rounded-md shadow-sm p-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium mb-1">نام</label>
                <input v-model="form.name" type="text" class="form-input" required />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">پلتفرم</label>
                <select v-model="form.platform" class="form-input" required>
                    <option value="telegram">تلگرام</option>
                    <option value="bale">بله</option>
                    <option value="rubika">روبیکا</option>
                </select>
                <div v-if="form.errors.platform" class="text-red-600 text-sm mt-1">{{ form.errors.platform }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">توکن</label>
                <input v-model="form.token" type="text" class="form-input" required />
                <div v-if="form.errors.token" class="text-red-600 text-sm mt-1">{{ form.errors.token }}</div>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    ذخیره
                </button>
                <a href="/bots" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </AdminLayout>
</template>
