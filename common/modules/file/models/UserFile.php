<?php

namespace common\modules\file\models;

use Yii;
use common\models\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "user_file".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $file_uuid
 * @property string $title
 * @property string $ext 
 * @property string $link
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 *
 * @property User $user
 */
class UserFile extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_file';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['delete'] = []; // 定义一个空数组，不做任何rules的校验
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'ext', 'link', 'created_at', 'file'], 'required'],
            [['user_id', 'status', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['file_uuid'], 'string', 'max' => 36],
            [['title'], 'string', 'max' => 300],
            [['ext'], 'string', 'max' => 10],
            ['file', 'file', 'maxFiles' => 1, 'maxSize' => 10 * 1024 *1024, 'extensions' => ['tif', 'png', 'jpg', 'doc', 'docx', 'xls', 'xlsx','ppt', 'pptx', 'pdf', 'zip', 'rar', '7z', 'txt']],
            [['link'], 'string', 'max' => 500],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'file_uuid' => Yii::t('app', 'File Uuid'),
            'title' => Yii::t('app', 'Title'),
            'ext' => Yii::t('app', 'Extension'),
            'link' => Yii::t('app', 'Link'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


}
