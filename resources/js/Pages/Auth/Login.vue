<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="login-page">
        <form class="login-card" @submit.prevent="submit">
            <h1>ورود ادمین</h1>

            <label>
                <span>ایمیل</span>
                <input v-model="form.email" type="email" autocomplete="username" required />
            </label>
            <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>

            <label>
                <span>رمز عبور</span>
                <input v-model="form.password" type="password" autocomplete="current-password" required />
            </label>
            <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>

            <label class="remember">
                <input v-model="form.remember" type="checkbox" />
                <span>مرا به خاطر بسپار</span>
            </label>

            <button type="submit" :disabled="form.processing">ورود</button>
        </form>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
}
.login-card {
    background: #fff;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 360px;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
label {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.9rem;
}
input[type='email'],
input[type='password'] {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
}
.remember {
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
}
.error {
    color: #dc2626;
    font-size: 0.8rem;
}
button {
    margin-top: 0.5rem;
    padding: 0.6rem;
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
}
button:disabled {
    opacity: 0.6;
    cursor: default;
}
</style>
