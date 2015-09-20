<?php

namespace zhuravljov\yii\rest\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class CollectionController
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class CollectionController extends Controller
{
    /**
     * @var \zhuravljov\yii\rest\Module
     */
    public $module;

    public function actionLink($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->addToCollection($tag);
            return $this->redirect(['request/create', 'tag' => $tag]);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }

    public function actionUnlink($tag)
    {
        if ($this->module->storage->exists($tag)) {
            $this->module->storage->removeFromCollection($tag);
            return $this->redirect(['request/create']);
        } else {
            throw new NotFoundHttpException('Request not found.');
        }
    }
}