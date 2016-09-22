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
use yii\widgets\ActiveForm;

/**
 * @property KladrLocation $russiaLocation
 *
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

    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->fieldSet(\Yii::t('skeeks/seo', 'Keywords'));

            echo $form->field($this, 'kladrApiToken');
            echo $form->field($this, 'kladrRequestLimit');
            echo $form->field($this, 'russiaId')->listBox(
                \yii\helpers\ArrayHelper::map(\skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_COUNTRY])->all(),
                'id', 'name'),
                [
                    'size' => 1
                ]
            );

        echo $form->fieldSetEnd();


    }
    /**
     * @var string
     */
    public $kladrApiToken               = "55ef04730a69de3d758b456a";
    public $russiaId                    = 1;
    public $kladrRequestLimit           = 300;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['kladrApiToken'], 'string'],
            [['russiaId'], 'integer'],
            [['kladrRequestLimit'], 'integer', 'max' => 400, 'min' => 5],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'kladrApiToken'                     => 'Токен с kladr-api.ru',
            'russiaId'                          => 'Россия',
            'kladrRequestLimit'                 => 'За один запрос к апи, выбирается столько записей',
        ]);
    }

    /**
     * Компонент настроен и готов к работе?
     *
     * @return bool
     */
    public function isReady()
    {
        return (bool) ($this->russiaLocation && $this->kladrApiToken);
    }


    /**
     * @return KladrLocation
     */
    public function getRussiaLocation()
    {
        if (!$this->russiaId)
        {
            return null;
        }

        return KladrLocation::findOne($this->russiaId);
    }

    /**
     * @return \skeeks\cms\kladr\libs\Api
     */
    public function createApi()
    {
        return new \skeeks\cms\kladr\libs\Api($this->kladrApiToken);
    }

    /**
     * @return \skeeks\cms\kladr\libs\Query
     */
    public function createApiQuery()
    {
        $query              = new \skeeks\cms\kladr\libs\Query();
        $query->Limit       = $this->kladrRequestLimit;

        return $query;
    }
}