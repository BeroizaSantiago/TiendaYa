<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'description' => $this->description,
        'price' => (float) $this->price,
        'stock' => $this->stock,
        'active' => (bool) $this->active,

        'attribute_stocks' => $this->whenLoaded('attributeStocks', function () {
            return $this->attributeStocks->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'stock' => $stock->stock,
                    'attribute_value' => [
                        'id' => $stock->attributeValue->id,
                        'value' => $stock->attributeValue->value,
                        'attribute' => [
                            'id' => $stock->attributeValue->attribute->id,
                            'name' => $stock->attributeValue->attribute->name,
                        ]
                    ]
                ];
            });
        }),
    ];
}
}
