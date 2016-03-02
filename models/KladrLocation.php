<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\kladr\models;

use paulzi\adjacencyList\AdjacencyListBehavior;
use paulzi\autotree\AutoTreeTrait;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%kladr_location}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $name_short
 * @property string $name_full
 * @property string $zip
 * @property string $okato
 * @property string $type
 * @property string $kladr_api_id
 * @property string $active
 * @property integer $parent_id
 * @property integer $sort
 *
 * @property-read string $typeName
 * @property-read string $fullName
 */
class KladrLocation extends \skeeks\cms\models\Core
{
    /**
     * Страна
     */
    const TYPE_COUNTRY      = 'country';

    /**
     * Регион, область
     */
    const TYPE_REGION       = 'region';

    /**
     * Район
     */
    const TYPE_DISTRICT     = 'district';

    /**
     * Город
     */
    const TYPE_CITY         = 'city';

    /**
     * Поселок
     */
    const TYPE_VILLAGE         = 'village';

    /**
     * Деревня
     */
    const TYPE_VILLAGE_SMALL         = 'village_sm';

    /**
     * Улица
     */
    const TYPE_STREET       = 'street';

    /**
     * Строение
     */
    const TYPE_BUILDING     = 'building';

    /**
     * @return array
     */
    static public function possibleTypes()
    {
        return [
            self::TYPE_COUNTRY      => 'Страна',
            self::TYPE_REGION       => 'Регион',
            self::TYPE_DISTRICT     => 'Район',
            self::TYPE_CITY         => 'Город',
            self::TYPE_VILLAGE      => 'Поселок',
            self::TYPE_VILLAGE_SMALL=> 'Деревня',
            self::TYPE_STREET       => 'Улица',
            self::TYPE_BUILDING     => 'Строение',
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%kladr_location}}';
    }

    use AutoTreeTrait;

    public function behaviors()
    {
        return [
            ['class' => AdjacencyListBehavior::className()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'parent_id', 'sort'], 'integer'],
            [['name', 'type'], 'required'],
            [['name', 'name_short', 'name_full'], 'string', 'max' => 255],
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
            'name_short' => Yii::t('app', 'Полное название (сокр)'),
            'name_full' => Yii::t('app', 'Полное название'),
            'zip' => Yii::t('app', 'Zip'),
            'okato' => Yii::t('app', 'Okato'),
            'type' => Yii::t('app', 'Тип'),
            'kladr_api_id' => Yii::t('app', 'Kladr Api ID'),
            'active' => Yii::t('app', 'Active'),
            'parent_id' => Yii::t('app', 'Родительское местоположение'),
            'sort' => Yii::t('app', 'Sort'),
        ];
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return (string) ArrayHelper::getValue(self::possibleTypes(), $this->type);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->name_full)
        {
            return $this->name_full;
        } else
        {
            return $this->name;
        }
    }

}