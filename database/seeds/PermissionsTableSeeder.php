<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionsTableSeeder extends Seeder
{

    public function run()
    {
        $values = ['upload', 'review', 'accept', 'reject', 'send'];

        foreach ($values as $value) {
            $model = new \Jedi\Models\PermissionsModel;

            $model->permission_name = $value;
            $model->save();
        }
    }

}
