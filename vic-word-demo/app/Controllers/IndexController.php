<?php
namespace App\Controllers;

use App\Models\Article;
use App\Models\ArticleWord;
use App\Models\Word;
use Lizhichao\Word\VicWord;
use Moon\Controller;
use Moon\Db\Connection;

class IndexController extends Controller
{
    public function index()
    {
//        /** @var Connection $db */
//        $db = \Moon::$app->get('db');

        return $this->render('index');
    }

    public function articleList()
    {
        $kw = trim(request('kw'));
        $list = [];
        if(strlen($kw) > 0){
            $word = Word::find()->where('word=?', [$kw])->first();
            if(!empty($word)){
                $sql = "select a.* from article_word aw left join article a on aw.article_id=a.id where aw.word_id=? order by aw.count desc";
                $list = ArticleWord::find()->getDb()->fetchAll($sql, [$word->id]);
            }
        }else{
            $list = Article::find()->order('id desc')->all();
        }
        return $this->render('article/index', ['list' => $list]);
    }

    public function add()
    {
        return $this->render('article/add');
    }

    public function save()
    {
        $title = request('title');
        $content = request('content');
        //dump($title, $content);

        $article = new Article();
        $article->title = $title;
        $article->content = $content;
        $article->created_at = date('Y-m-d H:i:s');
        $article->updated_at = date('Y-m-d H:i:s');
        $article->save();

        $this->vicWord($article->id);

        return [
            'code' => 200,
            'msg' => 'success'
        ];
    }

    public function vicWord($id)
    {
        echo '<meta charset="utf-8">';
        $start_time = microtime(true);
        $article = Article::find()->where('id=?', [$id])->first();
        //定义词典文件路径
        define('_VIC_WORD_DICT_PATH_', \Moon::$app->getRootPath() . '/vendor/lizhichao/word/Data/dict.igb');
        $fc = new VicWord();
        $arr = $fc->getAutoWord(strip_tags($article->content));
        //dump($arr);

        $bd_list = $this->mb_str_split('`~!@#$%^&*()_+-=[]\\{}|;\':",./<>? ·～！@#¥%……&*（）——【】、「」；；：“”‘’，。《》？');
        foreach ($arr as $item) {
            $w = trim($item[0]);
            if(strlen($w) == 0){
                continue;
            }
            if (in_array($w, $bd_list)) {
                continue;
            }
            //echo $item[0]."_";
            $word = Word::find()->where('word=?', [$w])->first();
            if (empty($word)) {
                $word = new Word();
                $word->word = $w;
                $word->created_at = date('Y-m-d H:i:s');
                $word->updated_at = date('Y-m-d H:i:s');
                $word->save();
            }

            $articleWord = ArticleWord::find()->where('article_id=? and word_id=?', [$id, $word->id])->first();
            if (empty($articleWord)) {
                $articleWord = new ArticleWord();
                $articleWord->article_id = $id;
                $articleWord->word_id = $word->id;
                $articleWord->count = 1;
                $articleWord->created_at = date('Y-m-d H:i:s');
                $articleWord->updated_at = date('Y-m-d H:i:s');
                $articleWord->save();
            } else {
                $articleWord->count += 1;
                $articleWord->updated_at = date('Y-m-d H:i:s');
                $articleWord->save();
            }
        }

        //echo 'Used ' . (microtime(true) - $start_time) . 's';
    }

    function mb_str_split($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
}