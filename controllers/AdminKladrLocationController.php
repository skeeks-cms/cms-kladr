<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\kladr\controllers;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\kladr\models\KladrLocation;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminKladrLocationController
 * @package skeeks\cms\kladr\controllers
 */
class AdminKladrLocationController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                     = "База местоположений";
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
                        [
                            'class'     => DataColumn::className(),
                            'attribute' => 'name',
                            'format'    => 'raw',
                            'value'     => function(KladrLocation $model)
                            {
                                if ($model->parents)
                                {
                                    return $model->fullName . " <small>(" . implode(", ", ArrayHelper::map($model->parents, 'id', 'fullName')) . ") </small>";
                                } else
                                {
                                    return $model->fullName;
                                }

                            },
                        ],

                        [
                            'class'     => DataColumn::className(),
                            'attribute' => 'type',
                            'filter'    => KladrLocation::possibleTypes(),
                            'value'     => function(KladrLocation $model)
                            {
                                return $model->typeName;
                            },
                        ]
                    ],
                ],

                'update-database' =>
                [
                    "class"         => AdminAction::className(),
                    "name"          => "Импорт местоположений",
                    "icon"          => "glyphicon glyphicon-paperclip",
                    "callback"      => [$this, 'actionUpdateDatabase'],
                ],
            ]
        );
    }


    public function actionUpdateDatabase()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            if (!\Yii::$app->kladr->russiaId || !\Yii::$app->kladr->kladrApiToken || !\Yii::$app->request->post('type')|| !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            $this->_importLocations(\Yii::$app->request->post('type'), \Yii::$app->request->post('char'));

            $rr->success = true;
            $rr->message = "Импорт завершен";

            return $rr;
        }

        return $this->render('update-database', [
            'abc' => $this->_getAbs()
        ]);
    }


    protected function _importLocations($codeType, $char)
    {
        if ($codeType == KladrLocation::TYPE_REGION)
        {
            $this->_importRegions($char);
        }

        if ($codeType == KladrLocation::TYPE_DISTRICT)
        {
            $this->_importDistricts($char);
        }

        if ($codeType == KladrLocation::TYPE_CITY)
        {
            $this->_importCities($char);
        }

        if ($codeType == KladrLocation::TYPE_STREET)
        {
            $this->_importStreets($char);
        }

        if ($codeType == KladrLocation::TYPE_BUILDING)
        {
            $this->_importBuilding($char);
        }
    }


    protected function _importRegions($char)
    {
        /**
         * @var $russia KladrLocation
         */
        $api    = new \skeeks\cms\kladr\libs\Api(\Yii::$app->kladr->kladrApiToken);
        $russia = KladrLocation::findOne(\Yii::$app->kladr->russiaId);

        if (!$russia)
        {
            return false;
        }



        $query              = new \skeeks\cms\kladr\libs\Query();
        $query->Limit       = 400;
        $query->ContentName = $char;
        $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::Region;

        $arResult = $api->QueryToArray($query);

        if ($arResult)
        {
            foreach ($arResult as $locationData)
            {
                $this->_writeLocation($locationData, $russia, \skeeks\cms\kladr\libs\ObjectType::Region);
            }
        }
    }

    protected function _importCities($char, $offset = 0)
    {
        if ($offset > 0)
        {
            //die;
        }


        $api        = new \skeeks\cms\kladr\libs\Api(\Yii::$app->kladr->kladrApiToken);

        $query              = new \skeeks\cms\kladr\libs\Query();
        $query->ContentName = $char;
        $query->Limit       = 400;
        $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::City;
        $query->WithParent  = 1;
        $query->offset      = $offset;


        $query->typeCode    = 1; //только города


        $arResult = $api->QueryToArray($query);

        if ($arResult)
        {
            foreach ($arResult as $locationData)
            {
                $parents = (array) ArrayHelper::getValue($locationData, 'parents');
                if (!$parents)
                {
                    continue;
                }

                $parent = $parents[count($parents) - 1];
                if (!$parent)
                {
                    continue;
                }

                $parentId   = (string) ArrayHelper::getValue($parent, 'id');
                $parent     = KladrLocation::findOne(['kladr_api_id' => $parentId]);

                $this->_writeLocation($locationData, $parent, \skeeks\cms\kladr\libs\ObjectType::City);
            }
        }

        if (count($arResult) >= 400)
        {
            $this->_importCities($char, 400 + $offset);
        }
    }


    protected function _importStreets($char, $offset = 0)
    {
        if ($offset > 0)
        {
            //die;
        }


        $api        = new \skeeks\cms\kladr\libs\Api(\Yii::$app->kladr->kladrApiToken);

        $query              = new \skeeks\cms\kladr\libs\Query();
        $query->ContentName = $char;
        $query->Limit       = 400;
        $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::Street;
        $query->WithParent  = 1;
        $query->offset      = $offset;


        $query->typeCode    = 1; //только города


        $arResult = $api->QueryToArray($query);

        if ($arResult)
        {
            foreach ($arResult as $locationData)
            {
                $parents = (array) ArrayHelper::getValue($locationData, 'parents');
                if (!$parents)
                {
                    continue;
                }

                $parent = $parents[count($parents) - 1];
                if (!$parent)
                {
                    continue;
                }

                $parentId   = (string) ArrayHelper::getValue($parent, 'id');
                $parent     = KladrLocation::findOne(['kladr_api_id' => $parentId]);

                $this->_writeLocation($locationData, $parent, \skeeks\cms\kladr\libs\ObjectType::Street);
            }
        }

        if (count($arResult) >= 400)
        {
            $this->_importStreets($char, 400 + $offset);
        }
    }



    protected function _importBuilding()
    {
        /**
         * @var $region KladrLocation
         */
        $api        = new \skeeks\cms\kladr\libs\Api(\Yii::$app->kladr->kladrApiToken);
        $regions    = KladrLocation::find()->where(['type' => \skeeks\cms\kladr\libs\ObjectType::Street])->all();

        foreach ($regions as $region)
        {
            if ($region->kladr_api_id)
            {



                    foreach ($this->_getAbs() as $char)
                    {
                        $query              = new \skeeks\cms\kladr\libs\Query();
                        $query->ContentName = $char;
                        $query->Limit       = 400;
                        $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::Building;
                        $query->ParentId    = $region->kladr_api_id;

                        $arResult = $api->QueryToArray($query);

                        if ($arResult)
                        {
                            foreach ($arResult as $locationData)
                            {
                                $this->_writeLocation($locationData, $region, \skeeks\cms\kladr\libs\ObjectType::Building);
                            }
                        }

                    }

            }
        }




    }

    protected function _importDistricts($char)
    {
        /**
         * @var $region KladrLocation
         */
        $api        = new \skeeks\cms\kladr\libs\Api(\Yii::$app->kladr->kladrApiToken);
        $regions    = KladrLocation::find()->where(['type' => \skeeks\cms\kladr\libs\ObjectType::Region])->all();

        foreach ($regions as $region)
        {
            if ($region->kladr_api_id)
            {


                    $query              = new \skeeks\cms\kladr\libs\Query();
                    $query->Limit       = 400;
                    $query->ContentName = $char;
                    $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::District;
                    $query->ParentId    = $region->kladr_api_id;
                    $query->ParentType  = \skeeks\cms\kladr\libs\ObjectType::Region;

                    $arResult = $api->QueryToArray($query);

                    if ($arResult)
                    {
                        foreach ($arResult as $locationData)
                        {
                            $this->_writeLocation($locationData, $region, \skeeks\cms\kladr\libs\ObjectType::District);
                        }
                    }


            }
        }
    }

    protected function _writeLocation($locationData, $parent, $type)
    {
        $apiRegion = KladrLocation::findOne(['kladr_api_id' => ArrayHelper::getValue($locationData, 'id')]);

        if ($apiRegion)
        {
            return;
        }

        $kladrLocation                  = new KladrLocation();
        $kladrLocation->kladr_api_id    = ArrayHelper::getValue($locationData, 'id');
        $kladrLocation->name            = ArrayHelper::getValue($locationData, 'name');
        $kladrLocation->zip             = ArrayHelper::getValue($locationData, 'zip');
        $kladrLocation->type            = $type;
        $kladrLocation->name_short      = ArrayHelper::getValue($locationData, 'name') . " " . ArrayHelper::getValue($locationData, 'typeShort');
        $kladrLocation->name_full       = ArrayHelper::getValue($locationData, 'name') . " " . ArrayHelper::getValue($locationData, 'type');

        $kladrLocation->appendTo($parent)->save();
    }

    protected function _getAbs()
    {
        $abs = ['а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','щ','ш','ь','ы','ъ','э','ю','я', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return $abs;
    }




    protected function _getAbsMore()
    {
        $abs = $this->_getAbs();
        foreach ($this->_getAbs() as $char)
        {
            $abs = array_merge($abs, $this->_addAllAbs($char));
        }

        return $abs;
    }

    /**
     * @param $string
     * @return array
     */
    public function _addAllAbs($string)
    {
        $result = [];
        foreach ($this->_getAbs() as $char)
        {
            $result[] = $string . $char;
        }

        return $result;
    }
}
