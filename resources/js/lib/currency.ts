export const DEFAULT_CURRENCY = 'LKR';

const toNumber = (amount: string): number => {
    const parsed = Number.parseFloat(amount);

    return Number.isFinite(parsed) ? parsed : 0;
};

export const formatMoney = (amount: string, currency: string): string => {
    const numericAmount = toNumber(amount);

    if (currency === 'LKR') {
        return `Rs. ${numericAmount.toLocaleString('en-LK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numericAmount);
};
