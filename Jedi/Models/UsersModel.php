<?php
namespace Jedi\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersModel extends AbstractModel implements AuthenticatableContract {

    use Authenticatable;

    use SoftDeletes;

    protected $table  = 'users';

    protected $fillable = [
        'email',
        'password',
        'lastname',
        'firstname',
        'is_active',
        'user_type_id'
    ];

    protected $hidden = ['password'];

    protected $dates  = ['deleted_at'];

//    public function usertypes()
//    {
//        return $this->hasMany('Jedi\Models\UserTypesModel', 'user_type_id');
//    }
}