<?php

use trntv\filekit\widget\Upload;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\grid\GridView;
use common\models\UserOrder;
/* @var $this yii\web\View */
/* @var $model \frontend\modules\user\models\AccountForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $dataProvider1 \yii\data\ActiveDataProvider */
$this->registerJs(
    '
    $(document).ready(function () {
        $("html, body").animate({scrollTop: $(".slide").height()+$(".navbar").height()},"slow");
        console.log($("slide").height());
    
        $("#reset-btn").click(function(){
            $(this).addClass("hidden");
            $("#reset-pw").removeClass("hidden");
        })
    
    });
    ',
    View::POS_READY,
    'my-button-handler'
);
$this->title = Yii::t('frontend', 'Nanny Settings')
?>

<div class="user-profile-form">
    <?php $form = ActiveForm::begin(); ?>
    <br>
    <div class="row">
        <div class="col-md-6">   
        <h2 style="background-color: #e39b79;">My NannyCare Account</h2>
    <!--<h2><?php //echo Yii::t('frontend', 'Account Settings') ?></h2>-->
        <?= $form->field($model, 'username')->textInput(['readOnly' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['readOnly' => true]) ?>
        <div class="hidden" id="reset-pw">
            <?= $form->field($model, 'password')->passwordInput() ?>
        
            <?= $form->field($model, 'password_confirm')->passwordInput() ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('frontend', 'Update'), ['class' => 'nav-btn']) ?>
            </div>
        </div>
                        
        <div class="form-group">
            <?= Html::Button(Yii::t('frontend', 'Reset Password'), ['class' => 'nav-btn', 'id' => 'reset-btn']) ?>
        </div>
        
        <div class="form-group">
                <a href="/user/default/get-credits"><span class="nav-btn btn-sticking-out">Get Membership</span></a>
        </div>
        <div class="form-group" id="listing-fee-expired">
                <a class="<?= UserOrder::NannyListingFeeStatus(Yii::$app->user->id) ? "hidden" : "btn nav-btn btn-sticking-out"  ?>">Your monthly listing fee is expired.</a>
                <a class="<?= UserOrder::NannyListingFeeStatus(Yii::$app->user->id) ? "btn nav-btn" : "hidden"  ?>"  style="background-color: #e39b79;border-color:#e39b79">Your monthly listing Fee will be expired at: <span><?=  date('Y-m-d', UserOrder::NannyListingFeeStatus(Yii::$app->user->id));  ?></span></a>
            </div>
            <div class="form-group">
                <?= Html::a(Html::tag('span', 'Search Jobs', ['class' => 'nav-btn']), ['/find-a-job/list']) ?>
            </div>
    </div>
        <div class="col-md-6">
            <h3 style="color: #414141;">
            <a class="btn" style="background-color: #e39b79;border-color:#e39b79;color:white">Step 1</a>
                Profile Details (click to create, view or edit)
            </h3>
            <ul class="process-label">
                <a href="main"><li class="process-label2 active" id="label-1">Main <span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="questions-n-schedule"><li class="process-label2 active" id="label-2">Questions & Schedule<span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="education-n-driving"><li class="process-label2 active" id="label-3">Education & Driving <span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="housekeeping"><li class="process-label2 active" id="label-4">Housekeeping<span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="about-you"><li class="process-label2 active" id="label-5">About you<span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="upload-files"><li class="process-label2 active" id="label-6">Upload Files<span><i class="fa fa-long-arrow-right"></i></span></li></a>
                <a href="upload-files-list"><li class="process-label2 active" id="label-7">Files List<span><i class="fa fa-long-arrow-right"></i></span></li></a>
            </ul>
            
            <div class="form-group">
            <a class="btn" style="background-color: #e39b79;border-color:#e39b79;color:white">Step 2</a>
                <a href="create-reference"> <span class="btn nav-btn">Add References</span></a>
            </div>
            <div class="form-group">
                <a class="btn" style="background-color: #e39b79;border-color:#e39b79;color:white">Step 3</a>
                <a href="https://nannycare.quickapp.pro/apply/applicant/new/10085" target=_blank><span class="btn nav-btn">Background Check</span></a>
            </div>
            <div class="form-group">
                <a class="btn" style="background-color: #e39b79;border-color:#e39b79;color:white">Step 4</a>
                <a href="https://www.protrainings.com/signup/nannycare"><span class="btn nav-btn">Get/Renew CPR</span></a>
            </div>
            
        </div>
    </div>

    <?php
    $h = <<<HTML
<h2 style="background-color: #e39b79;border-color:#e39b79">My References</h2>
HTML;

    echo $dataProvider->count == 0 ? '' : $h . GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'email:ntext',
            'reference_name:ntext',
            'reference_address:ntext',
            'contact_number:ntext',
            // 'ref_contact_email:ntext',
            // 'how_do_you_know:ntext',
            // 'years_known:ntext',

            ['class' => 'yii\grid\ActionColumn',
             
                'template' => '{view_ref} {update_ref}',
                'buttons' => [
                'view_ref' => function ($url, $model) {
                    return Html::a('<span class="fa fa-eye"></span> View', $url, [
                                'title' => Yii::t('app', 'View'),
                                'class'=>'btn btn-primary btn-xs',                                  
                    ]);
                },
                'update_ref' => function ($url, $model) {
                    return Html::a('<span class="fa fa-edit"></span> Edit', $url, [
                                'title' => Yii::t('app', 'Edit'),
                                'class'=>'btn btn-primary btn-xs',                                  
                    ]);
                },
            ],
            ],
        ],
    ]); ?>
<!--
    <?php
    $h = <<<HTML
<h2 style="color: #000;">Prevous Employments</h2>
HTML;

    echo $dataProvider1->count == 0 ? '' : $h . GridView::widget([
        'dataProvider' => $dataProvider1,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'email:ntext',
            'employer_name:ntext',
            'employer_address:ntext',
            // 'to_date:ntext',
            'position_type:ntext',
            // 'number_of_children',
            // 'ages_of_children_started:ntext',
            // 'ages_of_children_left:ntext',
            // 'responsibilities:ntext',
            // 'salary_starting:ntext',
            // 'salary_ending:ntext',
            // 'may_we_contact:ntext',
            // 'contact_phone:ntext',
            // 'contact_email:ntext',
            // 'reason_for_leaving:ntext',
            // 'hours_worked:ntext',
            // 'was_this_a_live_in_position:ntext',
            // 'emloyer_comment:ntext',

            ['class' => 'yii\grid\ActionColumn',
             
              'template' => '{view_emp} {update_emp}',
                'buttons' => [
                'view_emp' => function ($url, $model) {
                    return Html::a('<span class="fa fa-eye"></span> View', $url, [
                                'title' => Yii::t('app', 'View'),
                                'class'=>'btn btn-primary btn-xs',                                  
                    ]);
                },
                'update_emp' => function ($url, $model) {
                    return Html::a('<span class="fa fa-edit"></span> Edit', $url, [
                                'title' => Yii::t('app', 'Edit'),
                                'class'=>'btn btn-primary btn-xs',                                  
                    ]);
                },
            ],
            ],
        ],
    ]); ?>
    <?php ActiveForm::end(); ?>
-->

</div>
