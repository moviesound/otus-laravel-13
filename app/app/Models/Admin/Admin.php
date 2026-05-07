<?php

namespace App\Models\Admin;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
use database\factories\Bot\UserFactory;
=======
use Database\Factories\Admin\AdminFactory;
>>>>>>> 3431310 (add first part)
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
=======
use Spatie\Permission\Traits\HasRoles;
>>>>>>> 3431310 (add first part)

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
<<<<<<< HEAD
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
=======
    /** @use HasFactory<AdminFactory> */
    use HasFactory, Notifiable, HasRoles;
>>>>>>> 3431310 (add first part)

    protected $connection = 'admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
