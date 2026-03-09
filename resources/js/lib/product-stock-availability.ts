export type ProductStockAvailability = {
    availableQuantity: number | null;
    productionTimeDays: number | null;
    exceedsAvailableStock: boolean;
    stockDelayMessage: string | null;
};

export const buildStockDelayMessage = (
    productionTimeDays: number | null,
): string => {
    if (productionTimeDays !== null) {
        return `This quantity is not currently in stock. Your order will require additional production time and is expected to take about ${productionTimeDays} days.`;
    }

    return 'This quantity is not currently in stock. Your order will require additional production time.';
};

export const resolveProductStockAvailability = (
    requestedQuantity: number,
    availableQuantity: number | null,
    productionTimeDays: number | null,
): ProductStockAvailability => {
    const exceedsAvailableStock =
        availableQuantity !== null && requestedQuantity > availableQuantity;

    return {
        availableQuantity,
        productionTimeDays,
        exceedsAvailableStock,
        stockDelayMessage: exceedsAvailableStock
            ? buildStockDelayMessage(productionTimeDays)
            : null,
    };
};
