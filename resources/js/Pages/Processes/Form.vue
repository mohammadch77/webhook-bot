<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    process: Object,
    bots: {
        type: Array,
        default: () => [],
    },
    selectedBotIds: {
        type: Array,
        default: () => [],
    },
    hasSubmissions: {
        type: Boolean,
        default: false,
    },
    steps: {
        type: Array,
        default: () => [],
    },
});

const isEdit = computed(() => !! props.process);

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

const platformLabels = {
    telegram: 'تلگرام',
    bale: 'بله',
    rubika: 'روبیکا',
};

const toFormField = (field) => ({
    label: field?.label ?? '',
    field_type: field?.field_type ?? 'text',
    is_required: field?.is_required ?? true,
    options: field?.options ? field.options.map((o) => ({ ...o })) : [],
});

const toFormStep = (step) => ({
    name: step?.name ?? '',
    fields: (step?.fields ?? []).map(toFormField),
});

const form = useForm({
    name: props.process?.name ?? '',
    description: props.process?.description ?? '',
    is_active: props.process?.is_active ?? true,
    bot_ids: [...props.selectedBotIds],
    steps: props.steps.length ? props.steps.map(toFormStep) : [],
});

const slugPreview = computed(() => {
    return props.process?.process_key ?? 'پس از ذخیره تولید می‌شود';
});

const addStep = () => {
    form.steps.push(toFormStep(null));
};

const removeStep = (index) => {
    if (! confirm('این مرحله و همه فیلدهایش حذف شود؟')) {
        return;
    }
    form.steps.splice(index, 1);
};

const addField = (step) => {
    step.fields.push(toFormField(null));
};

const removeField = (step, index) => {
    step.fields.splice(index, 1);
};

const onFieldTypeChange = (field) => {
    if (field.field_type === 'boolean') {
        field.is_required = false;
    }
    if (field.field_type !== 'select') {
        field.options = [];
    } else if (field.options.length === 0) {
        field.options.push({ value: '', label: '' });
    }
};

const addOption = (field) => {
    field.options.push({ value: '', label: '' });
};

const removeOption = (field, index) => {
    field.options.splice(index, 1);
};

const submit = () => {
    if (isEdit.value) {
        form.put(`/processes/${props.process.id}`);
    } else {
        form.post('/processes');
    }
};
</script>

<template>
    <AdminLayout>
        <h1 class="text-xl font-semibold mb-4">{{ isEdit ? 'ویرایش فرآیند' : 'فرآیند جدید' }}</h1>

        <div v-if="isEdit && hasSubmissions" class="mb-4 rounded-md bg-yellow-100 px-4 py-2 text-yellow-800 text-sm">
            این فرآیند دارای ثبت است؛ ذخیره تغییرات یک نسخه جدید می‌سازد.
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="bg-white rounded-md shadow-sm p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">نام</label>
                    <input v-model="form.name" type="text" class="form-input" required />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">process_key</label>
                    <input :value="slugPreview" type="text" class="form-input bg-gray-50 text-gray-500" readonly />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">توضیحات</label>
                    <textarea v-model="form.description" rows="3" class="form-textarea"></textarea>
                    <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" class="form-checkbox" id="is_active" />
                    <label for="is_active" class="text-sm">فعال</label>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">پلتفرم‌ها (ربات‌ها)</label>
                    <div v-if="bots.length === 0" class="text-sm text-gray-400">هیچ رباتی ثبت نشده است.</div>
                    <div v-for="bot in bots" :key="bot.id" class="flex items-center gap-2 py-1">
                        <input
                            :id="`bot-${bot.id}`"
                            v-model="form.bot_ids"
                            type="checkbox"
                            :value="bot.id"
                            class="form-checkbox"
                        />
                        <label :for="`bot-${bot.id}`" class="text-sm">
                            {{ bot.name }} ({{ platformLabels[bot.platform] }})
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-md shadow-sm p-6 space-y-4">
                <h2 class="text-lg font-semibold">مراحل</h2>

                <div
                    v-for="(step, stepIndex) in form.steps"
                    :key="stepIndex"
                    class="border border-gray-200 rounded-md p-4 space-y-3"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 whitespace-nowrap">مرحله {{ stepIndex + 1 }}:</span>
                        <input v-model="step.name" type="text" class="form-input" placeholder="نام مرحله" required />
                        <button type="button" class="btn-danger whitespace-nowrap" @click="removeStep(stepIndex)">
                            حذف مرحله
                        </button>
                    </div>
                    <div v-if="form.errors[`steps.${stepIndex}.name`]" class="text-red-600 text-sm">
                        {{ form.errors[`steps.${stepIndex}.name`] }}
                    </div>

                    <div class="pr-4 space-y-3">
                        <div class="text-sm font-medium text-gray-600">فیلدها</div>

                        <div
                            v-for="(field, fieldIndex) in step.fields"
                            :key="fieldIndex"
                            class="flex flex-wrap items-start gap-2 bg-gray-50 rounded-md p-3"
                        >
                            <div class="flex-1 min-w-[160px]">
                                <input v-model="field.label" type="text" class="form-input" placeholder="برچسب" required />
                                <div
                                    v-if="form.errors[`steps.${stepIndex}.fields.${fieldIndex}.label`]"
                                    class="text-red-600 text-xs mt-1"
                                >
                                    {{ form.errors[`steps.${stepIndex}.fields.${fieldIndex}.label`] }}
                                </div>
                            </div>

                            <select
                                v-model="field.field_type"
                                class="form-select"
                                @change="onFieldTypeChange(field)"
                            >
                                <option v-for="(label, type) in typeLabels" :key="type" :value="type">
                                    {{ label }}
                                </option>
                            </select>

                            <label class="flex items-center gap-1 text-sm whitespace-nowrap">
                                <input
                                    v-model="field.is_required"
                                    type="checkbox"
                                    class="form-checkbox"
                                    :disabled="field.field_type === 'boolean'"
                                />
                                اجباری
                            </label>

                            <button type="button" class="btn-danger" @click="removeField(step, fieldIndex)">حذف</button>

                            <div v-if="field.field_type === 'select'" class="w-full space-y-2 mt-2">
                                <label class="block text-xs font-medium text-gray-600">گزینه‌ها</label>
                                <div v-for="(option, optionIndex) in field.options" :key="optionIndex" class="flex gap-2 items-center">
                                    <input v-model="option.value" type="text" placeholder="value" class="form-input" />
                                    <input v-model="option.label" type="text" placeholder="label" class="form-input" />
                                    <button type="button" class="btn-danger" @click="removeOption(field, optionIndex)">
                                        حذف گزینه
                                    </button>
                                </div>
                                <button type="button" class="btn-secondary" @click="addOption(field)">
                                    + افزودن گزینه
                                </button>
                                <div
                                    v-if="form.errors[`steps.${stepIndex}.fields.${fieldIndex}.options`]"
                                    class="text-red-600 text-xs"
                                >
                                    {{ form.errors[`steps.${stepIndex}.fields.${fieldIndex}.options`] }}
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-secondary" @click="addField(step)">+ افزودن فیلد</button>
                    </div>
                </div>

                <div v-if="form.steps.length === 0" class="text-sm text-gray-400">هیچ مرحله‌ای ثبت نشده است.</div>

                <button type="button" class="btn-secondary" @click="addStep">+ افزودن مرحله</button>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">ذخیره</button>
                <a href="/processes" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </AdminLayout>
</template>
