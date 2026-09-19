<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    process: Object,
    step: Object,
});

const isEdit = computed(() => !! props.step);

const form = useForm({
    name: props.step?.name ?? '',
});

const submit = () => {
    if (isEdit.value) {
        form.put(`/processes/${props.process.id}/steps/${props.step.id}`);
    } else {
        form.post(`/processes/${props.process.id}/steps`);
    }
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">{{ isEdit ? 'ویرایش مرحله' : 'مرحله جدید' }}</h1>

        <form class="bg-white rounded-md shadow-sm p-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium mb-1">نام</label>
                <input v-model="form.name" type="text" class="form-input" required />
                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <div v-if="isEdit">
                <label class="block text-sm font-medium mb-1">step_key</label>
                <input :value="step.step_key" type="text" class="form-input bg-gray-50 text-gray-500" readonly />
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">ذخیره</button>
                <a :href="`/processes/${process.id}/steps`" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </AdminLayout>
</template>
