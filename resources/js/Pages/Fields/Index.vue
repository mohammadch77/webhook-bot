<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    process: Object,
    step: Object,
    fields: Array,
});

const typeLabels = {
    text: 'متن',
    number: 'عدد',
    phone: 'شماره تلفن',
    textarea: 'متن بلند',
    select: 'انتخابی',
    boolean: 'بله/خیر',
    file: 'فایل',
    image: 'تصویر',
    date: 'تاریخ',
};

const destroyField = (field) => {
    if (! confirm(`فیلد «${field.label}» حذف شود؟`)) {
        return;
    }

    router.delete(`/processes/${props.process.id}/steps/${props.step.id}/fields/${field.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-semibold">فیلدهای مرحله «{{ step.name }}»</h1>
                <Link :href="`/processes/${process.id}/steps`" class="text-sm text-gray-500 hover:underline">
                    بازگشت به مراحل
                </Link>
            </div>
            <Link :href="`/processes/${process.id}/steps/${step.id}/fields/create`" class="btn-primary">
                فیلد جدید
            </Link>
        </div>

        <div class="bg-white rounded-md shadow-sm overflow-hidden">
            <table class="w-full text-sm text-right">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2">برچسب</th>
                        <th class="px-4 py-2">field_key</th>
                        <th class="px-4 py-2">نوع</th>
                        <th class="px-4 py-2">اجباری</th>
                        <th class="px-4 py-2">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="field in fields" :key="field.id" class="border-t">
                        <td class="px-4 py-2">{{ field.label }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ field.field_key }}</td>
                        <td class="px-4 py-2">{{ typeLabels[field.field_type] }}</td>
                        <td class="px-4 py-2">{{ field.is_required ? 'بله' : 'خیر' }}</td>
                        <td class="px-4 py-2 space-x-2 space-x-reverse whitespace-nowrap">
                            <Link
                                :href="`/processes/${process.id}/steps/${step.id}/fields/${field.id}/edit`"
                                class="btn-link"
                            >
                                ویرایش
                            </Link>
                            <button class="btn-danger" @click="destroyField(field)">حذف</button>
                        </td>
                    </tr>
                    <tr v-if="fields.length === 0">
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">هیچ فیلدی ثبت نشده است.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
