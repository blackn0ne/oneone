<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { vMaska } from 'maska/vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';


// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

// ─── Phone ────────────────────────────────────────────────────────────────────

// phone — отображаемое значение с маской (+7 (999) 999-99-99)
// phoneRaw — чистые цифры для отправки на бэкенд (79991234567)
const phone = ref('');
const phoneRaw = ref('');

/**
 * Обработчик события maska для получения чистых цифр
 */
function handleMaska(event: CustomEvent<{ masked: string; unmasked: string }>): void {
    phoneRaw.value = event.detail.unmasked;
}
</script>

<template>
    <AuthBase
        title="Вход в систему"
        description="Введите номер телефона и пароль для входа"
    >
        <Head title="Log in" />

        <p
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </p>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            class="flex flex-col gap-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6">

                <!-- Phone -->
                <div class="grid gap-2">
                    <Label for="phone">Номер телефона</Label>
                    <Input
                        id="phone"
                        v-model="phone"
                        v-maska="{ mask: '+7 (###) ###-##-##', eager: true }"
                        type="tel"
                        placeholder="+7 (999) 999-99-99"
                        autocomplete="tel"
                        :tabindex="1"
                        required
                        autofocus
                        @maska="handleMaska"
                    />
                    <!-- Скрытый input с чистыми цифрами для бэкенда -->
                    <input type="hidden" name="phone" :value="phoneRaw" />
                    <InputError :message="errors.phone" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Пароль</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            :tabindex="5"
                            class="text-sm"
                        >
                            Забыли пароль?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Пароль"
                        autocomplete="current-password"
                        :tabindex="2"
                        required
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember me -->
                <Label for="remember" class="flex items-center gap-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Запомнить меня</span>
                </Label>

                <!-- Submit -->
                <Button
                    type="submit"
                    class="mt-4 w-full"
                    data-test="login-button"
                    :tabindex="4"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Войти
                </Button>

            </div>

            <!-- Register link -->
            <p v-if="canRegister" class="text-center text-sm text-muted-foreground">
                Нет аккаунта?
                <TextLink :href="register()" :tabindex="5">Зарегистрироваться</TextLink>
            </p>
        </Form>
    </AuthBase>
</template>