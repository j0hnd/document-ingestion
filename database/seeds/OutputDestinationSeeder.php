<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class OutputDestinationSeeder extends Seeder
{

	public function run()
	{
        $values = ['Live', 'Test', 'File', 'No Output'];

        foreach ($values as $value) {
            $model = new \Jedi\Models\OutputDestinationsModel;

            $model->output_destination_name = $value;
            $model->save();
        }
	}

}
