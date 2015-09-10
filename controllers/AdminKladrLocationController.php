<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\kladr\controllers;
use skeeks\cms\kladr\models\KladrLocation;
use yii\helpers\ArrayHelper;

/**
 * Class AdminKladrLocationController
 * @package skeeks\cms\kladr\controllers
 */
class AdminKladrLocationController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = "База адресов";
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = KladrLocation::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "columns"      => [
                        'name',
                        'value',
                    ],
                ]
            ]
        );
    }

}
