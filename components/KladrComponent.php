<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
namespace skeeks\cms\kladr\components;
use skeeks\cms\base\Component;
use skeeks\cms\kladr\models\KladrLocation;
use yii\helpers\ArrayHelper;

/**
 * Class KladrComponent
 * @package skeeks\cms\kladr\components
 */
class KladrComponent extends Component
{

    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name'          => 'База местоположений',
        ]);
    }

    /**
     * @var string
     */
    public $kladrApiToken   = "55ef04730a69de3d758b456a";
    public $russiaId        = 1;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['kladrApiToken'], 'string'],
            [['russiaId'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'kladrApiToken'                     => 'Токен с kladr-api.ru',
            'russiaId'                          => 'Россия',
        ]);
    }
}