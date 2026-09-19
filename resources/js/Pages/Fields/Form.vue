<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    process: Object,
    step: Object,
    field: Object,
});

const isEdit = computed(() => !! props.field);

const form = useForm({
    label: props.field?.label ?? '',
    field_type: props.field?.field_type ?? 'text',
    is_required: props.field?.is_required ?? true,
    options: props.field?.options ?? [],
});

watch(
    () => form.field_type,
    (type) => {
        if (type === 'boolean') {
            form.is_required = false;
        }
        if (type !== 'select') {
            form.options = [];
        } else if (form.options.length === 0) {
            form.options.push({ value: '', label: '' });
        }
    },
);

const addOption = () => {
    form.options.push({ value: '', label: '' });
};

const removeOption = (index) => {
    form.options.splice(index, 1);
};

const submit = () => {
    const url = isEdit.value
        ? `/processes/${props.process.id}/steps/${props.step.id}/fields/${props.field.id}`
        : `/processes/${props.process.id}/steps/${props.step.id}/fields`;

    if (isEdit.value) {
        form.put(url);
    } else {
        form.post(url);
    }
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">{{ isEdit ? 'ویرایش فیلد' : 'فیلد جدید' }}</h1>

        <form class="bg-white rounded-md shadow-sm p-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="block text-sm font-medium mb-1">برچسب</label>
                <input v-model="form.label" type="text" class="form-input" required />
                <div v-if="form.errors.label" class="text-red-600 text-sm mt-1">{{ form.errors.label }}</div>
            </div>

            <div v-if="isEdit">
                <label class="block text-sm font-medium mb-1">field_key</label>
                <input :value="field.field_key" type="text" class="form-input bg-gray-50 text-gray-500" readonly />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">نوع فیلد</label>
                <select v-model="form.field_type" class="form-select">
                    <option value="text">متن</option>
                    <option value="number">عدد</option>
                    <option value="phone">شماره تلفن</option>
                    <option value="textarea">متن بلند</option>
                    <option value="select">انتخابی</option>
                    <option value="boolean">بله/خیر</option>
                    <option value="file">فایل</option>
                    <option value="image">تصویر</option>
                    <option value="date">تاریخ</option>
                </select>
                <div v-if="form.errors.field_type" class="text-red-600 text-sm mt-1">{{ form.errors.field_type }}</div>
            </div>

            <div class="flex items-center gap-2">
                <input
                    v-model="form.is_required"
                    type="checkbox"
                    class="form-checkbox"
                    id="is_required"
                    :disabled="form.field_type === 'boolean'"
                />
                <label for="is_required" class="text-sm">اجباری</label>
            </div>

            <div v-if="form.field_type === 'select'" class="space-y-2">
                <label class="block text-sm font-medium">گزینه‌ها</label>
                <div v-for="(option, index) in form.options" :key="index" class="flex gap-2 items-center">
                    <input v-model="option.value" type="text" placeholder="value" class="form-input" />
                    <input v-model="option.label" type="text" placeholder="label" class="form-input" />
                    <button type="button" class="btn-danger" @click="removeOption(index)">حذف</button>
                </div>
                <button type="button" class="btn-secondary" @click="addOption">افزودن گزینه</button>
                <div v-if="form.errors.options" class="text-red-600 text-sm mt-1">{{ form.errors.options }}</div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">ذخیره</button>
                <a :href="`/processes/${process.id}/steps/${step.id}/fields`" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </AdminLayout>
</template>
