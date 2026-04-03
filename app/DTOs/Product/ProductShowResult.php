<?php

namespace App\DTOs\Product;

class ProductShowResult
{
    /**
     * @param  array{average_rating: ?string, total_reviews: int}  $reviewSummary
     * @param  list<array{id: int, rating: int, review: string, reviewer_name: string, created_at: ?string, created_at_human: ?string}>  $reviews
     * @param  array{can_submit: bool, has_delivered_purchase: bool, has_reviewed: bool, requires_authentication: bool, message: ?string}  $reviewForm
     */
    public function __construct(
        public ProductShowItem $product,
        public array $reviewSummary,
        public array $reviews,
        public array $reviewForm,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product' => $this->product->toArray(),
            'review_summary' => $this->reviewSummary,
            'reviews' => $this->reviews,
            'review_form' => $this->reviewForm,
        ];
    }
}
