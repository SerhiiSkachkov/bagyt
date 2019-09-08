<?
	use uti\Ckeditor\helpers\CkeditorHelper;
    use uti\cms\helpers\MSmarty;
?>
<?Yii::import('uti.AdminUser.models.Profile');?>
<?Yii::import('uti.attachment.models.Attachments');?>
<h2><?=Yii::t('app','Комментарии')?></h2>
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
            <span class="pull-left">

			<?if(!empty($comment->user->id)):?>
                <?$profile =Profile::model()->find('user__id =:user__id', array('user__id'=>$comment->user->id));?>
                <? if (($profile->attachment != NULL) && ($profile->attachment->secret_name != null)) : ?>
				<?= CHtml::image(MSmarty::attachment_get_file_name($profile->attachment->secret_name, $profile->attachment->raw_name, $profile->attachment->file_ext, '_office_profile', 'office_photo'), '', array('class'=>"media-object")); ?>
				<? else : ?>
					<img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="media-object">
				<? endif; ?>

            <? else : ?>
                    <img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="media-object">
              <? endif; ?>
            </span>
            <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><a name="<?=$comment->id?>"></a>
            <div class="media-body">
                <h4 class="media-heading">
                <span class="comment-author <?=$comment->isUserAdmin() ? 'comment-author-admin' : '';?>"><span></span><span><?=$comment->getUserName();?></span></span>
                <span>
                    <?=$created_date->format('H:i')?>, <?=$created_date->format('d.m.Y') ?>
                    <?php if (Yii::app()->user->checkAccess('CommentsAnswer') && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                        / <a href="javascript:void(0);" class="comment_answer-comment"><?=Yii::t('app', 'ответить')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::canEditComment($comment, Yii::app()->user->id, 'CommentsEdit') || CommentsSettings::isModerator()) : ?>
                        / <a href="javascript:void(0);" class="comment_edit-comment"><?=Yii::t('app', 'редактировать')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                        / <a href="javascript:void(0);" class="comment_show-comment"><?=Yii::t('app', 'показать')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                        / <a href="javascript:void(0);" class="comment_hide-comment"><?=Yii::t('app', 'скрыть')?></a>
                    <?php endif; ?>
                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                        / <a href="javascript:void(0);" class="comment_delete-comment"><?=Yii::t('app', 'удалить')?></a>
                    <?php endif; ?>
                </span>
                </h4>

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
                <div class="comments_comment-text mt10 mb10"><?= CHtml::encode($comment->comment_text) ?></div>
                <? if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                    <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                <? endif; ?>
            <?php endif; ?>

            <br>
        </div>
        <!-- body -->

    </div>
    <?endif?>
    <!-- comment media -->

<?php endforeach; ?>
</div>
