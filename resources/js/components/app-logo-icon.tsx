import type { ImgHTMLAttributes } from 'react';
import darkLogo from '@/images/logo-dark.png';
import lightLogo from '@/images/logo.png';
import { cn } from '@/lib/utils';

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
