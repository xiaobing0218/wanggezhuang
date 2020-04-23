<?php
class user_store_model extends Model
{
    public $table_name = 'user_store';
    
    public function add($user_id, $goods_id)
    {
        $sql = "INSERT INTO {$this->table_name}(user_id, merchant_id, created_date) VALUES (:user_id, :merchant_id, :created_date) 
                ON DUPLICATE KEY UPDATE created_date = :created_date";
        return $this->execute($sql, array(':user_id' => $user_id, ':merchant_id' => $goods_id, 'created_date' => $_SERVER['REQUEST_TIME']));
    }
    
    /**
     * 获取用户商品收藏列表
     */
    public function get_user_favorites($user_id, $limit = null)
    {   
        $total = $this->find_count(array('user_id' => $user_id));
        if($total > 0)
        {
            $limit = $this->set_limit($limit, $total);
            $merchant_model = new merchant_model();
            $sql = "SELECT a.user_id,a.merchant_id, a.created_date, b.company_name, b.logo, b.address
                    FROM {$this->table_name} AS a
                    INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}merchant AS b
                    ON a.merchant_id = b.user_id
                    WHERE a.user_id = :user_id
                    ORDER BY a.created_date DESC
                    {$limit}
                   ";
            if($res = $this->query($sql, array(':user_id' => $user_id))) return $res;
        }
        return null;
    }
}