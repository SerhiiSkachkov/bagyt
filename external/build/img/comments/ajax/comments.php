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
    //use uti\AdminUser\models\ProfileStatuses;
    $publicAssetUrl = Yii::app()->assetManager->getPublishedUrl(Yii::app()->theme->basePath . '/public');
?>

<!-- <h2><?=Yii::t('app','Комментарии')?></h2> -->
<div class="comments-filter none">
    <div class="row list-view-sorting clearfix">
        <div class="pull-right margin-bottom-10">
            <label class="control-label"><?=Yii::t('app','Сортировать')?>:</label>
            <select id="commentSortField" class="form-control input-sm">
                <option value="0" <?= ($backorder == 0) ? 'selected="selected"' : '' ?>><?=Yii::t('app','Старые')?></option>
                <option value="1" <?= ($backorder == 1) ? 'selected="selected"' : '' ?>><?=Yii::t('app','Новые')?></option>
            </select>
        </div>
    </div>
</div>
<div class="comments">
    <?php $multiplier = 20 ?>
    <?php $max = 200 ?>
    <?php foreach($listComments as $comment) : ?>

        <?php if(((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE) || CommentsSettings::isModerator() || ((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER && $comment->countAnswers() > 0)) : ?>
        <?$settings = CommentsSettings::getSettingsByAlias($comment->alias->object_alias);?>
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
                    <img src="<?=$publicAssetUrl?>/store/img/default-no-image.png" alt="" class="media-object">
              <? endif; ?>
            </span>
            <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><a name="<?=$comment->id?>"></a>
            <div class="media-body">
              <p class="meta" style="margin-bottom:10px;">
                <strong>
					<span class="comment-author <?=$comment->isAuthorAdmin() ? 'comment-author-admin' : '';?>"><span></span><span><?=$comment->getUserName();?></span></span>
                </strong> - <span class="comment-date"><?=$created_date->format('H:i')?>, <?=$created_date->format('d.m.Y') ?></span>
              </p>
                <p>
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
                <div class="comments_comment-text mt10 mb10">
                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                        <?= $comment->comment_text ?>
                    <? else : ?>
                        <?= CHtml::encode($comment->comment_text); ?>
                    <? endif; ?>
                </div>
                <? if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                <? endif; ?>
                <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                    <div class="comment-edit-wrapper-ckeditor" style="display:none;"></div>
                <? endif; ?>
            <?php endif; ?>

            <br>
        </div>
        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
            <div class="answer-block-ckeditor" style="display: none;"></div>
        <? endif; ?>
        <!-- body -->

    </div>
    <?endif?>
    <!-- comment media -->

<?php endforeach; ?>
</div>
