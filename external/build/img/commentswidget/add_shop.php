<?
use uti\AdminComments\models\Comments;
use uti\AdminComments\models\CommentsObjectAliases;
use uti\AdminComments\models\CommentsObjects;
use uti\AdminComments\models\CommentsObjectsAccess;
use uti\AdminComments\models\CommentsSettings;
use uti\Ckeditor\helpers\CkeditorHelper;
use uti\AdminCaptcha\models\Captcha;
use uti\AdminCaptcha\models\CaptchaSettings;
?>

<div class="post-comment padding-top-40">
  <h2 class="tabcontent-title"><?= Yii::t('app','Оставить отзыв') ?></h2>
  <? if ((bool)$productsSettings->is_set_points) : ?>
    <? if ((bool)$productsSettings->is_set_points_for_guest && Yii::app()->user->isGuest) : ?>
      <? if ((Yii::app()->request->cookies['setProductRatio'] == NULL) || (!is_array(json_decode(Yii::app()->request->cookies['setProductRatio']->value))) || (!in_array($this->modelProducts->id, json_decode(Yii::app()->request->cookies['setProductRatio']->value)))) : ?>
          <label style="font-weight: 600;color:#999999">
            <?=Yii::t('app', 'Оценить')?>
          </label>
           <div class="review" style="margin-bottom:20px">
             <div class="header-review-inner" style="display: inline-block;">
               <input type="range" value="5" step="0.5" id="backing" style="display: none;">
               <div class="rateit js-rateit" id="rateit" data-rateit-backingfld="#backing" data-rateit-resetable="false"  data-rateit-ispreset="true" data-rateit-min="0" data-rateit-max="5">
               </div>
             </div>
             <div class="ratio-result" style="display: none"><?=Yii::t('app', 'Спасибо за ваше мнение!')?></div>
           </div>
      <? endif; ?>
    <? elseif(((bool)$productsSettings->is_set_points) && !Yii::app()->user->isGuest && (((bool)$userRatio && $productsSettings->is_change_points) || (bool)!$userRatio)) : ?>
        <label style="font-weight: 600;color:#999999">
          <?=Yii::t('app', 'Оценить')?>
        </label>
        <div class="review" style="margin-bottom:20px">
          <div class="header-review-inner" style="display: inline-block;">
            <input type="range" value="5" step="0.5" id="backing" style="display: none;">
            <div class="rateit  js-rateit" id="rateit" data-rateit-backingfld="#backing" data-rateit-resetable="false"  data-rateit-ispreset="true" data-rateit-min="0" data-rateit-max="5">
            </div>
          </div>
          <div class="ratio-result" style="display: none"><?=Yii::t('app', 'Спасибо за ваше мнение!')?></div>
        </div>
    <? endif; ?>
  <? endif; ?>
  <?php if (CommentsSettings::canPublishComments($object_alias)): ?>
    <?php if (Yii::app()->user->checkAccess('CommentsAdd')) : ?>

        <?$settings = CommentsSettings::getSettingsByAlias($object_alias);?>
        <span class="comment-moderation-message" style="display:none;"><br><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
        <?php $form = $this->beginWidget('CActiveForm', array(
            'enableAjaxValidation' => false,
            'htmlOptions' => array(
                'class' => 'comment-add-form'
            )
            )
        )
        ?>
        <div class="form-group">
            <!--<label><?=Yii::t('app', 'Сообщение')?></label>-->
            <span class="comment-moderation-message moderation-alert" style="<?=((int)$settings->is_moderation === 1) ? '' : 'display:none;'?>"><br><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
            <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                <? $ckeditorconfig = array('name' => 'area[textareackeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => empty($modelComments->comment_text) ? '' : $modelComments->comment_text); ?>
                <?=CkeditorHelper::init($ckeditorconfig) ?>
                <?php echo CHtml::activeTextArea($modelComments, 'comment_text', array('id' => 'comments_comment-text', 'class' => 'form-control', 'rows' => 8, 'style' => 'display: none;')) ?>
            <? else : ?>
                <?php echo CHtml::activeTextArea($modelComments, 'comment_text', array('id' => 'comments_comment-text', 'class' => 'form-control', 'rows' => 8)) ?>
            <? endif; ?>
            <div class="comment-error" style="color:red;"></div>
        </div>
            <? if((Captcha::captchaConditions('textCAPTCHA', 'comments_news', (int)TRUE) && $object_alias == 'news') || (Captcha::captchaConditions('textCAPTCHA', 'comments_product', (int)TRUE) && $object_alias == 'product') || (Captcha::captchaConditions('textCAPTCHA', 'comments_blog_posts', (int)TRUE) && $object_alias == 'blog_posts')): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group fy-field">
                            <div class=" capcha-field clearfix">
                                <div class="capcha-field__block">
                                    <?php $this->widget('uti\cms\components\UTICaptcha', array(
                                        'buttonLabel' => '<a href="#" class="btn-refresh" onclick="refreshCaptcha();return false;" style="color: #767F88;" class="refreshCaptcha"><i class="fa fa-refresh"></i></a>',
                                        'imageOptions' => array('class' => 'captcha-img'),
                                        'captchaAction' => '/comments/captcha/get?' . rand(10000, 100000),
                                    ))?>
                                    <?php echo CHtml::activeTextField($modelComments, 'verifyCode', array('id' => 'comments_captcha','class' => 'fy-input')) ?>
                                </div>
                                <p class="capcha-field__text">
                                    <?=Yii::t('app','Пожалуйста, введите код с картинки.');?>
                                </p>
                            </div>

                        </div>
                        <div class="captcha-error" style="color:red;"></div>
                    </div>
                </div>
            <? endif; ?>
        <? if((Captcha::captchaConditions('reCAPTCHA', 'comments_news', (int)TRUE) && $object_alias == 'news') || (Captcha::captchaConditions('reCAPTCHA', 'comments_product', (int)TRUE) && $object_alias == 'product') || (Captcha::captchaConditions('reCAPTCHA', 'comments_blog_posts', (int)TRUE) && $object_alias == 'blog_posts')): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group fy-field" style="">
                        <!--                                    <script src='https://www.google.com/recaptcha/api.js?hl=--><?//= Yii::app()->language; ?><!--'></script>-->
                        <div class="g-recaptcha" id="answer-all"></div>
                    </div>
                    <div class="captcha-error" style="color:red;"></div>
                </div>
            </div>

        <? endif; ?>
        <p>
            <?php echo CHtml::submitButton(Yii::t('app', 'Добавить'), array('id' => 'comment_add-comment', 'class' => 'fy-button fy-button-accent')) ?>
        </p>
        <?php $this->endWidget(); ?>
        <span class="comment-moderation-message" style="display:none;"><br><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
    <?php endif; ?>

<? if($captcha_settings && $captcha_settings->alias == 'reCAPTCHA'): ?>
    <script>
        $(document).ready(function () {
          if ($("#answer-all").length) {
            setTimeout(function () {
              grecaptcha.render( "answer-all", {
                'sitekey' : '<?= CaptchaSettings::getParams('site_key', 'reCAPTCHA')->value; ?>',
              });
            }, 1000);
          }
        })
    </script>
<? endif; ?>
  <?php endif; ?>
</div>
