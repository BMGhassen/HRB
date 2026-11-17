<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\NewUserNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'state',
        'code_client',
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
        'password' => 'hashed',
    ];
    public function vendeurs(){
        return $this->hasOne(Vendeur::class,'user_id', 'id');
    }
    public function commandes(){
        return $this->hasMany(Commande::class,'client_id', 'id');
    }

    public function client(){
        return $this->belongsTo(Client::class, 'code_client', 'code');
    }
    
    protected static function booted()
{
        static::created(function ($user) {
            // Find admins directly by role
            $admins = User::where('role', 'admin')->get();

            Notification::send($admins, new NewUserNotification($user));
        });
}
}
