import type { ImgHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';
import darkLogo from '@/images/logo-dark.png';
import lightLogo from '@/images/logo.png';

export default function AppLogoIcon({
    alt = 'LoomCraft',
    className,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <>
            <img src={lightLogo} alt={alt} className={cn(className, 'dark:hidden')} {...props} />
            <img src={darkLogo} alt={alt} className={cn(className, 'hidden dark:block')} {...props} />
        </>
    );
}
