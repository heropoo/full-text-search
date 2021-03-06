<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2019/1/16
 * Time: 12:12
 */
namespace App\Services;

use Moon\Db\Connection;

class FillModelCommentService
{
    /**
     * @var Connection
     */
    protected $db;
    protected $tableName;
    public function __construct(Connection $db, $tableName)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }
    public function fill($className){
        $ref = new \ReflectionClass($className);
        $commentString = $this->getPropertiesCommentString($ref->getName());
        $filename = $ref->getFileName();
        if (empty($ref->getDocComment())) {
            $this->insertAtLine($filename, $ref->getStartLine() - 2, $commentString."\n");
        }else{
            $content = file_get_contents($filename);
            $res = preg_match("#namespace ".str_replace('\\', '\\\\', $ref->getNamespaceName()).";\s+(/\*(\s|.)*?\*\/)\s+class {$ref->getShortName()}#", $content, $matches);
            if ($res) {
                $content = str_replace($matches[1], $commentString, $content);
                file_put_contents($filename, $content);
            }
        }
        return true;
    }
    protected function getPropertiesCommentString($className){
        $fields = $this->getTableFields();
        $commentString = "/**\n * Class $className \n";
        foreach ($fields as $field){
            $commentString .= " * @property {$field['type']} \${$field['field']} {$field['comment']}\n";
        }
        return $commentString . " */";
    }
    protected function getTableFields(){
        $list = $this->db->fetchAll('show full columns from '.$this->tableName);
        $fields = [];
        foreach ($list as $item) {
            $field = $item['Field'];
            $sub_type_tmp = explode(' ', str_replace('(', ' ', $item['Type']));
            switch ($sub_type_tmp[0]) {
                case 'boolean':
                case 'bool':
                    $type = 'boolean';
                    break;
                case 'varchar':
                case 'char':
                case 'text':
                case 'json':
                    $type = 'string';
                    break;
                case 'int':
                case 'integer':
                case 'tinyint':
                case 'bigint':
                case 'smallint':
                    $type = 'integer';
                    break;
                case 'real':
                case 'double':
                case 'float':
                    $type = 'float';
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $type = 'string';
                    break;
                default:
                    $type = 'mixed';
                    break;
            }
            $fields[] = [
                'type'=>$type,
                'field'=>$field,
                'comment'=>$item['Comment']
            ];
        }
        return $fields;
    }
    protected function insertAtLine($filename, $line, $content){
        $fileArr = file($filename);
        $lines = count($fileArr);
        if($line < 0){
            $line = 0;
        }else if($line > $lines){
            $line = $lines;
        }
        $fileArr[$line] .= $content;
        $newContent = implode('', $fileArr);
        return file_put_contents($filename,$newContent);
    }
}