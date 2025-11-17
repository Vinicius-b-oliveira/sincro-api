<?php

namespace App\Http\Resources\V1\Analytics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'chart_data' => $this->resource['chart_data'],
            'summary_stats' => $this->resource['summary_stats'],
            'filters' => $this->resource['filters'],
        ];
    }
}
