<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cta extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'target_url',
        'city',
        'brand',
        'appliance_type',
        'problem',
        'title',
        'text',
        'anchor',
        'placement',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function trackedUrl(): string
    {
        $campaign = collect([
            $this->brand,
            $this->appliance_type,
            $this->problem,
        ])->filter()->implode('_');

        $params = array_filter([
            'utm_source' => 'ufamasters',
            'utm_medium' => 'internal_cta',
            'utm_campaign' => $campaign ?: 'article_cta',
            'utm_content' => "post_{$this->post_id}_{$this->placement}",
        ]);

        $separator = str_contains($this->target_url, '?') ? '&' : '?';

        return $this->target_url . $separator . http_build_query($params);
    }
}
