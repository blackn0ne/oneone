<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Calendar, MapPin, Phone, Mail } from 'lucide-vue-next';
import { route } from '@/lib/routes';

interface Business {
    company_name: string | null;
    company_slogan: string | null;
    logo: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
}

interface Service {
    id: number;
    name: string;
    description: string | null;
    duration: number;
    price: number;
    booking_mode: string;
}

interface Props {
    tenantId: string;
    business: Business | null;
    services: Service[];
}

const props = defineProps<Props>();

const companyName = () => props.business?.company_name || 'Наша компания';
</script>

<template>
    <Head :title="companyName()" />

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-950 dark:to-slate-900">
        <!-- Header -->
        <header class="border-b border-slate-200/80 bg-white/80 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/80">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <div class="flex items-center gap-3">
                    <img
                        v-if="business?.logo"
                        :src="business.logo"
                        :alt="companyName()"
                        class="h-10 w-auto object-contain"
                    />
                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-lg font-bold text-primary-foreground">
                        {{ companyName().charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">{{ companyName() }}</h1>
                        <p v-if="business?.company_slogan" class="text-sm text-muted-foreground">
                            {{ business.company_slogan }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="`/set-tenant/${tenantId}`"
                    >
                        <Button variant="outline" size="sm">Панель управления</Button>
                    </Link>
                    <Link
                        v-else
                        :href="`/login?redirect=/dashboard`"
                    >
                        <Button variant="outline" size="sm">Войти</Button>
                    </Link>
                    <Link :href="`/set-tenant/${tenantId}?redirect=/bookings/create`">
                        <Button size="sm">
                            <Calendar class="mr-2 h-4 w-4" />
                            Записаться
                        </Button>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="mx-auto max-w-5xl px-4 py-16 text-center">
            <h2 class="text-3xl font-bold tracking-tight md:text-4xl">
                Добро пожаловать в {{ companyName() }}
            </h2>
            <p v-if="business?.company_slogan" class="mt-4 text-lg text-muted-foreground">
                {{ business.company_slogan }}
            </p>
            <Link :href="`/set-tenant/${tenantId}?redirect=/bookings/create`" class="mt-8 inline-block">
                <Button size="lg" class="gap-2">
                    <Calendar class="h-5 w-5" />
                    Записаться онлайн
                </Button>
            </Link>
        </section>

        <!-- Services -->
        <section class="mx-auto max-w-5xl px-4 py-12">
            <h3 class="mb-8 text-2xl font-semibold">Наши услуги</h3>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="service in services"
                    :key="service.id"
                    class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <h4 class="font-semibold">{{ service.name }}</h4>
                    <p v-if="service.description" class="mt-2 text-sm text-muted-foreground line-clamp-2">
                        {{ service.description }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            {{ service.duration }} мин
                        </span>
                        <span class="font-medium">
                            {{ service.price?.toLocaleString('ru-RU') }} ₽
                        </span>
                    </div>
                    <Link :href="`/set-tenant/${tenantId}?redirect=/bookings/create`" class="mt-4 block">
                        <Button variant="outline" size="sm" class="w-full">Записаться</Button>
                    </Link>
                </div>
            </div>
            <p v-if="services.length === 0" class="py-12 text-center text-muted-foreground">
                Услуги пока не добавлены
            </p>
        </section>

        <!-- Contacts -->
        <section
            v-if="business?.phone || business?.email || business?.address"
            class="mx-auto max-w-5xl px-4 py-12"
        >
            <h3 class="mb-6 text-2xl font-semibold">Контакты</h3>
            <div class="flex flex-wrap gap-6">
                <a
                    v-if="business.phone"
                    :href="`tel:${business.phone}`"
                    class="flex items-center gap-2 text-muted-foreground hover:text-foreground"
                >
                    <Phone class="h-4 w-4" />
                    {{ business.phone }}
                </a>
                <a
                    v-if="business.email"
                    :href="`mailto:${business.email}`"
                    class="flex items-center gap-2 text-muted-foreground hover:text-foreground"
                >
                    <Mail class="h-4 w-4" />
                    {{ business.email }}
                </a>
                <span
                    v-if="business.address"
                    class="flex items-center gap-2 text-muted-foreground"
                >
                    <MapPin class="h-4 w-4" />
                    {{ business.address }}
                </span>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mt-16 border-t border-slate-200 py-8 dark:border-slate-800">
            <div class="mx-auto max-w-5xl px-4 text-center text-sm text-muted-foreground">
                © {{ new Date().getFullYear() }} {{ companyName() }}
            </div>
        </footer>
    </div>
</template>
