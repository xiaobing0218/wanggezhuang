<?php
/*
Author: Administrator
CreeateTame:2020/3/16 9:43
*/
class jsapi_model extends Model{
    public $table_name = 'jsapi';

    /**
     * @return mixed
     */
    public function one_select(){
        $sql = "
            SELECT * FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}jsapi ORDER BY created_date desc LIMIT 1 
               ";
        $one=$this->query($sql);
        return $one;
    }

    /**
     * @param $token
     * @return mixed
     */
    public function add($token){
        $time=time();
        $sql= "INSERT INTO {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}jsapi (`jsapi`,`created_date`) 
                     VALUES ('{$token}', '{$time}');
                    ";
        return $this->execute($sql);

    }
}
