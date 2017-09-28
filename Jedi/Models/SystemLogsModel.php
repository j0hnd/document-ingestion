<?php
namespace Jedi\Models;

class SystemLogsModel extends AbstractModel
{
    protected $table  = 'system_logs';

    protected $guards = ['id'];
}