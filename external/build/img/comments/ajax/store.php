<?
use uti\Ckeditor\helpers\CkeditorHelper;
?>
<div class="comments">
    <?php $multiplier = 20 ?>
    <?php $max = 200 ?>

    <?php foreach($listComments as $comment) : ?>

        <?php if(((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE) || CommentsSettings::isModerator()) : ?>
            <?php $padding_left = (count(explode('.', $comment->upline)) - 1) * $multiplier; ?>
            <?php if ($padding_left > $max) { $padding_left = $max; } ?>

            <!-- comment -->
            <div class="media comments_comment-block" data-message-id="<?= $comment->id ?>" style="margin: 5px; margin-bottom: 20px; clear: both; margin-left: <?=$padding_left?>px;">
                <?php $created_date = date_create(CHtml::encode($comment->created_at)); ?><a name="<?=$comment->id?>"></a>
                <div class="review-item clearfix">
                    <div class="review-item-submitted">
                        <strong>
							<span class="comment-author <?=$comment->isAuthorAdmin() ? 'comment-author-admin' : '';?>"><span></span><span><?=$comment->getUserName();?></span></span>
                            <?=(CommentsSettings::canEditComment($comment, Yii::app()->user->id, 'CommentsEdit') || CommentsSettings::isModerator())?'<a style="margin-left: 10px;font-weight: 100" href="#" class="comment_edit-comment">редактировать</a>':''?>
                            <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                            / <a style="font-weight: 100;" href="#" class="comment_show-comment"><?=Yii::t('app', 'показать')?></a>  
                            <?php endif; ?>
                            <?php if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_ACTIVE || (int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                                / <a style="font-weight: 100;" href="#" class="comment_hide-comment"><?=Yii::t('app', 'скрыть')?></a>  
                            <?php endif; ?>
                            <?php if(CommentsSettings::isModerator() && (int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED) : ?>
                                / <a style="font-weight: 100;" href="#" class="comment_delete-comment"><?=Yii::t('app', 'удалить')?></a>  
                            <?php endif; ?>
                        </strong>
                        
                        <em><?=MSmarty::date_format($comment->created_at, 'd.m.Y')?> - <?=MSmarty::date_format($comment->created_at, 'H:i')?></em>
                        <? if (((bool)$isRatio) && ($comment->rating > 0)) : ?>
                            <div class="rateit" data-rateit-value="<?=sprintf('%.1f', $comment->rating)?>" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
                        <? endif; ?>
                    </div>
                    <?php if ((int)$comment->status_id !== Comments::COMMENT_STATUS_DELETED) : ?>
                        <?if(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_NOT_ACTIVE)) :?>
                            <span><?=Yii::t('app','Комментарий не активен');?></span>
                        <? elseif(CommentsSettings::isModerator() && ((int)$comment->status_id === Comments::COMMENT_STATUS_MODERATE)) : ?>
                            <span><?=Yii::t('app','Комментарий ожидает модерации');?></span>
                        <? endif; ?>
                        <div class="review-item-content">
                            <?=$comment->comment_text?>
                        </div>
                        <? if($comment->getLastModifierName() && $comment->getLastModificationDate()) :?>
                            <span> «<?=Yii::t('app', 'Последний раз редактировалось');?> <?=$comment->getLastModifierName();?> <?=Yii::t('app','в');?> <?=$comment->getLastModificationDate();?>»</span>
                        <? endif; ?>
                        
                        <? if (Yii::app()->isPackageInstall('Ckeditor') && (CommentsSettings::canEditComment($comment, Yii::app()->user->id, 'CommentsEdit') || CommentsSettings::isModerator())) : ?>
                            <div class="edit_comment" style="display: none">
                                <? $ckeditorconfig = array('name' => 'area[comment_'.$comment->id.'_ckeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => empty($comment->comment_text) ? '' : $comment->comment_text); ?>
                                <?=CkeditorHelper::init($ckeditorconfig) ?>
                                <p style="margin-top: 10px">
                                    <button class="btn btn-primary finish_edit"><?=Yii::t('app', 'изменить')?></button>
                                </p>
                            </div>
                        <? endif; ?>
                    <?php endif; ?>
                    <?php if((int)$comment->status_id === Comments::COMMENT_STATUS_DELETED) : ?>
                        <div class="note note-info">
                            <p><?=Yii::t('app', 'Комментарий удален')?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- body -->

        <?endif?>
        <!-- comment media -->

    <?php endforeach; ?>
    <? $this->widget('UTIAjaxLinkPager', array(
        'pages' => $pages,
        'onClick' => 'getPage(%%page%%);return false;',
        'nextPageLabel' => '<i class="fa fa-angle-double-right"></i>',
        'prevPageLabel' => '<i class="fa fa-angle-double-left"></i>',
        'header' => '',
        'selectedPageCssClass' => 'active',
        'htmlOptions' => array(
            'class' => 'pagination utiajaxlinkpagination'
        )
    )) ?>
</div>
