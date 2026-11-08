<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $this->avatar?->uri
                ? Storage::url($this->avatar->uri)
                : asset('assets/front/images/avatar.png'),
            'bio' => $this->bio,
            'location' => $this->location,
            'registered_at' => $this->created_at?->toDateString(),
            'is_online' => $this->last_seen_at?->gte(now()->subMinutes(User::ONLINE_MINUTES)) ?? false,
            'reputation_rank' => $this->reputation_rank,
            'reputation_level' => $this->reputation_level,
            'statistics' => [
                'questions' => (int) $this->questions_count,
                'answers' => (int) $this->answers_count,
                'solutions' => (int) $this->solutions_count,
            ],
            'permissions' => [
                'can_message' => false,
                'can_block' => false,
            ],
        ];
    }
}
