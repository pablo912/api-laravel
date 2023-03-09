<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    const USUARIO_VERIFICADO = '1';
    const USUARIO_NO_VERIFICADO = '0';

    const USUARIO_ADMINISTRADOR = 'true';
    const USUARIO_REGULAR = 'false';


    const USUARIO_ACTIVO = '1';
    const USUARIO_NO_ACTIVO = '0';



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'email', 'password','verification_token','verified','admin','active','plan_id',
        'expiration_date','token','queries','limit'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','verification_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public static function generarVerificationToken()
    {
        return Str::random(40);
    }

    public static function generarRecoverToken()
    {
        return Str::random(80);
    }

    public function plan(){

        return $this->belongsTo(Plan::class);

    }

    public function esVerificado()
    {
        return $this->verified == User::USUARIO_VERIFICADO;
    }


    public function esAdministrador()
    {
        return $this->admin == User::USUARIO_ADMINISTRADOR;
    }

    
}
