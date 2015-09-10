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

<?= $form->fieldSet('Основное'); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]); ?>

    <?= $form->fieldSelect($model, 'type', \skeeks\cms\kladr\models\KladrLocation::possibleTypes()); ?>
    <?= $form->fieldSelect($model, 'parent_id', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\kladr\models\KladrLocation::find()->all(),
        'id', 'name'
    )); ?>

    <?= $form->field($model, 'name_short')->textInput(['maxlength' => 255]); ?>
    <?= $form->field($model, 'name_full')->textInput(['maxlength' => 255]); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
