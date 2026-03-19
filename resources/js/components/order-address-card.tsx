type OrderAddress = {
    type: 'shipping' | 'billing';
    full_name: string;
    line1: string;
    line2: string | null;
    city: string;
    region: string | null;
    postal_code: string | null;
    country_code: string;
    phone: string | null;
};

type OrderAddressCardProps = {
    address: OrderAddress;
    className?: string;
};

const addressLabel = (type: OrderAddress['type']) =>
    type === 'shipping' ? 'Shipping address' : 'Billing address';

export default function OrderAddressCard({
    address,
    className = 'rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm',
}: OrderAddressCardProps) {
    return (
        <div className={className}>
            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                {addressLabel(address.type)}
            </p>
            <p className="mt-3 font-semibold text-(--welcome-strong)">
                {address.full_name}
            </p>
            <p className="text-(--welcome-body-text)">
                {address.line1}
                {address.line2 ? `, ${address.line2}` : ''}
            </p>
            <p className="text-(--welcome-body-text)">
                {address.city}
                {address.region ? `, ${address.region}` : ''}{' '}
                {address.postal_code ?? ''}
            </p>
            <p className="text-(--welcome-body-text)">
                {address.country_code}
            </p>
        </div>
    );
}
