<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import GoogleLoginButton from '@/Components/GoogleLoginButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In — Business Execution Toolkit" />

        <div class="auth-header">
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-desc">Sign in to access your business execution toolkit</p>
        </div>

        <div v-if="status" class="status-msg">
            {{ status }}
        </div>

        <!-- Google OAuth Button -->
        <div class="social-login">
            <GoogleLoginButton />
        </div>

        <div class="divider">
            <span>or sign in with email</span>
        </div>

        <form @submit.prevent="submit" class="auth-form">
            <div class="field-group">
                <label for="email" class="form-lbl">Email Address</label>
                <input
                    id="email"
                    type="email"
                    class="glass-input"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@company.com"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="field-group mt-4">
                <div class="lbl-row">
                    <label for="password" class="form-lbl">Password</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="forgot-link"
                    >
                        Forgot password?
                    </Link>
                </div>
                <input
                    id="password"
                    type="password"
                    class="glass-input"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-slate-400">Remember this device</span>
                </label>
            </div>

            <button
                type="submit"
                class="submit-btn"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span>Sign In to Toolkit</span>
                <span class="arr">→</span>
            </button>
        </form>

        <div class="register-prompt">
            Don't have an account?
            <Link :href="route('register')" class="reg-link">Create workspace</Link>
        </div>
    </GuestLayout>
</template>

<style scoped>
.auth-header {
  margin-bottom: 24px;
  text-align: center;
}

.auth-title {
  font-size: 22px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: -0.02em;
}

.auth-desc {
  font-size: 13px;
  color: #94a3b8;
  margin-top: 4px;
}

.status-msg {
  margin-bottom: 16px;
  padding: 10px 14px;
  border-radius: 10px;
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #4ade80;
  font-size: 13px;
  text-align: center;
}

.social-login {
  margin-bottom: 20px;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 20px 0;
  color: #64748b;
  font-size: 12px;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.divider span {
  padding: 0 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.field-group {
  display: flex;
  flex-direction: column;
}

.lbl-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-lbl {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
  margin-bottom: 6px;
}

.forgot-link {
  font-size: 12px;
  color: #818cf8;
  text-decoration: none;
  transition: color 0.15s ease;
}

.forgot-link:hover {
  color: #a5b4fc;
}

.glass-input {
  width: 100%;
  padding: 11px 14px;
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
}

.glass-input::placeholder {
  color: #475569;
}

.glass-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
  background: rgba(15, 23, 42, 0.85);
}

.submit-btn {
  margin-top: 24px;
  width: 100%;
  padding: 12px 18px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #4f46e5, #4338ca);
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.45);
}

.arr {
  font-size: 16px;
  transition: transform 0.2s ease;
}

.submit-btn:hover .arr {
  transform: translateX(3px);
}

.register-prompt {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 13px;
  color: #94a3b8;
  text-align: center;
}

.reg-link {
  color: #818cf8;
  font-weight: 600;
  text-decoration: none;
  margin-left: 4px;
}

.reg-link:hover {
  color: #a5b4fc;
}
</style>
