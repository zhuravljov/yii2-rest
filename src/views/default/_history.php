<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var string $activeTag
 * @var array $items
 */
?>
<div class="rest-default-history">

    <ul id="history-list2" class="request-list">
        <?php foreach (array_reverse($items) as $tag => $row): ?>
            <?php
            $options = ['data-tag' => $tag];

            if ($row['status'] < 300) {
                Html::addCssClass($options, 'success');
            } elseif ($row['status'] < 400) {
                Html::addCssClass($options, 'info');
            } elseif ($row['status'] < 500) {
                Html::addCssClass($options, 'warning');
            } else {
                Html::addCssClass($options, 'danger');
            }

            if ($tag === $activeTag) {
                Html::addCssClass($options, 'active');
            }
            ?>
            <li <?= Html::renderTagAttributes($options) ?>>
                <a href="<?= Url::to(['index', 'tag' => $tag]) ?>">
                    <span class="request-name">
                        <span class="request-method">
                            <?= Html::encode($row['method']) ?>
                        </span>
                        <span class="request-endpoint">
                            <?= Html::encode($row['endpoint']) ?>
                        </span>
                    </span>
                </a>
                <div class="actions">
                    <?= Html::a('&plus;', ['add-to-collection', 'tag' => $tag]) ?>
                    <?= Html::a('&times;', ['remove-from-history', 'tag' => $tag]) ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>