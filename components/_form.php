<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Настройки импорта'); ?>

    <? \yii\bootstrap\Alert::begin([
        'options' => [
          'class' => 'alert-info',
        ],
    ]); ?>
        Сервис kladr-api.ru используется для автоматического обновления базы адресов России. На сайте kladr-api.ru необходимо получить токен.
    <? \yii\bootstrap\Alert::end(); ?>
    <?= $form->field($model, 'kladrApiToken')->textInput(['maxlength' => 255]); ?>
    <?= $form->fieldSelect($model, 'russiaId', \yii\helpers\ArrayHelper::map(\skeeks\cms\kladr\models\KladrLocation::find()->where(['type' => \skeeks\cms\kladr\models\KladrLocation::TYPE_COUNTRY])->all(),
        'id', 'name'))
        ->hint('Необходимо указать российское местоположение'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Импорт местоположений'); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
