import { usePage } from '@inertiajs/react';
import type { HTMLAttributes } from 'react';
import darkLogo from '@/images/logo-dark.png';
import lightLogo from '@/images/logo.png';
import natureSeal from '@/images/brand/natures-nature-seal.png';
import natureWordmark from '@/images/brand/natures-nature-wordmark.png';
import { cn } from '@/lib/utils';
import type { SharedData } from '@/types';

type AppLogoIconProps = HTMLAttributes<HTMLElement> & {
    alt?: string;
    natureVariant?: 'wordmark' | 'seal';
};

export default function AppLogoIcon({ alt = 'LoomCraft', className, natureVariant = 'wordmark', ...props }: AppLogoIconProps) {
    const { site } = usePage<SharedData>().props;

    if (site.key === 'naturesnature') {
        return <img src={natureVariant === 'seal' ? natureSeal : natureWordmark} alt={alt} className={cn(className, 'object-contain')} {...props} />;
    }

    return (
        <>
            <img src={lightLogo} alt={alt} className={cn(className, 'dark:hidden')} {...props} />
            <img src={darkLogo} alt={alt} className={cn(className, 'hidden dark:block')} {...props} />
        </>
    );
}
