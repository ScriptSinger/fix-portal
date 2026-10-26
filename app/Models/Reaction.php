<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    /** Набор реакций под сообщением; порядок это порядок в выпадающей панели */
    public const EMOJI = ['👍', '❤️', '🔥', '😂', '🙏', '👎'];

    protected $fillable = ['user_id', 'reactable_type', 'reactable_id', 'emoji'];

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
