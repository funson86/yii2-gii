<?php

namespace common\models;

use Yii;

/**
 * This is the base model class for models which add Status and YesNo
 *
 * @author funson86 <funson86@gmail.com>
 * @since 2.0
 */
class BaseModel extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = -1;

    const YES = 0;
    const NO = 1;

    public static function getStatusLabels($id = null)
    {
        $data = [
            self::STATUS_ACTIVE => Yii::t('app', 'STATUS_ACTIVE'),
            self::STATUS_INACTIVE => Yii::t('app', 'STATUS_INACTIVE'),
            self::STATUS_DELETED => Yii::t('app', 'STATUS_DELETED'),
        ];

        if ($id !== null && isset($data[$id])) {
            return $data[$id];
        } else {
            return $data;
        }
    }

    /**
     * return label or labels array
     *
     * @param  integer $id
     * @return string or array
     */
    public static function getYesNoLabels($id = null)
    {
        $data = [
            self::YES => Yii::t('app', 'YES'),
            self::NO => Yii::t('app', 'NO'),
        ];

        if ($id !== null && isset($data[$id])) {
            return $data[$id];
        } else {
            return $data;
        }
    }

}
