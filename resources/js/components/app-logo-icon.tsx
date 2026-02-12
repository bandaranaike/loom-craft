import type { ImgHTMLAttributes } from 'react';
import logo from '@/images/logo.png';

export default function AppLogoIcon({
    alt = 'LoomCraft',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img src={logo} alt={alt} {...props} />
    );
}
