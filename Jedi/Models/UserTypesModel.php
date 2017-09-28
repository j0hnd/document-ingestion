<?php
namespace Jedi\Models;

class UserTypesModel extends AbstractModel {

    protected $table  = 'user_types';

    protected $guards = ['id'];

//    public function users()
//    {
//        return $this->belongsTo('Jedi\Models\UsersModel', 'user_type_id');
//    }
}