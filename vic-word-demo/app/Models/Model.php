<?php
namespace App\Models;

use Moon\Db\Table;

class Model extends Table
{
    public function __construct($tableName = null, $db = null)
    {
        if(is_null($db)){
            $db = \Moon::$app->get('db');
        }
        parent::__construct($tableName, $db);
    }
}