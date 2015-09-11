<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 10.09.2015
 */
/* @var $this yii\web\View */
/* @var $abc array */
?>

<? \yii\bootstrap\Alert::begin([
    'options' => [
      'class' => 'alert-info',
    ],
]); ?>
    Вы можете автоматически наполнить базу адресов на вашем сайте, при помоще сервиса http://kladr-api.ru/.
    Для начала обновления посетите раздел настроек.
<? \yii\bootstrap\Alert::end(); ?>


<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    "pjaxOptions"            =>
    [
        "id" => "sx-stat",
    ],

    "dataProvider"  => new \yii\data\ArrayDataProvider([
        'allModels' => [
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_COUNTRY,
                'name'  => 'Страна',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_COUNTRY])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_REGION,
                'name'  => 'Регион',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_REGION])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_DISTRICT,
                'name'  => 'Район',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_DISTRICT])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_CITY,
                'name'  => 'Город',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_CITY])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE,
                'name'  => 'Поселок',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE_SMALL,
                'name'  => 'Деревня',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE_SMALL])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_STREET,
                'name'  => 'Улица',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_STREET])->count()
            ],
            [
                'type'  => \skeeks\cms\kladr\models\KladrLocation::TYPE_BUILDING,
                'name'  => 'Строение',
                'count' => \skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_BUILDING])->count()
            ],
        ]
    ]),
    'columns' =>
    [
        [
            'attribute'     => 'name',
            'label'         => 'Тип местоположения'
        ],
        [
            'attribute'     => 'count',
            'label'         => 'Количество'
        ],

        [
            'format'        => 'raw',
            'label'         => '',
            'attribute'     => 'count',
            'class'         => \yii\grid\DataColumn::className(),
            'value'         => function($data)
            {
                $name = \yii\helpers\ArrayHelper::getValue($data, 'name');
                $type = \yii\helpers\ArrayHelper::getValue($data, 'type');
                if ($type == \skeeks\cms\kladr\models\KladrLocation::TYPE_COUNTRY)
                {
                    return "";
                }

                return \yii\helpers\Html::a('Запустить импорт', '#', [
                    'class' => 'btn-primary btn btn-xs',
                    'onclick' => new \yii\web\JsExpression(<<<JS
                    sx.KladrImport.execute('{$type}', '{$name}');
JS
)
                ]);
            }
        ],
    ]
]); ?>


<div class="sx-progress-global" id="sx-progress-global" style="display: none;">
    <span style="vertical-align:middle;"><h3>Общий процесс (Выполнено <span class="sx-executing-ptc">0</span>%)</h3></span>
    <span style="vertical-align:middle;"><span class="sx-executing-task-name"></span></span>
    <div>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-success"></div>
        </div>
    </div>
    <hr />
</div>


<div class="sx-progress-tasks" id="sx-progress-tasks" style="display: none;">
    <span style="vertical-align:middle;"><h3>Процесс импорта "<span id="sx-executing-name">0</span>" (Выполнено <span class="sx-executing-ptc">0</span>%)</h3></span>
    <span style="vertical-align:middle;"><span class="sx-executing-task-name"></span></span>
    <div>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-success"></div>
        </div>
    </div>

    <div style="text-align: center">
        <h1>Всего обработано:   <span id="sx-task-total">0</span></h1>
        <h1>Добавлено в базу:   <span id="sx-task-yes">0</span></h1>
        <h1>Пропущено:          <span id="sx-task-no">0</span></h1>
    </div>
    <hr />
</div>

<?

$data = [
    'backend'           => [
        \skeeks\cms\kladr\models\KladrLocation::TYPE_REGION    => (string) \skeeks\cms\helpers\UrlHelper::construct('kladr/admin-kladr-location/import-regions')->enableAdmin(),
        \skeeks\cms\kladr\models\KladrLocation::TYPE_DISTRICT  => (string) \skeeks\cms\helpers\UrlHelper::construct('kladr/admin-kladr-location/import-districts')->enableAdmin(),
        \skeeks\cms\kladr\models\KladrLocation::TYPE_CITY      => (string) \skeeks\cms\helpers\UrlHelper::construct('kladr/admin-kladr-location/import-cities')->enableAdmin(),
        \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE   => (string) \skeeks\cms\helpers\UrlHelper::construct('kladr/admin-kladr-location/import-villages')->enableAdmin(),
        \skeeks\cms\kladr\models\KladrLocation::TYPE_VILLAGE_SMALL   => (string) \skeeks\cms\helpers\UrlHelper::construct('kladr/admin-kladr-location/import-villages-sm')->enableAdmin()
    ],

    'abc'               => (array) $abc,
    'pjaxId'            => 'sx-stat'
];

$dataJson = \yii\helpers\Json::encode($data);

\skeeks\cms\assets\JsTaskManagerAsset::register($this);

$this->registerJs(<<<JS
(function(sx, $, _)
{

    sx.classes.KladrImportCharTask = sx.classes.tasks.AjaxTask.extend({

        _initQuery: function()
        {
            var self = this;

            new sx.classes.AjaxHandlerNoLoader(this.get("ajaxQuery"));

            this.getAjaxQuery().bind('success', function(e, data)
            {
                var nextOffset = Number(data.response.data.nextOffset);
                if (nextOffset > 0)
                {
                    self.trigger("updateData", data.response.data);
                    self._execute(nextOffset);
                } else
                {
                    self.trigger("complete", {
                        'task'      : self,
                        'result'    : data.response.data
                    });

                    self.trigger("updateData", data.response.data);
                }
            });

            this.getAjaxQuery().bind('error', function(e, data)
            {
                self.trigger("complete", {
                    'task'      : self,
                    'result'    : data
                });
            });

            return this;
        },

        execute: function()
        {
            var self = this;

            this.trigger("beforeExecute", {
                'task' : this
            });

            this._execute(0);
        },

        _execute: function(offset)
        {
            offset = Number(offset);

            if (offset > 0)
            {
                var data = this.getAjaxQuery().getData();
                data = _.extend(data, {'offset': offset});
                this.getAjaxQuery().setData(data);
            }

            this.getAjaxQuery().execute();
        }

    });



    sx.classes.KladrImportProgressBar = sx.classes.tasks.ProgressBar.extend({

        _init: function()
        {
            var self = this;

            this.applyParentMethod(sx.classes.tasks.ProgressBar, '_init', []);

            this.bind('update', function(e, data)
            {
                $(".sx-executing-task-name", self.getWrapper()).empty().append(data.Task.get('name'));
            });

            this.bind('updateProgressBar', function(e, data)
            {
                $(".sx-executing-ptc", self.getWrapper()).empty().append(self.getExecutedPtc());
            });
        }

    });

    sx.classes.KladrImport = sx.classes.Component.extend({

        _init: function()
        {
            var self = this;

            this.TaskManager = new sx.classes.tasks.Manager({
                'tasks' : [],
                'delayQueque' : 500
            });

            this.GlobalTaskManager = new sx.classes.tasks.Manager({
                'tasks' : [],
                'delayQueque' : 2000
            });

            this.ProgressBar = new sx.classes.KladrImportProgressBar(this.TaskManager, "#sx-progress-tasks");
            this.ProgressBarGlobal = new sx.classes.KladrImportProgressBar(this.GlobalTaskManager, "#sx-progress-global");


            this.TaskManager.bind("completeTask", function(e, data)
            {
                console.log('1');
                console.log(data);
            });

            this.TaskManager.bind('start', function()
            {
                self.Blocker  = new sx.classes.Blocker("#" + self.get('pjaxId'));
                self.Blocker.block();
                sx.App.Menu.block();
            });

            this.TaskManager.bind('stop', function()
            {
                self.Blocker.unblock();
                sx.App.Menu.unblock();
                $.pjax.reload('#' + self.get('pjaxId'), {});
            });

            this.total      = 0;
            this.yes        = 0;
            this.no         = 0;

            this.bind('updateData', function()
            {
                $("#sx-task-total").empty().append(self.total);
                $("#sx-task-yes").empty().append(self.yes);
                $("#sx-task-no").empty().append(self.no);
            });
        },

        execute: function(type, name)
        {
            $("#sx-executing-name").empty().append(name);

            var self = this;

            self.total      = 0;
            self.yes        = 0;
            self.no         = 0;

            self.trigger('updateData');

            tasks = [];

            _.each(this.get('abc'), function(char, key)
            {
                var ajaxQuery = sx.ajax.preparePostQuery(self.get('backend')[type], {
                    'char'      : char,
                    'type'      : type,
                });

                var task = new sx.classes.KladrImportCharTask(ajaxQuery, {
                    'name'      :   'На букву ' + char,
                    'offset'    :   0,
                });

                task.bind('updateData', function(e, data)
                {
                    self.total      = self.total + data.total;
                    self.yes        = self.yes + data.yes;
                    self.no         = self.no + data.no;

                    self.trigger('updateData');
                });

                tasks.push(task);
            });

            this.TaskManager.setTasks(tasks);
            this.TaskManager.start();
        },


    });

    sx.KladrImport = new sx.classes.KladrImport({$dataJson});
})(sx, sx.$, sx._);
JS
)
?>


