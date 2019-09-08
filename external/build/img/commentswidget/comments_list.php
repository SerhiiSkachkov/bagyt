<?
use uti\AdminComments\models\Comments;
use uti\AdminComments\models\CommentsObjectAliases;
use uti\AdminComments\models\CommentsObjects;
use uti\AdminComments\models\CommentsObjectsAccess;
use uti\AdminComments\models\CommentsSettings;
use uti\AdminUser\models\Profile;
use uti\attachment\models\Attachments;
use uti\Ckeditor\helpers\CkeditorHelper;
use uti\cms\helpers\MSmarty;

$publicAssetUrl = Yii::app()->assetManager->getPublishedUrl(Yii::app()->theme->basePath . '/public');
?>
<style media="screen">
  .button.default {
    background: #323232;
  }
  .button.default:before {
    background: #db2d2e;
  }
</style>
<div class="comments-filter none" style="display: none;">
    <div class="row list-view-sorting clearfix">
        <div class="pull-right margin-bottom-10">
            <label class="control-label"><?=Yii::t('app','Сортировать')?>:</label>
            <select id="commentSortField" class="form-control input-sm">
                <option value="0" selected="selected"><?=Yii::t('app','Старые')?></option>
                <option value="1" ><?=Yii::t('app','Новые')?></option>
            </select>
        </div>
    </div>
</div>
<div class="comments">
    <?php $multiplier = 20 ?>
    <?php $max = 200 ?>
    <?//vg($listComments);?>
    <?php foreach($listComments as $comment) : ?>
        <?php if(((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE) || CommentsSettings::isModerator() || ((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER && $comment->countAnswers() > 0)) : ?>
        <?php $padding_left = (count(explode('.', $comment->upline)) - 1) * $multiplier; ?>
        <?php if ($padding_left > $max) { $padding_left = $max; } ?>

        <!-- comment -->
        <div class="media comments_comment-block" data-message-id="<?= $comment->id ?>" style="margin: 5px; margin-bottom: 20px; clear: both; margin-left: <?=$padding_left?>px;">
            <span class="pull-left" style="padding-right:20px">

			<?if(!empty($comment->user->id)):?>
                <?$profile =Profile::model()->find('user__id =:user__id', array('user__id'=>$comment->user->id));?>
                <? if (($profile->attachment != NULL) && ($profile->attachment->secret_name != null)) : ?>
				<?= CHtml::image(MSmarty::attachment_get_file_name($profile->attachment->secret_name, $profile->attachment->raw_name, $profile->attachment->file_ext, '_office_profile', 'office_photo'), '', array('class'=>"media-object")); ?>
				<? else : ?>
					<img src="<?=$publicAssetUrl?>/store/img/default-no-image.png" alt="" class="media-object">
				<? endif; ?>

            <? else : ?>
                    <img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="media-object">
              <? endif; ?>
            </span>
            <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><a name="<?=$comment->id?>"></a>
            <div class="media-body">
              <p class="meta" style="margin-bottom:10px;">
                <strong>
					<span class="comment-author <?=$comment->isAuthorAdmin() ? 'comment-author-admin' : '';?>"><span></span><span><?=$comment->getUserName();?></span></span>
                </strong> - <span class="comment-date"><?=$created_date->format('H:i')?>, <?=$created_date->format('d.m.Y') ?></span>
              </p>
                <p class="">
                <span>

                    <?php if (Yii::app()->user->checkAccess('CommentsAnswer') && CommentsSettings::canPublishComments($comment->alias->object_alias) && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                        / <a style="" href="#" class="comment_answer-comment"><?=Yii::t('app', 'ответить')?></a>
                    <?php endif; ?>
                    <?php if((CommentsSettings::canEditComment($comment, Yii::app()->user->id, 'CommentsEdit') || CommentsSettings::isModerator()) && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                        / <a style="" href="#" class="comment_edit-comment"><?=Yii::t('app', 'редактировать')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                        / <a style="" href="#" class="comment_show-comment"><?=Yii::t('app', 'показать')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                        / <a style="" href="#" class="comment_hide-comment"><?=Yii::t('app', 'скрыть')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                        / <a style="" href="#" class="comment_delete-comment"><?=Yii::t('app', 'удалить')?></a>
                    <?php endif; ?>
                </span>
              </p>
            <?php if((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED || (int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER) : ?>
                <div class="note note-info">
                    <p><?=Yii::t('app', 'Комментарий удален')?></p>
                </div>
            <?php else :?>
                <?if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE)) :?>
                    <span><?=Yii::t('app','Комментарий не активен');?></span>
                <? elseif(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                    <span><?=Yii::t('app','Комментарий ожидает модерации');?></span>
                <? endif; ?>
                <div class="comments_comment-text">
                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                        <?= $comment->comment_text ?>
                    <? else : ?>
                        <?= CHtml::encode($comment->comment_text) ?>
                    <? endif; ?>
                </div>
                <? if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                    <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                <? endif; ?>
                <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                    <div class="comment-edit-wrapper-ckeditor" style="display:none;">
                        <? /*
                        <div class="form-group">
                            <span class="moderation-alert" style="color:inherit;<?=((int)$settings->is_moderation === 1) ? '' : 'display:none;'?>"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
                            <? $ckeditorconfig = array('name' => 'area[comment_'.$comment->id.'_ckeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => empty($comment->comment_text) ? '' : $comment->comment_text); ?>
                            <?=CkeditorHelper::init($ckeditorconfig) ?>
                            <div class="comment-error" style="color:red;"></div>
                        </div>
                        <p>
                            <button class="btn btn-primary submit mr5"><?=Yii::t('app', 'Сохранить')?></button>
                            <button class="btn cancell cancell"><?=Yii::t('app', 'Отмена')?></button>
                        </p>
                        */ ?>
                    </div>
                <? endif; ?>
            <?php endif; ?>
            <br>
        </div>
        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
            <div class="answer-block-ckeditor" style="display: none;">
                <? /*
                <div class="form-group">
                    <span class="moderation-alert" style="color:inherit;<?=((int)$settings->is_moderation === 1) ? '' : 'display:none;'?>"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
                    <? $ckeditorconfig = array('name' => 'area[comment_'.$comment->id.'_answer_ckeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => ''); ?>
                    <?=CkeditorHelper::init($ckeditorconfig) ?>
                    <div class="comment-answer-error" style="color:red;"></div>
                </div>
                <? if((int)$settings->is_capcha === 1) : ?>
                    <div class="form-group capcha-field" style="min-height: 40px; overflow: hidden; <?=((int)$settings->is_capcha === 1) ? '' : 'display:none;'?>">
                        <div class="left w100-xs" style=" white-space: nowrap; ">
                            <?php $this->widget('UTICaptcha', array(
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
                    <button class="btn btn-primary submit mr5"><?=Yii::t('app', 'Ответить')?></button>
                    <button class="btn cancell cancell"><?=Yii::t('app', 'Отмена')?></button>
                </p>
                */ ?>
            </div>
        <? endif; ?>
        <!-- body -->

    </div>
    <?endif?>
    <!-- comment media -->

<?php endforeach; ?>
</div>
