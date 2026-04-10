<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'puesto_id',
        'avatar',
        'activo',
        'registro_token',
        'registro_completado_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
            'registro_completado_at'  => 'datetime',
        ];
    }

    public function puesto(){
        return $this->belongsTo(Puesto::class);
    }

        /**
     * URL del avatar para AdminLTE
     */
    public function adminlte_image(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    public function adminlte_desc(): string
    {
        return $this->getRoleNames()->first()
            ? ucfirst(str_replace('_', ' ', $this->getRoleNames()->first()))
            : '';
    }

    public function adminlte_profile_url(): string
    {
        return route('perfil.show');
    }

    public function getRegistroCompletadoAttribute(): bool{
        return !is_null($this->registro_completado_at);
    }
}
