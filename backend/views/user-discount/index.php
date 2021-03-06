<?php

use common\Models\User;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $offForAllNannies */
/* @var $searchModel backend\models\search\UserDiscountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('backend', 'Discounts For Nannies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-discount-index">
    <div class="callout callout-<?= $offForAllNannies !== null ? 'success' : 'warning' ?>">
        <h4>
            <?php
            if ($offForAllNannies !== null) {
                echo 'Current Discount ( For All Nannies ): '.$offForAllNannies.'% off &nbsp;&nbsp;&nbsp;'.Html::a('Update Discount For All Nannies ', ['create'], ['style' => 'font-size: 16px']);
            } else {
                echo 'You have not set(or already expired) the Discount For All Nannies yet! '.Html::a('Set the Discount For All Nannies now', ['create'], ['style' => 'font-size: 16px']);
            }
            ?>
        </h4>
    </div>
    <hr>
    <div class="callout callout-info">
        <h3>List of Discounts For Each Nanny <?= Html::a('Set A Discount For A Nanny', ['/nannies/index?NannySearch%5Bid%5D=&NannySearch%5Bname%5D=&NannySearch%5Bemail%5D=&NannySearch%5Baddress%5D=&NannySearch%5Bstatus%5D=&sort=-id'], ['style' => 'font-size: 16px']); ?></h3>
    </div>
    <br>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'user_id',
                'label'     => 'User Info',
                'format'    => 'html',
                'value'     => function ($model) {
                    if ($model->user_id == 0) {
                        return Html::tag('span', 'All Nannies', ['style' => 'color: red']);
                    } else {
                        return Html::a(User::findById($model->user_id)->username, Yii::$app->urlManagerFrontend->createAbsoluteUrl('/nannies/view?id='.$model->user_id), ['target' => '_blank']);
                    }
                },
            ],

            'discount',

            [
                'attribute' => 'created_at',
                'format'    => 'date',
                'label'     => 'Created At',
            ],
            [
                'attribute' => 'expired_at',
                'format'    => 'html',
                'label'     => 'Expired At ( Status )',
                'value'     => function ($model) {
                    if ($model->expired_at && $model->expired_at >= time()) {
                        return date('F j, Y', $model->expired_at);
                    } elseif ($model->expired_at && $model->expired_at < time()) {
                        return Html::tag('span', 'Expired', ['style' => 'color: red']);
                    } else {
                        return Html::tag('span', 'Not set and thus discount invalid', ['style' => 'color: red']);
                    }
                },
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'buttons'  => [
                    'update' => function ($url, $model, $key) {
                        if ($model->user_id == 0) {
                            $url = 'create';
                        }

                        return Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-pencil']), $url, ['title' => Yii::t('yii', 'Update'), 'aria-label' => Yii::t('yii', 'Update'), 'data-pjax' => '0']);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
