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

<div id="comments_list-comments" class="comments-area">
    <h3 class="comments comments-title"><?=Yii::t('app','Комментарии')?></h3>
    <div class="comments-filter none" style="display: none;">
        <div class="list-view-sorting clearfix">
            <label class="control-label"><?=Yii::t('app','Сортировать')?>:</label>
            <select id="commentSortField" class="form-control input-sm">
                <option value="0" selected="selected"><?=Yii::t('app','Старые')?></option>
                <option value="1" ><?=Yii::t('app','Новые')?></option>
            </select>
        </div>
    </div>

    <ol class="commentlist">
        <?php
            $multiplier = 20;
            $max = 200;
        ?>
        <?php foreach($listComments as $comment) : ?>
            <?php if(((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE) || CommentsSettings::isModerator() || ((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER && $comment->countAnswers() > 0)) : ?>
                <?php $padding_left = (count(explode('.', $comment->upline)) - 1) * $multiplier; ?>
                <?php if ($padding_left > $max) { $padding_left = $max; } ?>
                <!-- comment -->
                <li class="comment byuser comment-author-superadmin bypostauthor even thread-even depth-1 comments-1 comments_comment-block" data-message-id="<?= $comment->id ?>" style="margin-left: <?=$padding_left?>px;">
                    <div id="comment-111" class="comment">
                        <div class="comments-photo"> 
                            <?if(!empty($comment->user->id)):?>
                                <?$profile =Profile::model()->find('user__id =:user__id', array('user__id'=>$comment->user->id));?>
                                <? if (($profile->attachment != NULL) && ($profile->attachment->secret_name != null)) : ?>
                                <?= CHtml::image(MSmarty::attachment_get_file_name($profile->attachment->secret_name, $profile->attachment->raw_name, $profile->attachment->file_ext, '_office_profile', 'office_photo'), '', array('class'=>"avatar avatar-100 photo")); ?>
                                <? else : ?>
                                    <img src="<?=$publicAssetUrl?>/store/img/default-no-image.png" alt="" class="avatar avatar-100 photo">
                                <? endif; ?>
                            <? else : ?>
                                <img src="<?=$publicAssetUrl?>/officeprofile/img/o.jpg" alt="" class="avatar avatar-100 photo">
                            <? endif; ?>
                        </div>
                        <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><a name="<?=$comment->id?>"></a>
                        <div class="comments-info">
                            <header class="comment-meta comment-author vcard clearfix">
                                <h4 class="text-blue"><span class="comment-author <?=$comment->isAuthorAdmin() ? 'comment-author-admin' : '';?>"><span></span><span><?=$comment->getUserName();?></span></span> <span class="comment-date">- <?=$created_date->format('H:i')?>, <?=$created_date->format('d.m.Y') ?></span></h4>
                                <div class="reply port-post-social pull-right"> 
                                    <?php if (Yii::app()->user->checkAccess('CommentsAnswer') && CommentsSettings::canPublishComments($comment->alias->object_alias) && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                                        <a href="javascript:void(0);" class="comment_answer-comment comment-reply-link" title="<?=Yii::t('app', 'Ответить')?>"><i class="fa fa-share" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                    <?php if((CommentsSettings::canEditComment($comment, Yii::app()->user->id, 'CommentsEdit') || CommentsSettings::isModerator()) && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                                        <a href="javascript:void(0);" class="comment_edit-comment comment-reply-link" title="<?=Yii::t('app', 'Редактировать')?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                                        <a href="javascript:void(0);" class="comment_show-comment comment-reply-link" title="<?=Yii::t('app', 'Показать')?>"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                                        <a href="javascript:void(0);" class="comment_hide-comment comment-reply-link" title="<?=Yii::t('app', 'Скрыть')?>"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                    <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
                                        <a href="javascript:void(0);" class="comment_delete-comment comment-reply-link" title="<?=Yii::t('app', 'Удалить')?>"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                    <?php endif; ?>
                                </div>
                            </header>
                            <div class="comment-content comment">
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
                                            <p><?= $comment->comment_text ?></p>
                                        <? else : ?>
                                            <p><?= CHtml::encode($comment->comment_text) ?></p>
                                        <? endif; ?>
                                    </div>
                                    <? if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                                        <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                                    <? endif; ?>
                                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                                        <div class="comment-edit-wrapper-ckeditor" style="display:none;">
                                        </div>
                                    <? endif; ?>
                                <?php endif; ?>
                            </div>

                        </div>
                        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                            <div class="answer-block-ckeditor" style="display: none;"></div>
                        <? endif; ?>
                        <!-- body -->

                    </div>
                </li>
            <?php endif; ?>
            <!-- comment media -->

        <?php endforeach; ?>
    </ol>
</div>