<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "parent_post".
 *
 * @property int $id
 * @property int $user_id
 * @property int $zip_code
 * @property string $job_type
 * @property string $type_of_help
 * @property string $summary
 * @property string $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $expired_at
 * @property string $remark
 * @property User $user
 */
class ParentPost extends \yii\db\ActiveRecord
{
    /**
     * 正常（审核通过）.
     */
    const STATUS_ACTIVE = 1;
    /**
     * 审核中.
     */
    const STATUS_PENDING = 0;
    /**
     * 审核失败.
     */
    const STATUS_FAILED = 2;
    /**
     * 删除.
     */
    const STATUS_DELETED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'parent_post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'zip_code', 'job_type', 'type_of_help', 'description', 'expired_at', 'summary'], 'required'],
            [['user_id', 'zip_code', 'expired_at'], 'integer'],
            [['description', 'summary', 'remark'], 'string'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['job_type', 'type_of_help'], 'string', 'max' => 200],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'zip_code'     => 'Zip Code',
            'job_type'     => 'Job Type',
            'type_of_help' => 'Type Of Help',
            'summary'      => 'Title',
            'description'  => 'Detailed Information',
            'status'       => 'Status',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
            'expired_at'   => 'Expired At',
            'remark'       => 'Remark',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param bool $insert
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->description = nl2br($this->description);

            return true;
        } else {
            return false;
        }
    }
}
