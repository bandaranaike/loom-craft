<?php

namespace App\DTOs\Product;

use App\Http\Requests\Vendor\StoreProductRequest;
use App\Models\User;
use App\ValueObjects\Dimensions;
use App\ValueObjects\Money;
use Illuminate\Http\UploadedFile;

class ProductCreateData
{
    /**
     * @param  list<UploadedFile>  $images
     */
    public function __construct(
        public User $user,
        public string $name,
        public string $description,
        public Money $vendorPrice,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public Dimensions $dimensions,
        public array $images,
        public ?UploadedFile $video,
    ) {}

    public static function fromRequest(StoreProductRequest $request): self
    {
        $length = $request->input('dimension_length');
        $width = $request->input('dimension_width');
        $height = $request->input('dimension_height');

        $images = array_values(array_filter(
            $request->file('images', []),
            static fn ($file): bool => $file instanceof UploadedFile && $file->isValid()
        ));

        $video = $request->file('video');

        return new self(
            $request->user(),
            $request->string('name')->toString(),
            $request->string('description')->toString(),
            Money::fromString($request->string('vendor_price')->toString()),
            $request->string('materials')->toString() ?: null,
            $request->integer('pieces_count') ?: null,
            $request->integer('production_time_days') ?: null,
            new Dimensions(
                is_numeric($length) ? (float) $length : null,
                is_numeric($width) ? (float) $width : null,
                is_numeric($height) ? (float) $height : null,
                $request->string('dimension_unit')->toString() ?: null,
            ),
            $images,
            $video instanceof UploadedFile && $video->isValid() ? $video : null,
        );
    }
}
