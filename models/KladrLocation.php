<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (ÑêèêÑ)
 * @date 10.09.2015
 */
namespace skeeks\cms\kladr\models;

use Yii;

/**
 * This is the model class for table "{{%kladr_location}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $zip
 * @property string $okato
 * @property string $type
 * @property string $kladr_api_id
 * @property string $active
 * @property integer $parent_id
 * @property integer $sort
 */
class KladrLocation extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%kladr_location}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'parent_id', 'sort'], 'integer'],
            [['name', 'type'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['zip', 'okato', 'kladr_api_id'], 'string', 'max' => 20],
            [['type'], 'string', 'max' => 10],
            [['active'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Name'),
            'zip' => Yii::t('app', 'Zip'),
            'okato' => Yii::t('app', 'Okato'),
            'type' => Yii::t('app', 'Type'),
            'kladr_api_id' => Yii::t('app', 'Kladr Api ID'),
            'active' => Yii::t('app', 'Active'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'sort' => Yii::t('app', 'Sort'),
        ];
    }

}