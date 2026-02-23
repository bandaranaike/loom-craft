import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <div className="flex aspect-square items-center justify-center overflow-hidden">
            <AppLogoIcon className="h-12 w-auto object-contain" />
        </div>
    );
}
