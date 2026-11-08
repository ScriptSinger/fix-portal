<?php

namespace App\Models;

use App\Mail\RegistrationConfirmation;
use App\Notifications\QueuedResetPassword;
use App\Services\UserDeletionService;
use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use DateTrait, HasApiTokens, HasFactory, ImageHandlerTrait, Notifiable; // Использовать трейт для добавления метода getDate  ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'location',
        'phone',
        'bio',
        'avatar',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'blocked_at' => 'datetime',
        'muted_until' => 'datetime',
        'email_notifications' => 'boolean',
    ];

    /** Сколько минут после последнего запроса пользователь считается «онлайн» */
    public const ONLINE_MINUTES = 5;

    /** Секрет для ссылки «отключить письма»: создаётся при первом обращении и дальше не меняется. */
    public function unsubscribeToken(): string
    {
        if (! $this->unsubscribe_token) {
            $this->unsubscribe_token = Str::random(40);
            $this->saveQuietly();
        }

        return $this->unsubscribe_token;
    }

    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(self::ONLINE_MINUTES));
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function ips(): HasMany
    {
        return $this->hasMany(Ip::class);
    }

    public function avatar(): HasOne
    {
        return $this->hasOne(Avatar::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user): void {
            if (blank($user->username)) {
                $user->username = static::makeUsername($user->name);
            }
        });

    }

    public function delete()
    {
        return app(UserDeletionService::class)->delete($this);
    }

    public function deleteWithoutCleanup(): ?bool
    {
        return parent::delete();
    }

    public static function makeUsername(?string $name): string
    {
        $base = Str::lower(Str::slug((string) $name, '_'));
        $base = preg_replace('/[^a-z0-9_]+/', '', $base) ?: 'user';
        $base = Str::substr($base, 0, 32);
        $candidate = $base;
        $suffix = 1;

        while (static::query()->where('username', $candidate)->exists()) {
            $tail = '_'.$suffix++;
            $candidate = Str::substr($base, 0, 32 - Str::length($tail)).$tail;
        }

        return $candidate;
    }

    public function hasVerifiedEmail()
    {
        // Реализация проверки подтверждения адреса электронной почты
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified()
    {
        // Реализация отметки адреса электронной почты как подтвержденного
        $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function sendEmailVerificationNotification()
    {
        Mail::to($this->email)->send(new RegistrationConfirmation($this));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    public function getEmailForVerification()
    {
        // Получение адреса электронной почты для верификации
        return $this->email;
    }
}
