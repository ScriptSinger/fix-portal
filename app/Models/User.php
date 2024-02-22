<?php

namespace App\Models;

use App\Mail\RegistrationConfirmation;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\DateTrait;
use App\Traits\ImageHandlerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, DateTrait, ImageHandlerTrait; // Использовать трейт для добавления метода getDate  ;

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
    ];




    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->comments()->each(function ($comment) {
                $comment->replies()->delete();
            });
            $user->comments()->delete();
            $user->questions()->delete();
        });

        static::restoring(function ($user) {
            $user->comments()->withTrashed()->each(function ($comment) {
                $comment->replies()->withTrashed()->restore();
            });
            $user->comments()->withTrashed()->restore();
            $user->questions()->withTrashed()->restore();
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

    public function getEmailForVerification()
    {
        // Получение адреса электронной почты для верификации
        return $this->email;
    }
}
