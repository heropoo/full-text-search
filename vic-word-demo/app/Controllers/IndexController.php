<?php
namespace App\Controllers;

use App\Models\Article;
use App\Models\ArticleWord;
use App\Models\Word;
use Elasticsearch\ClientBuilder;
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
//            $word = Word::find()->where('word=?', [$kw])->first();
//            if(!empty($word)){
//                $sql = "select a.* from article_word aw left join article a on aw.article_id=a.id where aw.word_id=? order by aw.count desc";
//                $list = ArticleWord::find()->getDb()->fetchAll($sql, [$word->id]);
//            }
            $client = ClientBuilder::create()->setHosts([env('ES_HOST')])->build();
            $params['index'] = 'user';
            $params['type'] = 'article';
            $params['body']['query']['match']['content'] = $kw;
            //执行查询
            $rtn = $client->search($params)['hits'];
//            dump($rtn);
            $list = array_column($rtn['hits'], '_source');
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

        $article = new Article();
        $article->title = $title;
        $article->content = $content;
        $article->created_at = date('Y-m-d H:i:s');
        $article->updated_at = date('Y-m-d H:i:s');

        /** @var Connection $db */
        $db = \Moon::$app->get('db');
        $db->beginTransaction();

        try{
            $article->save();
            //$this->vicWord($article->id);
            $this->createEsIndexType($article->id);
            $db->commit();
        }catch (\Exception $e){
            $db->rollback();
            return [
                'code' => 500,
                'msg' => $e->getMessage()
            ];
        }

        return [
            'code' => 200,
            'msg' => 'success'
        ];
    }

    public function vicWord($id)
    {
//        $start_time = microtime(true);
        $article = Article::find()->where('id=?', [$id])->first();
        //定义词典文件路径
        define('_VIC_WORD_DICT_PATH_', \Moon::$app->getRootPath() . '/vendor/lizhichao/word/Data/dict.igb');
        $fc = new VicWord();
        $arr = $fc->getAutoWord(strip_tags($article->content));

        $bd_list = $this->mb_str_split('`~!@#$%^&*()_+-=[]\\{}|;\':",./<>? ·～！@#¥%……&*（）——【】、「」；；：“”‘’，。《》？');
        foreach ($arr as $item) {
            $w = trim($item[0]);
            if(strlen($w) == 0){
                continue;
            }
            if(strlen($w) > 255){
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

    public function createEsIndexType($id){
        $article = Article::find()->where('id=?', [$id])->first();

        $client = ClientBuilder::create()->setHosts([env('ES_HOST')])->build();
        //dump($client);
        $params['body'] = array(
            'id' => $article['id'],
            'title' => $article['title'],
            'content' => strip_tags($article['content']),
        );
//        $params['body'] = [
//            'properties'=>[
//                'title'=>[
//                    'type'=>'text',
//                    'analyzer'=> 'ik_max_word',
//                    'search_analyzer'=> 'ik_max_word'
//                ],
//                'content'=>[
//                    'type'=>'text',
//                    'analyzer'=> 'ik_max_word',
//                    'search_analyzer'=> 'ik_max_word'
//                ],
//                'id'=>$article['id']
//            ]
//
//            //'id'=>$article['id']
//        ];
        $params['id'] = $article['id'];
        $params['index'] = 'user';
        $params['type'] = 'article';
        $res = $client->index($params);
        //var_dump($res);
    }

    public function createEsIndex(){
        $client = ClientBuilder::create()->setHosts([env('ES_HOST')])->build();

        echo $str = 'curl -X PUT \''.env('ES_HOST').':9200/user\' -d \'
{
  "mappings": {
    "article": {
      "properties": {
        "id": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        },
        "title": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        },
        "content": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        }
      }
    }
  }
}\'';

    }
}