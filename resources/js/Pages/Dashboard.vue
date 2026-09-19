<script setup>
import { router } from '@inertiajs/vue3';

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="dashboard">
        <h1>داشبورد</h1>
        <button @click="logout">خروج</button>
    </div>
</template>

<style scoped>
.dashboard {
    padding: 2rem;
}
</style>
