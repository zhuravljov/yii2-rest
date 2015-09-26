<?php
use yii\bootstrap\ActiveForm;

/**
 * @var \yii\web\View $this
 * @var \zhuravljov\yii\rest\models\ImportForm $model
 * @var ActiveForm $form
 */
?>
<div class="rest-collection-import-form">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'options' => ['enctype' => 'multipart/form-data'],
    ]) ?>
        <?= $form->field($model, 'dataFile')->fileInput() ?>
        <div class="form-group">
            <button type="submit" class="btn btn-success">Import</button>
        </div>
    <?php ActiveForm::end() ?>
</div>