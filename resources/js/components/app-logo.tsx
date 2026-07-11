import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';
import type { SharedData } from '@/types';

export default function AppLogo() {
    const { site } = usePage<SharedData>().props;

    return (
        <div className="flex aspect-square items-center justify-center overflow-hidden">
            <AppLogoIcon natureVariant={site.key === 'naturesnature' ? 'seal' : 'wordmark'} className="h-12 w-auto object-contain" />
        </div>
    );
}
