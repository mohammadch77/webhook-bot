<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const admin = computed(() => page.props.auth?.admin);
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);

const links = [
    { label: 'Bots', href: '/bots' },
    { label: 'فرآیندها', href: '/processes' },
    { label: 'ثبت‌ها', href: '/submissions' },
    { label: 'تنظیمات', href: '/settings' },
];

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen flex bg-gray-100" dir="rtl">
        <aside class="w-56 shrink-0 bg-gray-900 text-gray-100 flex flex-col">
            <div class="px-4 py-4 text-lg font-semibold border-b border-gray-800">
                Bot Workflow
            </div>
            <nav class="flex-1 px-2 py-4 space-y-1">
                <Link
                    v-for="link in links"
                    :key="link.href"
                    :href="link.href"
                    class="block rounded-md px-3 py-2 text-sm hover:bg-gray-800"
                >
                    {{ link.label }}
                </Link>
            </nav>
            <div class="px-4 py-3 border-t border-gray-800 text-sm">
                <div class="mb-2 text-gray-400">{{ admin?.name }}</div>
                <button class="text-red-400 hover:text-red-300" @click="logout">خروج</button>
            </div>
        </aside>

        <main class="flex-1 p-6">
            <div v-if="flashSuccess" class="mb-4 rounded-md bg-green-100 px-4 py-2 text-green-800 text-sm">
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="mb-4 rounded-md bg-red-100 px-4 py-2 text-red-800 text-sm">
                {{ flashError }}
            </div>

            <slot />
        </main>
    </div>
</template>
