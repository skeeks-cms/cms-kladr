<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.08.2015
 */
return [

    'components' =>
    [
        'kladr' => [
            'class'         => 'skeeks\cms\kladr\components\KladrComponent',
        ],

        'i18n' => [
            'translations' =>
            [
                'skeeks/cms-kladr' => [
                    'class'             => 'yii\i18n\PhpMessageSource',
                    'basePath'          => '@skeeks/cms/kladr/messages',
                    'fileMap' => [
                        'skeeks/cms-kladr' => 'main.php',
                    ],
                ]
            ]
        ]
    ],

    'modules' =>
    [
        'kladr' => [
            'class'         => 'skeeks\cms\kladr\Module',
        ]
    ]
];