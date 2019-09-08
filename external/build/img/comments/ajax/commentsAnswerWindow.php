<?
	use uti\Ckeditor\helpers\CkeditorHelper;
?>
<div class="form-group">
    <span class="moderation-alert" style="color:inherit;<?=((int)$settings->is_moderation === 1) ? '' : 'display:none;'?>"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
    <? $ckeditorconfig = array('name' => 'area[comment_'.$comment->id.'_answer_ckeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => ''); ?>
    <?=CkeditorHelper::init($ckeditorconfig) ?>
    <div class="comment-answer-error" style="color:red;"></div>
</div>
<? if((int)$settings->is_capcha === 1) : ?>
    <div class="form-group capcha-field" style="min-height: 40px; overflow: hidden; <?=((int)$settings->is_capcha === 1) ? '' : 'display:none;'?>">
        <div class="left w100-xs" style=" white-space: nowrap; ">
            <?php $this->widget('uti\cms\components\UTICaptcha', array(
                    'buttonLabel' => '<a href="#" onclick="refreshCaptcha();return false;" style="" class="refreshCaptcha refresh mt0 ml5"><i class="fa fa-refresh" aria-hidden="true"></i></a>',
                    'imageOptions' => array('class' => 'captcha-img'),
                    'captchaAction' => '/comments/captcha/get?' . rand(10000, 100000),
                ))?>
            <!--span class="col-sm-6 col-md-6" style="padding: 0">
                <?=Yii::t('app','Пожалуйста, введите код с картинки.');?>
            </span-->
        </div>
        <div class="col-md-6 col-sm-6 col-xs-12 w100-xs" style="margin: 9px 0 0 0px;">
            <?php echo CHtml::textField('verifyCode', '', array('class' => 'form-control comment_answer_captcha')) ?>
        </div>

        <div class="col-md-3 col-lg-4 captcha-error" style="color:red; margin: 12px 0 0 0px;"></div>
    </div>
<? endif; ?>
<p>
    <button class="button red submit"><?=Yii::t('app', 'Ответить')?></button>
    <button class="button default cancell"><?=Yii::t('app', 'Отмена')?></button>
</p>
