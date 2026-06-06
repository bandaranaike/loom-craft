export type ProductStockAvailability = {
    availableQuantity: number | null;
    productionTimeDays: number | null;
    shortageQuantity: number;
    preparationSetupDays: number;
    preparationWeavingDays: number;
    preparationBufferDays: number;
    preparationTimeDays: number;
    exceedsAvailableStock: boolean;
    stockDelayMessage: string | null;
};

export type ProductPreparationConfig = {
    setup_days: number;
    buffer_rate: number;
    default_weaving_days: number;
};

const defaultPreparationConfig: ProductPreparationConfig = {
    setup_days: 2,
    buffer_rate: 0.1,
    default_weaving_days: 1,
};

export const buildStockDelayMessage = (
    shortageQuantity: number,
    preparationTimeDays: number,
): string => {
    const pieceLabel = shortageQuantity === 1 ? 'piece' : 'pieces';

    return `This quantity is not currently in stock. ${shortageQuantity} ${pieceLabel} will need production and the preparation time is expected to take about ${preparationTimeDays} days.`;
};

export const resolveProductStockAvailability = (
    requestedQuantity: number,
    availableQuantity: number | null,
    productionTimeDays: number | null,
    config: ProductPreparationConfig = defaultPreparationConfig,
): ProductStockAvailability => {
    const normalizedAvailableQuantity = Math.max(0, availableQuantity ?? 0);
    const shortageQuantity = Math.max(
        0,
        requestedQuantity - normalizedAvailableQuantity,
    );
    const exceedsAvailableStock = shortageQuantity > 0;
    const preparationSetupDays = exceedsAvailableStock ? config.setup_days : 0;
    const preparationWeavingDays = exceedsAvailableStock
        ? (productionTimeDays ?? config.default_weaving_days) * shortageQuantity
        : 0;
    const preparationBufferDays = exceedsAvailableStock
        ? Math.ceil(
              (preparationSetupDays + preparationWeavingDays) *
                  Math.max(0, config.buffer_rate),
          )
        : 0;
    const preparationTimeDays = Math.ceil(
        preparationSetupDays + preparationWeavingDays + preparationBufferDays,
    );

    return {
        availableQuantity,
        productionTimeDays,
        shortageQuantity,
        preparationSetupDays,
        preparationWeavingDays,
        preparationBufferDays,
        preparationTimeDays,
        exceedsAvailableStock,
        stockDelayMessage: exceedsAvailableStock
            ? buildStockDelayMessage(shortageQuantity, preparationTimeDays)
            : null,
    };
};
