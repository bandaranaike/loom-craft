# Freeform Task Request

Write the task here in your own way.

No structure is required.

Useful things to include when available:

- What you want changed
- Why it matters
- Screens, routes, or files involved
- Expected behavior
- Constraints or things to avoid
- Any examples

Latest note:

- [ ] Stock Algorithm
    - Normally, vendors do not have a huge number of pieces in available stock.
    - They made products on demand.
    - The production time depends on the quantity, number of colors, required pattern, and the product type.
        - Machine and Fabric prepare time: 2 days and do not depend on the quantity.
        - Weaving time depends on the quantity, the type of weaving, and number of colors.
            - Wall hangers: 2 days per item
            - Cushion covers: 6 hours per item
            - Bags: 1 day per item
    - When a product is added to the system, the vendor can specify the production time. It's always weaving time (column: products.production_time_days).
    - The product quantity (column: products.pieces_count) is always the number of available pieces, and it does not take any time to prepare.
    - When the user adds product items in to the cart to buy, we need to calculate the total time of the product preparation.
    - Using your knowledge of statistics, mathematics, critical thinking, and algorithms, we need to create an algorithm that will calculate the total time of the product
      preparation by considering all the possible factors.
    - Use your knowledge and add missing factors to the algorithm.
    - It could be a mathematical formula or a program.
    - The Laravel and PHP are more suitable for this algorithm. Use backend programming for that and show it in the front end using an API since it uses database values.
    - When the quantity of products is changed, the order preparation time should be recalculated.
    - The preparation time should be calculated if the available quantity is less than the quantity of products in the cart.
