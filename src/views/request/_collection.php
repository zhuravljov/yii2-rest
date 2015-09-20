<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var string $activeTag
 * @var array $items
 */
?>
<div class="rest-request-collection">
    <ul id="collection-list" class="request-list">
        <?php foreach ($items as $group => $rows): ?>
            <li>
                <div class="request-list-group">
                    <?= Html::encode($group) ?>
                    <?= Html::tag('span', count($rows), ['class' => 'counter']) ?>
                </div>
                <ul>
                    <?php foreach ($rows as $tag => $row): ?>
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
                            <a href="<?= Url::to(['request/create', 'tag' => $tag]) ?>">
                                <span class="request-name">
                                    <span class="request-method">
                                        <?= Html::encode($row['method']) ?>
                                    </span>
                                    <span class="request-endpoint">
                                        <?= Html::encode($row['endpoint']) ?>
                                    </span>
                                </span>
                                <?php if (!empty($row['description'])): ?>
                                    <span class="request-description">
                                        <?= Html::encode($row['description']) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <div class="actions">
                                <?= Html::a('&times;', ['collection/unlink', 'tag' => $tag], ['data-method' => 'post']) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</div>