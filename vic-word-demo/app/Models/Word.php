<?php


namespace App\Models;


/**
 * Class App\Models\Word 
 * @property integer $id 
 * @property string $word 分词
 * @property string $created_at 
 * @property string $updated_at 
 */
class Word extends Model
{
    protected $tableName = 'word';
    protected $primaryKey = 'id';
}