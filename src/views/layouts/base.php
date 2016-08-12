<?php
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var string $menu
 * @var string $content
 */

$asset = \zhuravljov\yii\rest\RestAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => 'REST Client',
            'brandUrl' => ['request/create'],
            'options' => ['class' => 'navbar-inverse navbar-fixed-top'],
        ]);

        echo $menu;

        echo Nav::widget([
            'options' => ['class' => 'nav navbar-nav navbar-right'],
            'items' => [
                [
                    'label' => 'Application',
                    'url' => Yii::$app->homeUrl,
                ],
            ],
        ]);

        NavBar::end();
        ?>

        <div class="container">
            <?= $this->render('_alerts') ?>
            <?= $content ?>
        </div><!-- .container -->
    </div><!-- .wrap -->

    <footer class="footer">
        <div class="container">
            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
