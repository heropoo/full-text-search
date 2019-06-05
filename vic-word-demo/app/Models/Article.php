<?php
namespace App\Models;

/**
 * Class App\Models\Article 
 * @property integer $id 
 * @property string $title 文章标题
 * @property string $content 文章内容
 * @property string $created_at 
 * @property string $updated_at 
 */
class Article extends Model
{
    protected $tableName = 'article';
    protected $primaryKey = 'id';

}