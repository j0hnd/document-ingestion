<?php
namespace Jedi\Repositories;

use Jedi\Repositories\EloquentInterface;
use Illuminate\Database\Eloquent\Model;

use Jedi\Models\SystemLogsModel;
use DB;


class EloquentRepository implements EloquentInterface
{

    public $model;
    protected $errors = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    public function get_all_active_users($orderByField = null, $orderByType = 'asc')
    {
        $results = DB::table($this->model->getTable() . ' as u')
            ->select('u.id as user_id', 'u.firstname', 'u.lastname', 'u.email', 'ut.id as user_type_id', 'ut.type_name')
            ->join('user_types as ut', 'ut.id', '=', 'u.user_type_id')
            ->where([
                'u.is_active' => 1,
                'u.is_hide'   => 0
            ]);

        if (!is_null($orderByField)) {
            $results->orderBy($orderByField, $orderByType);
        }

        return $results;
    }

    public function get_active_user($user_id)
    {
        return DB::table($this->model->getTable() . ' as u')
            ->select('u.id as user_id', 'u.firstname', 'u.lastname', 'u.email', 'ut.id as user_type_id', 'ut.type_name', 'ut.is_admin')
            ->join('user_types as ut', 'ut.id', '=', 'u.user_type_id')
            ->where([
                'u.id'        => $user_id,
                'u.is_active' => 1,
                'u.is_hide'   => 0
            ]);
    }

    public function paginate($no = 10, $columns = ['*'])
    {
        return $this->model->paginate($no, $columns);
    }

    public function find($id, $columns = ['*'])
    {
        // return $this->model->find($id, $columns);
        return $this->model->find($id);
    }

    public function findBy($field, $value, $operator = '=')
    {
        return $this->model->where($field, $operator, $value)->get();
    }

    public function findOneBy(array $options, $columns = ['*'])
    {
        return $this->model->where($options , $columns)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->model->find($id);
        return $model->update($data);
    }

    public function delete($id)
    {
        if (is_array($id)) {
            return $this->model->destroy($id);
        } else {
            $remove = $this->model->where('id' , '=' , $id);
            return $remove->delete();
        }
    }

    public function restore($id)
    {
        if (is_array($id)) {
            $restore = $this->model
                ->withTrashed()
                ->whereIn('id' , $id);

            return $restore->restore();
        } else {
            $restore = $this->model->withTrashed()->where('id' , '=' , $id);

            return $restore->restore();
        }

    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function parse_sql_error_message($message = null)
    {
        $needle = [
            'SQLSTATE[23000]' //duplicate entry
        ];

        if (!is_null($message)) {
            foreach ($needle as $n) {
                if (strpos($n, $message) !== true) {
                    $message = 'Duplicate entry';
                    break;
                }
            }
        }

        return $message;
    }

    public function log(array $details)
    {
        try {
            foreach ($details as $detail) {
                $old_value = (isset($detail['old_value'])) ? $detail['old_value'] : null;
                $new_value = (isset($detail['new_value'])) ? $detail['new_value'] : null;
                $action_by = (isset($detail['action_by']) && !empty(['action_by'])) ? $detail['action_by'] : '';

                $system_logs_obj = new SystemLogsModel;
                $system_logs_obj->action    = $detail['action'];
                $system_logs_obj->source    = $detail['source'];
                $system_logs_obj->old_value = $old_value;
                $system_logs_obj->new_value = $new_value;
                $system_logs_obj->action_by = $action_by;

                $system_logs_obj->save();                
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}