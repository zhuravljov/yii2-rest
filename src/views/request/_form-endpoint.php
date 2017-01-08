<?php
/**
 * @var \yii\web\View $this
 * @var string $baseUrl
 */

use yii\helpers\Html;
?>
{label}
<div class="input-group endpoint">
    <div class="input-group-addon" title="<?= Html::encode($baseUrl) ?>">
        <?= Html::encode($baseUrl) ?>
    </div>
    {input}
    <span class="input-group-btn">
        <button class="btn btn-lg btn-primary" type="submit" tabindex="-1">Send</button>
    </span>
</div>
{hint}
{error}
<?php
$this->registerCss(<<<'CSS'

.endpoint > .input-group-addon {
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 16px;
}

CSS
);