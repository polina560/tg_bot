<?php

namespace frontend\controllers;

use common\components\exceptions\ModelSaveException;
use common\modules\user\{models\SignupForm, models\User, Module};
use common\modules\user\models\{LoginForm};
use frontend\models\{ContactForm, PasswordResetRequestForm, ResendVerificationEmailForm, ResetPasswordForm};
use RequirementChecker;
use Throwable;
use Yii;
use yii\base\{Exception, InvalidArgumentException, InvalidConfigException};
use yii\captcha\CaptchaAction;
use yii\db\StaleObjectException;
use yii\filters\{AccessControl, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\web\{BadRequestHttpException, Controller, ErrorAction, HttpException, Response};

/**
 * Site Controller
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?']
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['POST']]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => ErrorAction::class,
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ]
        ];
    }

    /**
     * Displays homepage.
     */
    public function actionIndex(): string
    {
        $confirmStatus = Yii::$app->request->get('confirm_status');
        switch ($confirmStatus) {
            case 'success':
                Yii::$app->session->addFlash('success', 'Адрес электронной почты успешно подтвержден');
                break;
            case 'email_is_confirmed':
                Yii::$app->session->addFlash('warning', 'Этот адрес электронной почты уже подтвержден');
                break;
            case 'token_is_not_valid':
                Yii::$app->session->addFlash('error', 'Неверный токен подтверждения, возможно ссылка устарела');
                break;
            default:
                break;
        }
        return $this->render('index');
    }

    public function actionHealth(): string
    {
        require_once dirname(__DIR__, 2) . '/requirements/RequirementChecker.php';
        $requirementsChecker = new RequirementChecker();
        $requirementsChecker->checkYii();
        if (!empty($requirementsChecker->result['summary']['errors'])) {
            Yii::$app->response->statusCode = 500;
            return print_r($requirementsChecker->result['summary']['errors'], true);
        }
        return 'OK';
    }

    /**
     * Logs in a user.
     *
     * @throws ModelSaveException
     * @throws Exception
     * @throws HttpException
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('index');
        }
        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logs out the current user.
     *
     * @throws Throwable
     * @throws ModelSaveException
     * @throws StaleObjectException
     */
    public function actionLogout(): Response
    {
        User::logout();
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @throws InvalidConfigException
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Thank you for contacting us. We will respond to you as soon as possible.')
                );
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'There was an error sending your message.'));
            }

            return $this->refresh();
        }
        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @throws Exception
     */
    public function actionSignup(): Response|string
    {
        Module::initI18N();
        if (!Yii::$app->params['signup']['enabled_clients']['email-password']) {
            Yii::$app->session->setFlash('error', Yii::t(Module::MODULE_ERROR_MESSAGES, 'Registration disabled'));
            return $this->goHome();
        }
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash(
                'success',
                Yii::t('app', 'Thank you for registration. Please check your inbox for verification email.')
            );
            return $this->goHome();
        }
        return $this->render('signup', ['model' => $model]);
    }

    /**
     * Requests password reset.
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionRequestPasswordReset(): Response|string
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Check your email for further instructions.'));

                return $this->goHome();
            }
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app', 'Sorry, we are unable to reset password for the provided email address.')
            );
        }

        return $this->render('requestPasswordResetToken', ['model' => $model]);
    }

    /**
     * Resets password.
     *
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws HttpException
     */
    public function actionResetPassword(string $token): Response|string
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'New password saved.'));

            return $this->redirect('login');
        }

        return $this->render('resetPassword', ['model' => $model]);
    }

    /**
     * Resend verification email
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionResendVerificationEmail(): Response|string
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Check your email for further instructions.'));
                return $this->goHome();
            }
            Yii::$app->session->setFlash(
                'error',
                Yii::t('app', 'Sorry, we are unable to resend verification email for the provided email address.')
            );
        }

        return $this->render('resendVerificationEmail', ['model' => $model]);
    }
}
