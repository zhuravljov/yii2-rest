<?php
use yii\bootstrap\ActiveForm;
use zhuravljov\yii\rest\models\RequestForm;

/**
 * @var \yii\web\View $this
 * @var RequestForm $model
 * @var ActiveForm $form
 */
?>
<div class="rest-default-form">
    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'fieldConfig' => [
            'labelOptions' => ['class' => 'control-label sr-only'],
        ],
    ]) ?>
        <?= $form->field($model, 'tab', [
            'template' => '{input}',
            'options' => ['class' => ''],
        ])->hiddenInput() ?>

        <div class="row">
            <div class="col-sm-2">

                <?= $form->field($model, 'method', [
                    'options' => ['class' => 'form-group form-group-lg'],
                ])->dropDownList($model->methodLabels()) ?>

            </div>
            <div class="col-sm-10">

                <?= $form->field($model, 'endpoint', [
                    'template' => <<<HTML
                        {label}
                        <div class="input-group">
                            <div class="input-group-addon">$model->baseUrl</div>
                            {input}
                            <span class="input-group-btn">
                                <button class="btn btn-lg btn-primary" type="submit" tabindex="-1">Send</button>
                            </span>
                        </div>
                        {hint}
                        {error}
HTML
                    ,
                    'options' => ['class' => 'form-group form-group-lg'],
                ])->textInput([
                    'placeholder' => 'endpoint',
                    'autofocus' => true,
                ]) ?>

            </div>
        </div>

        <ul class="nav nav-tabs">
            <li class="<?= $model->tab == 1 ? 'active' : '' ?>">
                <a href="#request-query" data-toggle="tab" tabindex="-1">
                    Query
                </a>
            </li>
            <li class="<?= $model->tab == 2 ? 'active' : '' ?>">
                <a href="#request-body" data-toggle="tab" tabindex="-1">
                    Body
                </a>
            </li>
            <li class="<?= $model->tab == 3 ? 'active' : '' ?>">
                <a href="#request-headers" data-toggle="tab" tabindex="-1">
                    Headers
                </a>
            </li>
        </ul>

        <div class="tab-content">

            <div id="request-query" class="tab-pane <?= $model->tab == 1 ? 'active' : '' ?>">
                <?= $this->render('_params', [
                    'model' => $model,
                    'form' => $form,
                    'keyAttribute' => 'queryKeys',
                    'valueAttribute' => 'queryValues',
                    'activeAttribute' => 'queryActives',
                ]) ?>
            </div><!-- #request-params -->

            <div id="request-body" class="tab-pane <?= $model->tab == 2 ? 'active' : '' ?>">
                <?= $this->render('_params', [
                    'model' => $model,
                    'form' => $form,
                    'keyAttribute' => 'bodyKeys',
                    'valueAttribute' => 'bodyValues',
                    'activeAttribute' => 'bodyActives',
                ]) ?>
            </div><!-- #request-body -->

            <div id="request-headers" class="tab-pane <?= $model->tab == 3 ? 'active' : '' ?>">
                <?= $this->render('_params', [
                    'model' => $model,
                    'form' => $form,
                    'keyAttribute' => 'headerKeys',
                    'valueAttribute' => 'headerValues',
                    'activeAttribute' => 'headerActives',
                ]) ?>
            </div><!-- #request-headers -->

        </div>

    <?php ActiveForm::end() ?>
</div>
<?php
$this->registerJs(<<<'JS'

var inputSenderTab = $('#sender-tab');
$('a[href=#request-query]').on('show.bs.tab', function() {
    inputSenderTab.val(1);
});
$('a[href=#request-body]').on('show.bs.tab', function() {
    inputSenderTab.val(2);
});
$('a[href=#request-headers]').on('show.bs.tab', function() {
    inputSenderTab.val(3);
});

JS
);