import { Link } from '@inertiajs/react';
import { privacyPolicy, termsOfService } from '@/routes';

type LegalLinksProps = {
    className?: string;
    linkClassName?: string;
};

export default function LegalLinks({
    className = '',
    linkClassName = '',
}: LegalLinksProps) {
    return (
        <div className={className}>
            <Link href={termsOfService()} className={linkClassName}>
                Terms of Service
            </Link>
            <Link href={privacyPolicy()} className={linkClassName}>
                Privacy Policy
            </Link>
        </div>
    );
}
