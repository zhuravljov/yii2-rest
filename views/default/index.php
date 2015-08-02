<?php
/**
 * @var \yii\web\View $this
 * @var \zhuravljov\yii\rest\models\Sender $model
 */
?>
<div class="rest-default-index">
    <div class="row">
        <div class="col-lg-9">

            <?= $this->render('_form', ['model' => $model]) ?>

        </div>
        <div class="col-lg-3">

            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#collections" data-toggle="tab">Collections</a>
                </li>
                <li >
                    <a href="#history" data-toggle="tab">History</a>
                </li>
            </ul>

            <div class="tab-content">

                <div id="collections" class="tab-pane active">
                    collections
                </div><!-- #collections -->

                <div id="history" class="tab-pane">
                    history
                </div><!-- #history -->

            </div>

        </div>
    </div>
</div>
