<?php
/**
 * @var \yii\web\View $this
 * @var \yii\base\Model $model
 * @var \yii\widgets\ActiveForm $form
 * @var string $keyAttribute
 * @var string $valueAttribute
 * @var string $activeAttribute
 */
$id = uniqid('params-');
$fieldOptions = [
    'options' => ['class' => 'form-group form-group-sm'],
];
$i = 1;
?>
<div id="<?= $id ?>" class="params-list">
    <table class="table">
        <tbody>
            <?php foreach (array_keys($model->$keyAttribute) as $i): ?>
            <tr data-index="<?= $i ?>">
                <td class="column-check">
                    <?= $form->field($model, $activeAttribute . "[$i]")->checkbox(['tabindex' => -1], false) ?>
                </td>
                <td class="column-key">
                    <?= $form->field($model, $keyAttribute . "[$i]", $fieldOptions)->textInput([
                        'placeholder' => $model->getAttributeLabel($keyAttribute),
                    ]) ?>
                </td>
                <td class="column-value">
                    <?= $form->field($model, $valueAttribute . "[$i]", $fieldOptions)->textInput([
                        'placeholder' => $model->getAttributeLabel($valueAttribute),
                    ]) ?>
                </td>
                <td class="column-actions">
                    <button type="button" class="close" tabindex="-1">
                        <span>&times;</span>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
// Удаление и добавление строк параметров
$this->registerJs(<<<'JS'

$('.params-list')
    .on('focus', 'tr:last input', function() {
        var curRow = $(this).parents('tr').first();
        var i = parseInt(curRow.data('index'));
        var newRow = curRow.clone();
        newRow.attr('data-index', i + 1);
        newRow.find('input').each(function(){
            $(this).attr('name', $(this).attr('name').replace(
                '[' + i + ']',
                '[' + (i + 1) + ']'
            ));
        });
        newRow.insertAfter(curRow);
    })
    .on('click', 'button.close', function() {
        $(this).parents('tr').remove();
    });

JS
);
$this->registerCss(<<<'CSS'

.params-list {
    margin: -8px;
}
.params-list .form-group {
    margin-bottom: 0;
}
.params-list .form-group .help-block {
    margin: 0;
}
.params-list td {
    border-top: none !important;
}
.params-list td.column-check,
.params-list td.column-actions {
    width: 30px;
    vertical-align: middle !important;
}
.params-list td.column-key {
    width: 30%;
}
.params-list tr:last-child button.close {
    display: none;
}

CSS
);