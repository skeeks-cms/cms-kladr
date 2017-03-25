<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */
return [

    'other' =>
    [
        'items' =>
        [
            [
                "label"     => "База местопложений",
                "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],

                'items' =>
                [
                    [
                        "label"     => "База местопложений",
                        "url"       => ["kladr/admin-kladr-location"],
                        "img"       => ['\skeeks\cms\kladr\assets\Asset', 'icons/global.png'],
                    ],

                    [
                        "label" => "Настройки",
                        "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\kladr\components\KladrComponent'],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                        "activeCallback"       => function($adminMenuItem)
                        {
                            return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                        },
                    ],
                ]
            ],
        ]
    ]
];