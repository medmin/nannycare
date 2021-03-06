<?php

namespace common\models;

use Yii;
use yii\web\NotFoundHttpException;

//use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_profile".
 *
 * @property int $user_id
 * @property int $locale
 * @property string $firstname
 * @property string $middlename
 * @property string $lastname
 * @property string $picture
 * @property string $avatar
 * @property string $avatar_path
 * @property string $avatar_base_url
 * @property int $gender
 * @property User $user
 */
class PostalCode
{
    public $country_code;
    public $postal_code;
    public $place_name;
    private $admin_name1;
    private $admin_code1;
    private $admin_name2;
    private $admin_code2;
    private $admin_name3;
    private $admin_code3;
    public $latitude;
    public $longitude;
    public $accuracy;
    public $mysql_table = 'postal_codes';
    public $mysql_conn = false;
    private $mysql_row;
    public $print_name;
    public $location_type;
    const UNIT_MILES = 1;
    const UNIT_KILOMETERS = 2;
    const MILES_TO_KILOMETERS = 1.609344;
    const LOCATION_POSTAL_CODE = 1;
    const LOCATION_PLACE_NAME = 2;

    /**
     *  Constructor.
     *
     *  Instantiate a new PostalCode object by passing in a location. The location
     *  can be specified by a string containing a 5-digit postal code, city and
     *  state, or latitude and longitude.
     *
     *  @param  string
     *
     *  @return PostalCode
     */
    public function __construct($location)
    {
//        @mysql_connect('localhost', 'lovingna_new', 'newsoftware249');
//        @mysql_select_db('lovingna_new');
        if (is_array($location)) {
            $this->setPropertiesFromArray($location);
            $this->print_name = $this->postal_code;
            $this->location_type = $this::LOCATION_POSTAL_CODE;
        } else {
            $this->location_type = $this->locationType($location);
            switch ($this->location_type) {
                case self::LOCATION_POSTAL_CODE:
                    $this->postal_code = $this->sanitizePostalCode($location);
                    $this->print_name = $this->postal_code;
                    break;
                case self::LOCATION_PLACE_NAME:
                    $a = $this->parsePlaceName($location);
                    $this->place_name = $a[0];
                    $this->admin_code1 = $a[1];
                    $this->print_name = $this->place_name;
                    break;
                default:
                    throw new NotFoundHttpException(Yii::t('frontend', 'Invalid location type', [], Yii::$app->language));
            }
        }
    }

    public function __toString()
    {
        return $this->print_name;
    }

    /**
     *   Calculate Distance using SQL.
     *
     *   Calculates the distance, in miles, to a specified location using MySQL
     *   math functions within the query.
     *
     *   @param  string
     *
     *   @return float
     */
    private function calcDistanceSql($location)
    {
        $sql = 'SELECT 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS(t2.latitude) - '
              .'RADIANS(t1.latitude)) / 2), 2) + COS(RADIANS(t1.latitude)) * '
              .'COS(RADIANS(t2.latitude)) * POW(SIN((RADIANS(t2.longitude) - '
              .'RADIANS(t1.longitude)) / 2), 2)), '
              .'SQRT(1 - POW(SIN((RADIANS(t2.latitude) - RADIANS(t1.latitude)) / 2), 2) + '
              .'COS(RADIANS(t1.latitude)) * COS(RADIANS(t2.latitude)) * '
              .'POW(SIN((RADIANS(t2.longitude) - RADIANS(t1.longitude)) / 2), 2))) '
              .'AS "miles" '
              ."FROM {$this->mysql_table} t1 INNER JOIN {$this->mysql_table} t2 ";
        switch ($this->location_type) {
            case self::LOCATION_POSTAL_CODE:
                // note: postal code is sanitized in the constructor
                $sql .= "WHERE t1.postal_code = '{$this->postal_code}' ";
                break;
            case self::LOCATION_PLACE_NAME:
                $place_name = ($this->place_name);
                $admin_code = ($this->admin_code1);
                $sql .= "WHERE (t1.place_name = '$place_name' AND t1.admin_code1 = '$admin_code') AND t2.postal_code = '$postal_code_to'";
                break;
            default:
                throw new \Exception('Invalid location type for '.__CLASS__);
        }
        switch (self::locationType($location)) {
            case self::LOCATION_POSTAL_CODE:
                $postal_code_to = $this->sanitizePostalCode($location);
                $sql .= "AND t2.postal_code = '$postal_code_to'";
                break;
            case self::LOCATION_PLACE_NAME:
                $a = $this->parsePlaceName($location);
                $place_name = ($a[0]);
                $admin_code = ($a[1]);
                $sql .= "AND (t2.place_name = '$place_name' AND t2.admin_code1 = '$admin_code')";
                break;
        }
//        Yii::$app->db->createCommand($sql)->query();  这个方法没有使用过，有坑，$postal_code_from 和 $postal_code_to 都是没有定义
        $r = @mysql_query($sql);
        if (!$r) {
            throw new \Exception(mysql_error());
        }
        if (@mysql_num_rows($r) == 0) {
            throw new \Exception("Record does not exist calculatitudeing distance between $postal_code_from and $postal_code_to");
        }
        $miles = @mysql_result($r, 0);
        @mysql_free_result($r);

        return $miles;
    }

    public function getCity()
    {
        if (empty($this->place_name)) {
            $this->setPropertiesFromDb();
        }

        return $this->place_name;
    }

    public function getCounty()
    {
        if (empty($this->admin_name2)) {
            $this->setPropertiesFromDb();
        }

        return $this->admin_name2;
    }

    public function getStateName()
    {
        if (empty($this->admin_name1)) {
            $this->setPropertiesFromDb();
        }

        return $this->admin_name1;
    }

    public function getStatePrefix()
    {
        if (empty($this->admin_code1)) {
            $this->setPropertiesFromDb();
        }

        return $this->admin_code1;
    }

    public function getDbRow()
    {
        if (empty($this->mysql_row)) {
            $this->setPropertiesFromDb();
        }

        return $this->mysql_row;
    }

    /**
     *   Get Distance To Postal Code.
     *
     *   Gets the distance to another postal code. The distance can be obtained in
     *   either miles or kilometers.
     *
     *   @param  string
     *   @param  int
     *   @param  int
     *
     *   @return float
     */
    public function getDistanceTo($postal_code, $units = self::UNIT_MILES)
    {
        $miles = $this->calcDistanceSql($postal_code);
        if ($units == self::UNIT_KILOMETERS) {
            return $miles * self::MILES_TO_KILOMETERS;
        } else {
            return $miles;
        }
    }

    public function getSameCity()
    {
        $sql = "SELECT * FROM {$this->mysql_table} WHERE place_name='{$this->place_name}';";
        // $r = @mysql_query($sql);
        //echo var_dump($sql);
        $a = [];
        $row = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($row as $re) {
            $a[] .= new self($re); // 直接使用 = 的话，$a 中的元素会是对象，使用 .= 转化为字符串，触发 __toString
        }

        return $a;
    }

    public function getPostalCodesInRange($range_from, $range_to, $units = 1)
    {
        if (empty($this->country_code)) {
            $this->setPropertiesFromDb();
        }
        $sql = "SELECT 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2))) AS \"miles\", z.* FROM {$this->mysql_table} z "
              ."WHERE postal_code <> '{$this->postal_code}' "
              ."AND latitude BETWEEN ROUND({$this->latitude} - (25 / 69.172), 4) "
              ."AND ROUND({$this->latitude} + (25 / 69.172), 4) "
              ."AND longitude BETWEEN ROUND({$this->longitude} - ABS(25 / COS({$this->latitude}) * 69.172)) "
              ."AND ROUND({$this->longitude} + ABS(25 / COS({$this->latitude}) * 69.172)) "
              ."AND 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2))) <= $range_to "
              ."AND 3956 * 2 * ATAN2(SQRT(POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2)), SQRT(1 - POW(SIN((RADIANS({$this->latitude}) - "
              .'RADIANS(z.latitude)) / 2), 2) + COS(RADIANS(z.latitude)) * '
              ."COS(RADIANS({$this->latitude})) * POW(SIN((RADIANS({$this->longitude}) - "
              ."RADIANS(z.longitude)) / 2), 2))) >= $range_from "
              .'ORDER BY 1 ASC';
//        $r = @mysql_query($sql);
//        if (!$r) {
//            throw new Exception(mysql_error());
//        }
        $a = [];
        $row = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($row as $re) {
            // TODO: load PostalCode from array
            $a[$re['miles']] = new self($re);
        }

        return $a;
    }

    // 既然没用到就先注释掉
//    private function hasDbConnection()
//    {
//        if ($this->mysql_conn) {
//            return mysql_ping($this->mysql_conn);
//        } else {
//            return mysql_ping();
//        }
//    }
    private function locationType($location)
    {
        if (self::isValidPostalCode($location)) {
            return self::LOCATION_POSTAL_CODE;
        } elseif (self::isValidPlaceName($location)) {
            return self::LOCATION_PLACE_NAME;
        } else {
            return false;
        }
    }

    public static function isValidPostalCode($postal_code)
    {
        return preg_match('/^[0-9]{5}/', $postal_code);
    }

    public static function isValidPlaceName($location)
    {
        $words = explode(',', $location);
        if (empty($words) || count($words) != 2 || strlen(trim($words[1])) != 2) {
            return false;
        }
        if (!is_numeric($words[0]) && !is_numeric($words[1])) {
            return true;
        }

        return false;
    }

    public static function parsePlaceName($location)
    {
        $words = explode(',', $location);
        if (empty($words) || count($words) != 2 || strlen(trim($words[1])) != 2) {
            throw new \Exception('Failed to parse place_name and state from string.');
        }
        $place_name = trim($words[0]);
        $admin_code = trim($words[1]);

        return [$place_name, $admin_code];
    }

    // @access protected
    private function sanitizePostalCode($postal_code)
    {
        return preg_replace('/[^0-9]/', '', $postal_code);
    }

    private function setPropertiesFromArray($a)
    {
        foreach ($a as $key => $value) {
            $this->$key = $value;
        }
        $this->mysql_row = $a;
    }

    private function setPropertiesFromDb()
    {
        switch ($this->location_type) {
            case self::LOCATION_POSTAL_CODE:
                $sql = "SELECT * FROM {$this->mysql_table} t "
                      ."WHERE postal_code = '{$this->postal_code}' LIMIT 1";
                break;
            case self::LOCATION_PLACE_NAME:
                $sql = "SELECT * FROM {$this->mysql_table} t "
                      ."WHERE place_name = '{$this->place_name}' "
                      ."AND admin_code1 = '{$this->admin_code1}' LIMIT 1";
                break;
            default:
                throw new NotFoundHttpException(Yii::t('frontend', 'Invalid location type', [], Yii::$app->language));
        }
        $row = Yii::$app->db->createCommand($sql)->queryOne();
        if (!$row) {
            throw new \Exception("{$this->print_name} was not found in the database.");
        }
        $this->setPropertiesFromArray($row);
    }
}
