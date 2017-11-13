<?php

namespace yuncms\user\models;

/**
 * This is the ActiveQuery class for [[UserToken]].
 *
 * @see UserToken
 */
class UserTokenQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /*public function active()
    {
        return $this->andWhere(['status' => UserToken::STATUS_PUBLISHED]);
    }*/

    /**
     * @inheritdoc
     * @return UserToken[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserToken|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * 热门模型
     * @param string $reference 计算字段
     * @param float $pull 热度衰减下拉指数默认是1.8
     * @return mixed
     */
    public function hottest($reference = 'views', $pull = 1.8)
    {
        return $this->orderBy(['(' . $reference . ' / pow((((UNIX_TIMESTAMP(NOW()) - created_at) / 3600) + 2),' . $pull . ') )' => SORT_DESC]);
    }

    /**
     * 查询今日新增
     * @return $this
     */
    public function dayCreate()
    {
        return $this->andWhere('date(created_at)=date(NOW())');
    }

    /**
     * 查询本周新增
     * @return $this
     */
    public function weekCreate()
    {
        return $this->andWhere('month(FROM_UNIXTIME(created_at)) = month(curdate()) AND week(FROM_UNIXTIME(created_at)) = week(curdate())');
    }

    /**
     * 查询本月新增
     * @return $this
     */
    public function monthCreate()
    {
        return $this->andWhere('month(FROM_UNIXTIME(created_at)) = month(curdate()) AND year(FROM_UNIXTIME(created_at)) = year(curdate())');
    }

    /**
     * 查询本年新增
     * @return $this
     */
    public function yearCreate()
    {
        return $this->andWhere('year(FROM_UNIXTIME(created_at)) = year(curdate())');
    }

    /**
     * 查询本季度新增
     * @return $this
     */
    public function quarterCreate()
    {
        return $this->andWhere('quarter(FROM_UNIXTIME(created_at)) = quarter(curdate())');
    }
}
