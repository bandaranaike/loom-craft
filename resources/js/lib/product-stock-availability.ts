export type ProductStockAvailability = {
    availableQuantity: number | null;
    productionTimeDays: number | null;
    shortageQuantity: number;
    preparationSetupDays: number;
    preparationWeavingDays: number;
    preparationBufferDays: number;
    preparationTimeDays: number;
    exceedsAvailableStock: boolean;
    exceedsMaximumPreparationDays: boolean;
    maximumPreparationDays: number;
    stockDelayMessage: string | null;
};

export type ProductPreparationConfig = {
    setup_days: number;
    buffer_rate: number;
    default_weaving_days: number;
    max_display_days: number;
};

const defaultPreparationConfig: ProductPreparationConfig = {
    setup_days: 2,
    buffer_rate: 0.1,
    default_weaving_days: 1,
    max_display_days: 60,
};

export const buildStockDelayMessage = (
    shortageQuantity: number,
    preparationTimeDays: number,
    exceedsMaximumPreparationDays: boolean,
): string => {
    const pieceLabel = shortageQuantity === 1 ? 'piece' : 'pieces';
    const displayDays = exceedsMaximumPreparationDays
        ? `${preparationTimeDays}+ days`
        : `${preparationTimeDays} days`;

    if (exceedsMaximumPreparationDays) {
        return `This quantity is not currently in stock. ${shortageQuantity} ${pieceLabel} will need production and the preparation time is expected to take ${displayDays}. The product order time is getting longer. Before placing this order, you must contact the vendor.`;
    }

    return `This quantity is not currently in stock. ${shortageQuantity} ${pieceLabel} will need production and the preparation time is expected to take about ${displayDays}.`;
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
    const calculatedPreparationTimeDays = Math.ceil(
        preparationSetupDays + preparationWeavingDays + preparationBufferDays,
    );
    const maximumPreparationDays = Math.max(1, config.max_display_days);
    const exceedsMaximumPreparationDays =
        calculatedPreparationTimeDays > maximumPreparationDays;
    const preparationTimeDays = exceedsMaximumPreparationDays
        ? maximumPreparationDays
        : calculatedPreparationTimeDays;

    return {
        availableQuantity,
        productionTimeDays,
        shortageQuantity,
        preparationSetupDays,
        preparationWeavingDays,
        preparationBufferDays,
        preparationTimeDays,
        exceedsAvailableStock,
        exceedsMaximumPreparationDays,
        maximumPreparationDays,
        stockDelayMessage: exceedsAvailableStock
            ? buildStockDelayMessage(
                  shortageQuantity,
                  preparationTimeDays,
                  exceedsMaximumPreparationDays,
              )
            : null,
    };
};
