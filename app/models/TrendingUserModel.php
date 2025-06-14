<?php

/**
 * This class extends the base model class and represents your associated table
 */ 
class TrendingUserModel extends \Asatru\Database\Model
{
    /**
     * Add to view count
     * 
     * @param $user
     * @return void
     * @throws \Exception
     */
    public static function addViewCount($user)
    {
        try {
            $token = md5($_SERVER['REMOTE_ADDR']);

            if (strpos($user, '/') !== false) {
                $user = substr($user, strpos($user, '/') + 1);
            }

            $exists = TrendingUserModel::raw('SELECT COUNT(*) as count FROM `@THIS` WHERE token = ? AND username = ? AND DATE(created_at) = CURDATE()', [
                $token,
                $user
            ])->first();
            
            if ($exists->get('count') == 0) {
                TrendingUserModel::raw('INSERT INTO `@THIS` (username, token, created_at) VALUES(?, ?, CURRENT_TIMESTAMP)', [
                    $user, $token
                ]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get trending users from date up to now
     * 
     * @param $fromDate
     * @param $limit
     * @param $paginate
     * @return mixed
     * @throws \Exception
     */
    public static function getTrendingUsers($fromDate = null, $limit = 3, $paginate = null)
    {
        try {
            if ($fromDate === null) {
                $fromDate = date('Y-m-d', strtotime('-1 week'));
            }

            if ($paginate === null) {
                $rows = TrendingUserModel::raw('SELECT COUNT(username) AS count, username FROM `@THIS` WHERE DATE(created_at) > ? GROUP BY username ORDER BY count DESC LIMIT ' . $limit, [
                    $fromDate
                ]);
            } else {
                $rows = TrendingUserModel::raw('SELECT COUNT(username) AS count, username FROM `@THIS` WHERE DATE(created_at) > ? GROUP BY username HAVING count < ? ORDER BY count DESC LIMIT ' . $limit, [
                    $fromDate,
                    (int)$paginate
                ]);
            }

            return $rows;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}