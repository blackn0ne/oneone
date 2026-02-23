<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { route } from '@/lib/routes';
import { ref } from 'vue';

interface Settings {
    id: number;
    project_name?: string;
    project_description?: string;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    logo?: string;
    favicon?: string;
    global_currency: string;
    default_language: string;
    bank_transfer_enabled: boolean;
    bank_name?: string;
    bank_account?: string;
    bank_swift?: string;
    bank_iban?: string;
    bank_instructions?: string;
    smtp_host?: string;
    smtp_port?: number;
    smtp_username?: string;
    smtp_password?: string;
    smtp_encryption?: string;
    smtp_from_address?: string;
    smtp_from_name?: string;
    whatsapp_enabled: boolean;
    whatsapp_api_key?: string;
    whatsapp_api_secret?: string;
    whatsapp_phone_number?: string;
    whatsapp_business_id?: string;
    whatsapp_webhook_url?: string;
}

interface Props {
    settings: Settings;
}

const props = defineProps<Props>();

// General settings form
const generalForm = useForm({
    project_name: props.settings.project_name || '',
    project_description: props.settings.project_description || '',
    meta_title: props.settings.meta_title || '',
    meta_description: props.settings.meta_description || '',
    meta_keywords: props.settings.meta_keywords || '',
    logo: props.settings.logo || '',
    favicon: props.settings.favicon || '',
    global_currency: props.settings.global_currency || 'USD',
    default_language: props.settings.default_language || 'ru',
});

// Payment settings form
const paymentForm = useForm({
    bank_transfer_enabled: props.settings.bank_transfer_enabled || false,
    bank_name: props.settings.bank_name || '',
    bank_account: props.settings.bank_account || '',
    bank_swift: props.settings.bank_swift || '',
    bank_iban: props.settings.bank_iban || '',
    bank_instructions: props.settings.bank_instructions || '',
});

// Email settings form
const emailForm = useForm({
    smtp_host: props.settings.smtp_host || '',
    smtp_port: props.settings.smtp_port || 587,
    smtp_username: props.settings.smtp_username || '',
    smtp_password: props.settings.smtp_password || '',
    smtp_encryption: props.settings.smtp_encryption || 'tls',
    smtp_from_address: props.settings.smtp_from_address || '',
    smtp_from_name: props.settings.smtp_from_name || '',
});

// WhatsApp settings form
const whatsappForm = useForm({
    whatsapp_enabled: props.settings.whatsapp_enabled || false,
    whatsapp_api_key: props.settings.whatsapp_api_key || '',
    whatsapp_api_secret: props.settings.whatsapp_api_secret || '',
    whatsapp_phone_number: props.settings.whatsapp_phone_number || '',
    whatsapp_business_id: props.settings.whatsapp_business_id || '',
    whatsapp_webhook_url: props.settings.whatsapp_webhook_url || '',
});

const submitGeneral = () => {
    generalForm.post(route('central.settings.general.update'));
};

const submitPayment = () => {
    paymentForm.post(route('central.settings.payment.update'));
};

const submitEmail = () => {
    emailForm.post(route('central.settings.email.update'));
};

const submitWhatsApp = () => {
    whatsappForm.post(route('central.settings.whatsapp.update'));
};

</script>

<template>
    <Head title="Настройки" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Настройки</h1>
                <p class="text-muted-foreground">
                    Управление настройками платформы
                </p>
            </div>

            <Tabs default-value="general" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger value="general">Общие</TabsTrigger>
                    <TabsTrigger value="payment">Платежи</TabsTrigger>
                    <TabsTrigger value="email">Email</TabsTrigger>
                    <TabsTrigger value="whatsapp">WhatsApp</TabsTrigger>
                </TabsList>

                <!-- General Settings -->
                <TabsContent value="general">
                    <Card>
                        <form @submit.prevent="submitGeneral">
                            <CardHeader>
                                <CardTitle>Общие настройки</CardTitle>
                                <CardDescription>
                                    Основные настройки проекта, SEO и локализация
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="project_name">Название проекта</Label>
                                        <Input
                                            id="project_name"
                                            v-model="generalForm.project_name"
                                            placeholder="Моя платформа"
                                        />
                                        <p v-if="generalForm.errors.project_name" class="text-sm text-destructive">
                                            {{ generalForm.errors.project_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="global_currency">Глобальная валюта</Label>
                                        <Select v-model="generalForm.global_currency">
                                            <SelectTrigger id="global_currency">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="USD">USD</SelectItem>
                                                <SelectItem value="EUR">EUR</SelectItem>
                                                <SelectItem value="RUB">RUB</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="project_description">Описание проекта</Label>
                                    <Textarea
                                        id="project_description"
                                        v-model="generalForm.project_description"
                                        placeholder="Описание вашей платформы..."
                                        rows="3"
                                    />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="logo">Логотип (URL)</Label>
                                        <Input
                                            id="logo"
                                            v-model="generalForm.logo"
                                            placeholder="https://example.com/logo.png"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="favicon">Фавикон (URL)</Label>
                                        <Input
                                            id="favicon"
                                            v-model="generalForm.favicon"
                                            placeholder="https://example.com/favicon.ico"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="meta_title">Meta Title</Label>
                                    <Input
                                        id="meta_title"
                                        v-model="generalForm.meta_title"
                                        placeholder="Заголовок для SEO"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="meta_description">Meta Description</Label>
                                    <Textarea
                                        id="meta_description"
                                        v-model="generalForm.meta_description"
                                        placeholder="Описание для SEO"
                                        rows="2"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="meta_keywords">Meta Keywords</Label>
                                    <Input
                                        id="meta_keywords"
                                        v-model="generalForm.meta_keywords"
                                        placeholder="ключевое, слово, другое"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="default_language">Основной язык</Label>
                                    <Select v-model="generalForm.default_language">
                                        <SelectTrigger id="default_language">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="ru">Русский</SelectItem>
                                            <SelectItem value="en">English</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button type="submit" :disabled="generalForm.processing">
                                    {{ generalForm.processing ? 'Сохранение...' : 'Сохранить' }}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </TabsContent>

                <!-- Payment Settings -->
                <TabsContent value="payment">
                    <Card>
                        <form @submit.prevent="submitPayment">
                            <CardHeader>
                                <CardTitle>Настройки платежей</CardTitle>
                                <CardDescription>
                                    Настройки банковских переводов
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="bank_transfer_enabled"
                                        v-model:checked="paymentForm.bank_transfer_enabled"
                                    />
                                    <Label for="bank_transfer_enabled" class="cursor-pointer">
                                        Включить банковский перевод
                                    </Label>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label for="bank_name">Название банка</Label>
                                        <Input
                                            id="bank_name"
                                            v-model="paymentForm.bank_name"
                                            placeholder="Название банка"
                                        />
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="bank_account">Номер счета</Label>
                                            <Input
                                                id="bank_account"
                                                v-model="paymentForm.bank_account"
                                                placeholder="1234567890"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="bank_swift">SWIFT код</Label>
                                            <Input
                                                id="bank_swift"
                                                v-model="paymentForm.bank_swift"
                                                placeholder="SWIFT"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="bank_iban">IBAN</Label>
                                        <Input
                                            id="bank_iban"
                                            v-model="paymentForm.bank_iban"
                                            placeholder="IBAN"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="bank_instructions">Инструкции для клиентов</Label>
                                        <Textarea
                                            id="bank_instructions"
                                            v-model="paymentForm.bank_instructions"
                                            placeholder="Инструкции по оплате..."
                                            rows="4"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button type="submit" :disabled="paymentForm.processing">
                                    {{ paymentForm.processing ? 'Сохранение...' : 'Сохранить' }}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </TabsContent>

                <!-- Email Settings -->
                <TabsContent value="email">
                    <Card>
                        <form @submit.prevent="submitEmail">
                            <CardHeader>
                                <CardTitle>Настройки Email (SMTP)</CardTitle>
                                <CardDescription>
                                    Настройки SMTP сервера для отправки писем
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="smtp_host">SMTP Host</Label>
                                        <Input
                                            id="smtp_host"
                                            v-model="emailForm.smtp_host"
                                            placeholder="smtp.example.com"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="smtp_port">SMTP Port</Label>
                                        <Input
                                            id="smtp_port"
                                            v-model.number="emailForm.smtp_port"
                                            type="number"
                                            placeholder="587"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="smtp_username">SMTP Username</Label>
                                        <Input
                                            id="smtp_username"
                                            v-model="emailForm.smtp_username"
                                            placeholder="user@example.com"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="smtp_password">SMTP Password</Label>
                                        <Input
                                            id="smtp_password"
                                            v-model="emailForm.smtp_password"
                                            type="password"
                                            placeholder="••••••••"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="smtp_encryption">Шифрование</Label>
                                        <Select v-model="emailForm.smtp_encryption">
                                            <SelectTrigger id="smtp_encryption">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="tls">TLS</SelectItem>
                                                <SelectItem value="ssl">SSL</SelectItem>
                                                <SelectItem value="">Нет</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="smtp_from_address">От (Email)</Label>
                                        <Input
                                            id="smtp_from_address"
                                            v-model="emailForm.smtp_from_address"
                                            type="email"
                                            placeholder="noreply@example.com"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="smtp_from_name">От (Имя)</Label>
                                        <Input
                                            id="smtp_from_name"
                                            v-model="emailForm.smtp_from_name"
                                            placeholder="Моя платформа"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button type="submit" :disabled="emailForm.processing">
                                    {{ emailForm.processing ? 'Сохранение...' : 'Сохранить' }}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </TabsContent>

                <!-- WhatsApp Settings -->
                <TabsContent value="whatsapp">
                    <Card>
                        <form @submit.prevent="submitWhatsApp">
                            <CardHeader>
                                <CardTitle>Настройки WhatsApp Business API</CardTitle>
                                <CardDescription>
                                    Настройки интеграции с WhatsApp Business API
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="whatsapp_enabled"
                                        v-model:checked="whatsappForm.whatsapp_enabled"
                                    />
                                    <Label for="whatsapp_enabled" class="cursor-pointer">
                                        Включить WhatsApp Business API
                                    </Label>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="whatsapp_api_key">API Key</Label>
                                            <Input
                                                id="whatsapp_api_key"
                                                v-model="whatsappForm.whatsapp_api_key"
                                                placeholder="API Key"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="whatsapp_api_secret">API Secret</Label>
                                            <Input
                                                id="whatsapp_api_secret"
                                                v-model="whatsappForm.whatsapp_api_secret"
                                                type="password"
                                                placeholder="••••••••"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="whatsapp_phone_number">Номер телефона</Label>
                                            <Input
                                                id="whatsapp_phone_number"
                                                v-model="whatsappForm.whatsapp_phone_number"
                                                placeholder="+1234567890"
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="whatsapp_business_id">Business ID</Label>
                                            <Input
                                                id="whatsapp_business_id"
                                                v-model="whatsappForm.whatsapp_business_id"
                                                placeholder="Business ID"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="whatsapp_webhook_url">Webhook URL</Label>
                                        <Input
                                            id="whatsapp_webhook_url"
                                            v-model="whatsappForm.whatsapp_webhook_url"
                                            placeholder="https://example.com/webhook/whatsapp"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button type="submit" :disabled="whatsappForm.processing">
                                    {{ whatsappForm.processing ? 'Сохранение...' : 'Сохранить' }}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
