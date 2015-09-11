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
                                if ($model->parent_id)
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
        return $this->render('update-database', [
            'abc' => $this->_getAbs()
        ]);
    }

    /**
     * Импорт регионов
     *
     * @return array|RequestResponse
     */
    public function actionImportRegions()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $rr->data =
            [
                'yes'       => 0,
                'no'        => 0,
                'offset'    => \Yii::$app->request->post('offset', 0),
                'total'     => 0,
                'nextOffset'=> 0,
            ];

            if (!\Yii::$app->kladr->isReady() || !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            $query              = \Yii::$app->kladr->createApiQuery();
            $query->ContentName = \Yii::$app->request->post('char');
            $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::Region;
            $query->WithParent  = 1;
            $query->offset      = \Yii::$app->request->post('offset', 0);

            $arResult = \Yii::$app->kladr->createApi()->QueryToArray($query);

            $rr->data['total'] = count($arResult);

            if (count($arResult) >= \Yii::$app->kladr->kladrRequestLimit)
            {
                $rr->data['nextOffset'] = $rr->data['offset'] + \Yii::$app->kladr->kladrRequestLimit;
            }

            if ($arResult)
            {
                foreach ($arResult as $locationData)
                {
                    if ( $this->_writeLocation($locationData, \Yii::$app->kladr->russiaLocation, \skeeks\cms\kladr\libs\ObjectType::Region) )
                    {
                        $rr->data['yes'] = $rr->data['yes'] + 1;
                    } else
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                    }
                }
            }

            $rr->success    = true;
            $rr->message    = "Импорт завершен";

            return $rr;
        }

        return (array) $rr;
    }


    /**
     * Импорт районов
     *
     * @return array|RequestResponse
     */
    public function actionImportDistricts()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $rr->data =
            [
                'yes'       => 0,
                'no'        => 0,
                'offset'    => \Yii::$app->request->post('offset', 0),
                'total'     => 0,
                'nextOffset'=> 0,
            ];

            if (!\Yii::$app->kladr->isReady() || !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            /**
             * @var $region KladrLocation
             */
            $regions    = KladrLocation::find()->where(['type' => KladrLocation::TYPE_REGION])->all();

            foreach ($regions as $region)
            {
                if ($region->kladr_api_id)
                {
                    $query              = \Yii::$app->kladr->createApiQuery();
                    $query->ContentName = \Yii::$app->request->post('char');
                    $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::District;
                    $query->ParentId    = $region->kladr_api_id;
                    $query->ParentType  = \skeeks\cms\kladr\libs\ObjectType::Region;

                    $query->offset      = \Yii::$app->request->post('offset', 0);
                    $arResult           = \Yii::$app->kladr->createApi()->QueryToArray($query);

                    $rr->data['total'] = $rr->data['total'] + count($arResult);

                    foreach ((array) $arResult as $locationData)
                    {
                        if ( $this->_writeLocation($locationData, $region, KladrLocation::TYPE_DISTRICT) )
                        {
                            $rr->data['yes'] = $rr->data['yes'] + 1;
                        } else
                        {
                            $rr->data['no'] = $rr->data['no'] + 1;
                        }
                    }
                }
            }

            $rr->success    = true;
            $rr->message    = "Импорт завершен";

            return $rr;
        }

        return (array) $rr;
    }




    /**
     * Импорт городов
     *
     * @return array|RequestResponse
     */
    public function actionImportCities()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $rr->data =
            [
                'yes'       => 0,
                'no'        => 0,
                'offset'    => \Yii::$app->request->post('offset', 0),
                'total'     => 0,
                'nextOffset'=> 0,
            ];

            if (!\Yii::$app->kladr->isReady() || !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            $query              = \Yii::$app->kladr->createApiQuery();
            $query->ContentName = \Yii::$app->request->post('char');
            $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::City;
            $query->WithParent  = 1;
            $query->offset      = \Yii::$app->request->post('offset', 0);

            $query->typeCode    = 1; //только города

            $arResult = \Yii::$app->kladr->createApi()->QueryToArray($query);

            $rr->data['total'] = count($arResult);

            if (count($arResult) >= \Yii::$app->kladr->kladrRequestLimit)
            {
                $rr->data['nextOffset'] = $rr->data['offset'] + \Yii::$app->kladr->kladrRequestLimit;
            }

            if ($arResult)
            {
                foreach ($arResult as $locationData)
                {


                    $parents = (array) ArrayHelper::getValue($locationData, 'parents');
                    if (!$parents)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parent = $parents[count($parents) - 1];
                    if (!$parent)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parentId   = (string) ArrayHelper::getValue($parent, 'id');
                    $parent     = KladrLocation::findOne(['kladr_api_id' => $parentId]);

                    if ( $this->_writeLocation($locationData, $parent, KladrLocation::TYPE_CITY) )
                    {
                        $rr->data['yes'] = $rr->data['yes'] + 1;
                    } else
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                    }
                }
            }

            $rr->success    = true;
            $rr->message    = "Импорт завершен";

            return $rr;
        }

        return (array) $rr;
    }

    /**
     * Импорт поселков
     *
     * @return array|RequestResponse
     */
    public function actionImportVillages()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $rr->data =
            [
                'yes'       => 0,
                'no'        => 0,
                'offset'    => \Yii::$app->request->post('offset', 0),
                'total'     => 0,
                'nextOffset'=> 0,
            ];

            if (!\Yii::$app->kladr->isReady() || !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            $query              = \Yii::$app->kladr->createApiQuery();
            $query->ContentName = \Yii::$app->request->post('char');
            $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::City;
            $query->WithParent  = 1;
            $query->offset      = \Yii::$app->request->post('offset', 0);

            $query->typeCode    = 2; //только поселки

            $arResult = \Yii::$app->kladr->createApi()->QueryToArray($query);

            $rr->data['total'] = count($arResult);

            if (count($arResult) >= \Yii::$app->kladr->kladrRequestLimit)
            {
                $rr->data['nextOffset'] = $rr->data['offset'] + \Yii::$app->kladr->kladrRequestLimit;
            }

            if ($arResult)
            {
                foreach ($arResult as $locationData)
                {


                    $parents = (array) ArrayHelper::getValue($locationData, 'parents');
                    if (!$parents)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parent = $parents[count($parents) - 1];
                    if (!$parent)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parentId   = (string) ArrayHelper::getValue($parent, 'id');
                    $parent     = KladrLocation::findOne(['kladr_api_id' => $parentId]);

                    if ( $this->_writeLocation($locationData, $parent, KladrLocation::TYPE_VILLAGE) )
                    {
                        $rr->data['yes'] = $rr->data['yes'] + 1;
                    } else
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                    }
                }
            }

            $rr->success    = true;
            $rr->message    = "Импорт завершен";

            return $rr;
        }

        return (array) $rr;
    }



    /**
     * Импорт деревень
     *
     * @return array|RequestResponse
     */
    public function actionImportVillagesSm()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $rr->data =
            [
                'yes'       => 0,
                'no'        => 0,
                'offset'    => \Yii::$app->request->post('offset', 0),
                'total'     => 0,
                'nextOffset'=> 0,
            ];

            if (!\Yii::$app->kladr->isReady() || !\Yii::$app->request->post('char'))
            {
                $rr->success = false;
                $rr->message = "Некорректные настройки";

                return $rr;
            }

            $query              = \Yii::$app->kladr->createApiQuery();
            $query->ContentName = \Yii::$app->request->post('char');
            $query->ContentType = \skeeks\cms\kladr\libs\ObjectType::City;
            $query->WithParent  = 1;
            $query->offset      = \Yii::$app->request->post('offset', 0);

            $query->typeCode    = 4; //только поселки

            $arResult = \Yii::$app->kladr->createApi()->QueryToArray($query);

            $rr->data['total'] = count($arResult);

            if (count($arResult) >= \Yii::$app->kladr->kladrRequestLimit)
            {
                $rr->data['nextOffset'] = $rr->data['offset'] + \Yii::$app->kladr->kladrRequestLimit;
            }

            if ($arResult)
            {
                foreach ($arResult as $locationData)
                {


                    $parents = (array) ArrayHelper::getValue($locationData, 'parents');
                    if (!$parents)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parent = $parents[count($parents) - 1];
                    if (!$parent)
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                        continue;
                    }

                    $parentId   = (string) ArrayHelper::getValue($parent, 'id');
                    $parent     = KladrLocation::findOne(['kladr_api_id' => $parentId]);

                    if ( $this->_writeLocation($locationData, $parent, KladrLocation::TYPE_VILLAGE_SMALL) )
                    {
                        $rr->data['yes'] = $rr->data['yes'] + 1;
                    } else
                    {
                        $rr->data['no'] = $rr->data['no'] + 1;
                    }
                }
            }

            $rr->success    = true;
            $rr->message    = "Импорт завершен";

            return $rr;
        }

        return (array) $rr;
    }








    /**
     * @param $locationData
     * @param $parent
     * @param $type
     * @return bool
     */
    protected function _writeLocation($locationData, $parent, $type)
    {
        $apiRegion = KladrLocation::findOne(['kladr_api_id' => ArrayHelper::getValue($locationData, 'id')]);

        if ($apiRegion)
        {
            return false;
        }

        $kladrLocation                  = new KladrLocation();
        $kladrLocation->kladr_api_id    = ArrayHelper::getValue($locationData, 'id');
        $kladrLocation->name            = ArrayHelper::getValue($locationData, 'name');
        $kladrLocation->zip             = ArrayHelper::getValue($locationData, 'zip');
        $kladrLocation->type            = $type;
        $kladrLocation->name_short      = ArrayHelper::getValue($locationData, 'name') . " " . ArrayHelper::getValue($locationData, 'typeShort');
        $kladrLocation->name_full       = ArrayHelper::getValue($locationData, 'name') . " " . ArrayHelper::getValue($locationData, 'type');

        return (bool) $kladrLocation->appendTo($parent)->save();
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
