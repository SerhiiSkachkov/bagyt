<?
	use use uti\Ckeditor\helpers\CkeditorHelper;
?>
<div class="com-item mb20 post-comment">
<?php if (Yii::app()->user->checkAccess('CommentsAdd')) : ?>
    
    <?$settings = CommentsSettings::getSettingsByAlias($object_alias);?> 
        <? if((int)$settings->is_moderation === 1) : ?>
        <span class="comment-moderation-message" style="display:none;"><br><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
    <? endif; ?>
    
    <div class="com-photo" style="width: 96px">
                <? $profile = Profile::model()->find('user__id =:user__id', array('user__id' => Yii::app()->user->id));?> 
                <? if (($profile->attachment != NULL) && ($profile->attachment->secret_name != null)) : ?>
                <?= CHtml::image(MSmarty::attachment_get_file_name($profile->attachment->secret_name, $profile->attachment->raw_name, $profile->attachment->file_ext, '_office_profile', 'office_photo'), '', array('class'=>"mb5")); ?>
                <? else : ?>
                    <img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="mb5">
                <? endif; ?>    
            
            </div>

    <?php $form = $this->beginWidget('CActiveForm', array(
        'enableAjaxValidation' => false,
        'htmlOptions' => array(
            'class' => 'comment-add-form'
        )
        )
    )
    ?>

<div class="form-group">
                <? if((int)$settings->is_moderation === 1) : ?>
                    <span class="comment-moderation-message" style=""><br><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
                <? endif; ?>
                <?php echo CHtml::activeTextArea($modelComments, 'comment_text', array('id' => 'comments_comment-text', 'class' => 'form-control bg-white wmax', 'placeholder' => Yii::t('app', 'Введите Ваш комментарий'), 'rows' => 5)) ?>
                <div class="comment-error font-red"></div>
            </div>

<div class="row">
    <div class="col-md-8 col-sm-8">
            <? if ((int)$settings->is_capcha === 1) : ?>
            <div class="form-group capcha-field" style="min-height: 40px; overflow:hidden;<?=((int)$settings->is_capcha === 1) ? '' : 'display:none;'?>">
                <div class="left w100-xs" style=" white-space: nowrap; ">
                            <?php $this->widget('UTICaptcha', array(
                        'buttonLabel' => '<a href="#" onclick="refreshCaptcha();return false;" style="" class="refreshCaptcha refresh mt0 ml5"><i class="fa fa-refresh" aria-hidden="true"></i></a>', 
                        'imageOptions' => array('class' => 'captcha-img left'),
                                    'captchaAction' => '/comments/captcha/get?' . rand(10000, 100000),
                                ))?>
                
                    <!--span class="col-sm-6 col-md-6" style="padding: 0">
                        <?=Yii::t('app','Пожалуйста, введите код с картинки.');?>
                    </span-->
                    </div>
                <div class="col-md-6 col-sm-6 col-xs-12 w100-xs" style="margin: 9px 0 0 0px;">
                        <?php echo CHtml::activeTextField($modelComments, 'verifyCode', array('id' => 'comments_captcha','class' => 'form-control')) ?>
                    </div>
                <div class="col-md-6 col-lg-4 captcha-error" style="color:red; "></div>
                </div>
            <? endif; ?>
        </div>
    <div class="col-md-4 col-sm-4">
        <div class="text-right">
            <?php echo CHtml::submitButton(Yii::t('app', 'Отправить'), array('id' => 'comment_add-comment', 'class' => 'btn green')) ?>
        </div>
    </div>
</div>
    <?php $this->endWidget(); ?>
<?php endif; ?>
</div>