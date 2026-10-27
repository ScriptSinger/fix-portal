<?php

namespace App\Models;

use App\Mail\RegistrationConfirmation;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\QueuedResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, DateTrait, ImageHandlerTrait; // Использовать трейт для добавления метода getDate  ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location',
        'phone',
        'bio',
        'avatar',
        'role_id'
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
    ];

    /** Сколько минут после последнего запроса пользователь считается «онлайн» */
    public const ONLINE_MINUTES = 5;

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

        // Пользователь стирается окончательно вместе со своими вопросами, комментариями и привязками соцсетей:
        // иначе занятая «пустая» привязка не пустила бы его войти через ту же соцсеть заново
        static::deleting(function ($user) {
            $user->comments()->get()->each->delete();
            $user->questions()->get()->each->delete();
            \App\Models\SocialAccount::where('user_id', $user->id)->delete();
            $user->avatar()->delete();
            \App\Models\Reaction::where('user_id', $user->id)->delete();
            $user->notifications()->delete();
        });
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
