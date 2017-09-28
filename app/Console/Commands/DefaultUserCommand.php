<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Jedi\Models\UsersModel;
use Jedi\Models\UserTypesModel;
use Hash, DB;


class DefaultUserCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'jedi:defaultuser';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate default user';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try {
            $user_type_obj = DB::table('user_types as t')
                ->select('id')
                ->where('t.type_name', 'System Admin')
                ->where('t.is_active', 1);

            if ($user_type_obj->count()) {
                $users_model = new UsersModel();

                $users_model->firstname    = 'SYSTEM';
                $users_model->lastname     = 'SYSTEM';
                $users_model->email        = config('defaults.default_user');
                $users_model->password     = Hash::make(config('defaults.default_pwd'));
                $users_model->user_type_id = $user_type_obj->first()->id;
                $users_model->is_active = 1;
                $users_model->is_hide = 1;

                if ($users_model->save()) {
                    $this->info('\'system\' is added as the default user');
                }
            }



        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
