<?php
namespace frontend\modules\user\models;

use cheatsheet\Time;
use common\commands\SendEmailCommand;
use common\models\User;
use common\models\UserToken;
use frontend\modules\user\Module;
use yii\base\Exception;
use yii\base\Model;
use Yii;
use yii\helpers\Url;

/**
 * Signup form
 */
class SignupForm extends Model
{

    public $username;

    public $email;

    public $password;
    
    public $password_repeat;

    public $type;

    public $legal;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique',
                'targetClass'=>'\common\models\User',
                'message' => Yii::t('frontend', 'This username has already been taken.')
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['type', 'integer'],
            ['type', 'required'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique',
                'targetClass'=> '\common\models\User',
                'message' => Yii::t('frontend', 'This email address has already been taken.')
            ],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'required'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'message'=>"Passwords don't match" ],
            ['legal','required'],
            ['legal', 'integer'],
            ['legal', 'compare', 'compareValue' => 1, 'operator' => '==', 'message' => 'Please agree to the Terms and Conditions and Privacy Policy.']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username'=>Yii::t('frontend', 'Username'),
            'email'=>Yii::t('frontend', 'Email'),
            'password'=>Yii::t('frontend', 'Password'),
            'legal' => 'I agree to NannyCare.com\'s Terms and Conditions and Privacy Policy.',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $shouldBeActivated = $this->shouldBeActivated();
            $user = new User();
            $user->type=$this->type;
            $user->type==2 ? $user->step=7 : '';  // step db default 1
            $user->username = $this->username;
            $user->email = $this->email;
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->setPassword($this->password);
            if(!$user->save()) {
                throw new Exception("User couldn't be  saved");
            };
            $user->afterSignup();
            if ($shouldBeActivated) {
                $token = UserToken::create(
                    $user->id,
                    UserToken::TYPE_ACTIVATION,
                    Time::SECONDS_IN_A_DAY
                );
                (new \common\lib\SendEmail([
                    'subject' => Yii::t('frontend', 'Activation email'),
                    'to' => $this->email,
                    'body' => Yii::t('frontend', 'Hi there, <br>
                    Thank you for registering with NannyCare.com! Please click on the link below to activate your account. We look forward to helping you and genuinely appreciate your business! <br>
                    <br>
                    Sincerely, <br>
                    Team NannyCare.com<br><br>
                    {url}', ['url' => Yii::$app->formatter->asUrl(Url::to(['/user/sign-in/activation', 'token' => $token->token], true))])
                ]))->handle();
            }
            return $user;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function shouldBeActivated()
    {
        return true;
        /** @var Module $userModule 
        $userModule = Yii::$app->getModule('user');
        if (!$userModule) {
            return false;
        } elseif ($userModule->shouldBeActivated) {
            return true;
        } else {
            return false;
        }*/
    }
}
