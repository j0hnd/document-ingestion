<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserTypesSeeder extends Seeder
{

    public function run()
    {
        $values = ['System Admin', 'CRO', 'Basic'];

        foreach ($values as $value) {
            $model = new \Jedi\Models\UserTypesModel();

            $model->type_name = $value;
            $model->is_active = 1;

            if ($value == 'System Admin') {
                $model->is_admin = 1;
            }

            $model->save();
        }
    }

}
