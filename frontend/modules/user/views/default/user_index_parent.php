<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\modules\file\models\UserFile;

/* @var $this yii\web\View */
/* @var $model \frontend\modules\user\models\AccountForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $record common\models\ParentNanny */
/* @var $file_model UserFile */

$this->registerJs(
    '
    $(document).ready(function () {
        $("html, body").animate({scrollTop: $(".slide").height()+$(".navbar").height()},"slow");
        console.log($("slide").height());
        $("#reset_button").click(function(){
            $(this).addClass("hidden");
            $("#reset_pwd").removeClass("hidden");
        })
     });
    ',
    View::POS_READY,
    'my-button-handler'
);

$this->registerJs('
    $(".upload-btn").click(function(){
        $("#FilesList").modal("hide");
        $("#UploadFile").modal("show");
    })
', View::POS_END);
$this->title = Yii::t('frontend', 'Parent Account Page')
?>

<div class="user-profile-form">

    <?php $form = ActiveForm::begin(); ?>
    <br>
    <div class="col-md-6">   
        <h2 style="color: #414141;">My NannyCare Account</h2>

        <?= $form->field($model, 'username')->textInput(['readOnly' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['readOnly' => true]) ?>

        <div class="hidden" id="reset_pwd">
            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'password_confirm')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('frontend', 'Confirm Reset Password'), ['class' => 'nav-btn']) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::Button(Yii::t('frontend', 'Reset Password'), ['class' => 'nav-btn', 'id' => 'reset_button']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="col-md-6 parent-profile">
        <h2 style="color: #414141;">My Profile</h2>
        <h3><b>Personal data:</b><span style="float: right;"><a href="/user/sign-in/continue-family" class="btn btn-inverse">Edit Profile</a></span></h3>
        <h3><b>Credits:</b> <?= $model->credits; ?><span style="float: right;"><a href="get-credits" class="btn btn-inverse">Get Credits</a></span></h3>
        <h3><b>Upload files:</b><span style="float: right;"><a  data-toggle="modal" data-target="#FilesList" class="btn btn-inverse">Files list</a></span></h3>
        <h3><b>Nannies Selected:</b><span style="float: right;"><a href="/nannies/index" class="btn btn-inverse">Find A Nanny</a></span></h3>
        <div class="nannies-selected-table">
            <?php
            $id = Yii::$app->user->id;
            $parentnannyrecords = \common\models\ParentNanny::find()->where(['parentid'=>$id])->all();
            if (count($parentnannyrecords)) :
            ?>
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Email</th><th>Profile Link</th></tr></thead>
                <tbody>
                <?php
                    foreach ($parentnannyrecords as $record) {
                        echo '<tr><td>'. preg_split('/\s+/', $record->nanny->name)[0] .'</td><td>'. $record->nanny->email .'</td><td>'. Html::a('Click Here', '/nannies/view?id=' . $record->nannyid) .'</td></tr>';
                    }
                ?>
                </tbody>
            </table>
            <?php endif; ?>

        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="FilesListModalLabel" id="FilesList">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="FilesListModalLabel">Files List</h4>
            </div>
            <div class="modal-body">
                <?php
                $file_models = UserFile::find()->where(['user_id' => Yii::$app->user->id, 'status' => UserFile::STATUS_ACTIVE])->all();
                if (count($file_models) == 0) {
                    echo Yii::t('frontend', 'No file found');
                } else {
                    $html = '<table class="table table-hover"><thead><tr><th>#</th><th>File Name</th><th>Download Link</th></tr></thead><tbody>';
                    foreach ($file_models as $idx => $file_model) {
                        $html .= '<tr><td>'.($idx + 1).'</td><td>'. $file_model->title .'</td><td>'. Html::a('download', \yii\helpers\Url::to(['/file/user/download', 'user_id' => $file_model->user_id, 'file_uuid' => $file_model->file_uuid])) .'</td></tr>';
                    }
                    $html .= '</tbody></table>';
                    echo $html;
                }
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success upload-btn">Upload File</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="UploadFileModalLabel" id="UploadFile">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="UploadFileModalLabel">Upload File</h4>
            </div>
            <?php $upload_form = ActiveForm::begin([
                'options' => [
                    'enctype' => 'multipart/form-data',
                ],
                'action' => '/file/user/upload'
            ]) ?>
            <div class="modal-body">
                <?php
                $upload_model = new UserFile();
                echo $upload_form->field($upload_model, 'title')->textInput(['placeholder' => 'description file']);
                echo $upload_form->field($upload_model, 'file')->fileInput();
                ?>
                <p class="text-muted well well-sm no-shadow">
                    Attention: <br />
                    You can upload only 1 file at once and the total size should be no more than 10M. <br>
                    Supported formats are tif, png, jpg, doc, docx, xls, xlsx, ppt, pptx, pdf, zip, rar, 7z, txt.
                </p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Upload</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancle</button>
            </div>
            <?php ActiveForm::end() ?>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->