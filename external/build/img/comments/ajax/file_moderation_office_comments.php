<?
use uti\Ckeditor\helpers\CkeditorHelper;
?>
<?Yii::import('uti.attachment.models.Attachments');?>
<?
Yii::import('uti.office.modules.profile.models.*');
Yii::import('uti.adminregister.models.*');
?>
<p class="semibold fs15 uppercase mb15 bbottom pb5">
    <?=Yii::t('app','Комментарии модератора')?>
</p>
<div class="comments">
    <?php $multiplier = 20 ?>
    <?php $max = 200 ?>
    <?php foreach($listComments as $comment) : ?>
        <?php if(((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE) || CommentsSettings::isModerator() || ((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER && $comment->countAnswers() > 0)) : ?>
        <?php $padding_left = (count(explode('.', $comment->upline)) - 1) * $multiplier; ?>
        <?php if ($padding_left > $max) { $padding_left = $max; } ?>
       
        <!-- comment -->
        <div class="media comments_comment-block com-item bbottom mb20 pb20" data-message-id="<?= $comment->id ?>">
            <div class="com-photo">
                <? if (!empty($comment->user->id)) : ?>
                    <? $profile =Profile::model()->find('user__id =:user__id', array('user__id' => $comment->user->id));?> 
                    <? if (($profile->attachment != NULL) && ($profile->attachment->secret_name != null)) : ?>
                    <?= CHtml::image(MSmarty::attachment_get_file_name($profile->attachment->secret_name, $profile->attachment->raw_name, $profile->attachment->file_ext, '_office_profile', 'office_photo'), '', array('class'=>"mb5")); ?>
                    <? else : ?>
                        <img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="mb5">
                    <? endif; ?>    
                <? else : ?>
                    <img src="<?=Yii::app()->theme->baseUrl?>/public/comments/member-photo.png" alt="" class="mb5">
                <? endif; ?>  
            </div>
            <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><?/*<a name="<?=$comment->id?>"></a> */?>
            <div class="com-content">
                <?/*
                <h4 class="media-heading"> 
                <?=$comment->getUserName();?> 
                <span>
                    <?=$created_date->format('H:i')?>, <?=$created_date->format('d.m.Y') ?>   
                    <?php if (Yii::app()->user->checkAccess('CommentsAnswer') && ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED  && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED_FOREVER)) : ?>
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
                </h4>*/?>
                <div class="controls mb15">
                    <?/*php if((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED || (int)$comment->status_id === Comments::COMMENT_STATUS_DELETED_FOREVER) : ?>
                        <div class="note note-info">
                            <p><?=Yii::t('app', 'Комментарий удален')?></p>
                        </div>
                    <?php else :*/?>
                        <?/*if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE)) :?>
                            <span><?=Yii::t('app','Комментарий не активен');?></span>
                        <? elseif(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                            <span><?=Yii::t('app','Комментарий ожидает модерации');?></span>
                        <? endif; */?>
                        <ul>
                            <? $lastStatus = FilesStatusesHistory::getLatestStatus($comment->alias->file->id, $comment->id)?>
                            <? if ($comment->user__id != $comment->alias->file->users__id) : ?>
                                <li class="light">
                                    <?=Yii::t('app', 'Статус модерации:')?>
                                    <span class="text-gray semibold">
                                        <?=CHtml::encode($lastStatus->status->lang->name)?>
                                    </span>
                                </li>
                            <? endif; ?>
                            
                            <li>
                                <? $dateFormatter = Yii::app()->dateFormatter ?>
                                <?=$dateFormatter->format('d MMMM yyyy, hh:mm', $comment->created_at)?>
                            </li>
                        </ul>
                        <?/* if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                            <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                        <? endif; */?>
                    <?/*php endif; */?>
                </div>
                <p class="comments_comment-text"><?= CHtml::encode($comment->comment_text) ?></p>
            </div>
            <!-- body -->

        </div>
    <?endif?>
    <!-- comment media -->

<?php endforeach; ?>
</div>