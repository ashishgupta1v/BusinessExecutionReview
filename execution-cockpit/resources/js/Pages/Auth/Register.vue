<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import GoogleLoginButton from '@/Components/GoogleLoginButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create Workspace — Business Execution Toolkit" />

        <div class="auth-header">
            <h1 class="auth-title">Get Started Free</h1>
            <p class="auth-desc">Create your workspace in under 30 seconds</p>
        </div>

        <!-- Google OAuth Button -->
        <div class="social-login">
            <GoogleLoginButton />
        </div>

        <div class="divider">
            <span>or sign up with email</span>
        </div>

        <form @submit.prevent="submit" class="auth-form">
            <div class="field-group">
                <label for="name" class="form-lbl">Full Name</label>
                <input
                    id="name"
                    type="text"
                    class="glass-input"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Alex Morgan"
                />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div class="field-group mt-4">
                <label for="email" class="form-lbl">Work Email</label>
                <input
                    id="email"
                    type="email"
                    class="glass-input"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="alex@company.com"
                />
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="field-group mt-4">
                <label for="password" class="form-lbl">Password</label>
                <input
                    id="password"
                    type="password"
                    class="glass-input"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="At least 8 characters"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="field-group mt-4">
                <label for="password_confirmation" class="form-lbl">Confirm Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    class="glass-input"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Re-enter password"
                />
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <button
                type="submit"
                class="submit-btn"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span>Create Workspace</span>
                <span class="arr">→</span>
            </button>
        </form>

        <div class="login-prompt">
            Already have an account?
            <Link :href="route('login')" class="login-link">Sign in</Link>
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

.form-lbl {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
  margin-bottom: 6px;
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

.login-prompt {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 13px;
  color: #94a3b8;
  text-align: center;
}

.login-link {
  color: #818cf8;
  font-weight: 600;
  text-decoration: none;
  margin-left: 4px;
}

.login-link:hover {
  color: #a5b4fc;
}
</style>
