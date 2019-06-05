<?php


namespace App\Models;


/**
 * Class App\Models\ArticleWord 
 * @property integer $id 
 * @property integer $article_id 文章id
 * @property integer $word_id 分词id
 * @property integer $count 出现次数
 * @property string $created_at 
 * @property string $updated_at 
 */
class ArticleWord extends Model
{
    protected $tableName = 'article_word';
    protected $primaryKey = 'id';
}