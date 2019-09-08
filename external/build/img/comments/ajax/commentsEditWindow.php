<?
	use uti\Ckeditor\helpers\CkeditorHelper;
?>
<div class="form-group">
    <span class="moderation-alert" style="color:inherit;<?=((int)$settings->is_moderation === 1) ? '' : 'display:none;'?>"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
    <? $ckeditorconfig = array('name' => 'area[comment_'.$comment->id.'_ckeditor]', 'type' => 'comment', 'ckfinder' => false, 'value' => empty($comment->comment_text) ? '' : $comment->comment_text); ?>
    <?=CkeditorHelper::init($ckeditorconfig) ?>
    <div class="comment-error" style="color:red;"></div>
</div>
<p>
    <button class="button red submit"><?=Yii::t('app', 'Сохранить')?></button>
    <button class="button default cancell"><?=Yii::t('app', 'Отмена')?></button>
</p>
