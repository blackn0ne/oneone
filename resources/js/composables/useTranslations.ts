import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useTranslations() {
    const page = usePage();
    
    const translations = computed<Record<string, string>>(() => {
        return (page.props.translations as Record<string, string>) || {};
    });

    const t = (key: string, fallback?: string): string => {
        return translations.value[key] || fallback || key;
    };

    return {
        translations,
        t,
    };
}
