<?
	use use uti\Ckeditor\helpers\CkeditorHelper;
?>
<style>
    .wmax {
        width: 100%!important;
        max-width: 100%!important;
    }
    .com-item {
        display: table;
    }
    .com-photo {
        display: table-cell;
        padding: 0 10px 0 0;
        vertical-align: top;
    }
    .com-photo img {
        max-height: 116px;
        width: auto;
    }
    .com-item {
        width: 100%;
    }
    .com-content {
        display: table-cell;
        padding: 0;
        width: 100%;
    }
    .com-item .controls ul {
        padding: 0;
        margin: 0;
        list-style: none;
    }
</style>
<div class="col-md-12">
<?$settings = CommentsSettings::getSettingsByAlias($object_alias);?>
<script type="text/template" id="edit_template">

    <div class="form-group">
        <? if((int)$settings->is_moderation === 1) : ?>
            <span style="color:inherit;"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
        <?endif;?>
        <textarea class="form-control" rows="8"></textarea>
    </div>
    <p>
        <button class="btn btn-primary submit"><?=Yii::t('app', 'Сохранить')?></button>
        <button class="btn cancell"><?=Yii::t('app', 'Отмена')?></button>
    </p>
</script>
<script type="text/template" id="answer_template">
    <div class="form-group">
        <? if((int)$settings->is_moderation === 1) : ?>
            <span style="color:inherit;"><?=Yii::t('app', 'Комментарий будет отображен после проверки модератором!')?></span>
        <?endif;?>
        <textarea class="form-control comments_comment-text" rows="8"></textarea>
        <div class="comment-answer-error" style="color:red;"></div>
    </div>
    <? if ((int)$settings->is_capcha === 1) : ?>
        <div class="form-group" style="min-height: 40px; overflow: hidden;">
            <div class="col-md-6 col-lg-5">
                <span class="col-sm-6 col-md-6" style="padding-left: 0">
                    <?php $this->widget('UTICaptcha', array(
                            'buttonLabel' => '<a href="#" onclick="refreshCaptcha();return false;" style="color: #767F88;" class="refreshCaptcha"><i class="fa fa-refresh"></i></a>',
                            'imageOptions' => array('class' => 'captcha-img'),
                            'captchaAction' => '/comments/captcha/get?' . rand(10000, 100000),
                        ))?>
                    <a class="refreshCaptcha" href="#" onclick="refreshCaptcha();return false;" style="color: #767F88;"><i class="fa fa-refresh"></i></a>
                </span>
                <span class="col-sm-6 col-md-6" style="padding: 0">
                    <?=Yii::t('app','Пожалуйста, введите код с картинки.');?>
                </span>
            </div>
            <div class="col-md-3 col-lg-3">
                <?php echo CHtml::textField('verifyCode', '', array('class' => 'form-control comment_answer_captcha')) ?>
            </div>
            <div class="col-md-3 col-lg-4 captcha-error" style="color:red;"></div>
        </div>
    <? endif; ?>
    <p>
        <button class="btn btn-primary submit"><?=Yii::t('app', 'ответить')?></button>
        <button class="btn cancell"><?=Yii::t('app', 'Отмена')?></button>
    </p>
</script>
<script type="text/javascript">
<? if ((int)$settings->is_capcha === 1) : ?>
    function refreshCaptcha(){
        $.ajax({
            url : '<?=Yii::app()->createUrl('/comments/captcha/get');?>',
            dataType : 'json',
            type : 'GET',
            data : {
                refresh  : '1'
            },
            success : function(data){
                if(data.url){
                    $('.captcha-img').attr('src', data.url);
                }
            }
        });
    }
<? endif; ?>
    
    (function(){
        var Comments = function(params){
            this.settings = {
                urlAdd : app.createAbsoluteUrl('comments/ajax/add'),
                urlGet : app.createAbsoluteUrl('comments/ajax/get'),
                urlAnswer : app.createAbsoluteUrl('comments/ajax/answer'),
                urlEdit : app.createAbsoluteUrl('comments/ajax/edit'),
                changeStatus : app.createAbsoluteUrl('comments/ajax/changeStatus'),
            };
            
            this.object_id = null;
            this.object_alias = null;
            this.status_id = null;
            
            this.init = function(params){
                if (params)
                {
                    if (params.object_id) this.object_id = params.object_id;
                    if (params.object_alias) this.object_alias = params.object_alias;
                }
                
                this.bindEvents();
            }
            
            this.bindEvents = function(){
                $('#comments_list-comments')
                .on('click', '.comment_answer-comment', {object : this}, this.answer)
                .on('click', '.comment_edit-comment', {object : this}, this.edit)
                .on('click', '.comment_show-comment', {object : this, status : <?=Comments::COMMENT_STATUS_ACTIVE?>}, this.changeStatus)
                .on('click', '.comment_hide-comment', {object : this, status : <?=Comments::COMMENT_STATUS_NOT_ACTIVE?>}, this.changeStatus)
                .on('click', '.comment_delete-comment', {object : this, status : <?=Comments::COMMENT_STATUS_DELETED_FOREVER?>}, this.changeStatus);
                
                $('#comment_add-comment').on('click', {object : this}, this.add);
            };
            
            this.add = function(event){
                event.preventDefault();
                
                $.ajax({
                    url : event.data.object.settings.urlAdd,
                    dataType : 'json',
                    type : 'POST',
                    data : {
                        YII_CSRF_TOKEN : app.csrfToken,
                        object_id   : event.data.object.object_id,
                        object_alias   : event.data.object.object_alias,
                        comment_text   : $('#comments_comment-text').val(),
                        <? if ((int)$settings->is_capcha === 1) : ?>
                        captcha        : $('#comments_captcha').val(),
                        <? endif; ?>
                    },
                    success : function(data){
                        if(data.data == 'success'){
                            event.data.object.get();
                            _toastr(T("Комментарий опубликован"), "top-right", "success");
                            //event.data.object.get();
                            $('#comments_comment-text').val('');
                            $('#comments_comment-text').removeClass('error');
                            $('.comment-error').html('');
                            <?
                                if($settings->is_moderation == 1) : 
                            ?>
                            $('.comment-add-form').hide();
                            $('.comment-moderation-message').show();
                            <? endif; ?>
                            <? if ((int)$settings->is_capcha === 1) : ?>
                                $('.captcha-error').html('');
                                refreshCaptcha();
                                $('#comments_captcha').val('');
                            <? endif; ?>
                        }else{
                            <? if ((int)$settings->is_capcha === 1) : ?>
                            if(data.data.verifyCode)
                            {
                                $('#comments_captcha').addClass('error');
                                $('.captcha-error').html(data.data.verifyCode);
                                refreshCaptcha();
                                $('#comments_captcha').val('');
                            }
                            else
                            {
                                $('.captcha-error').html('');
                                refreshCaptcha();
                                $('#comments_captcha').val('');
                            }
                            <? endif; ?>
                            if(data.data.comment)
                            {
                                $('#comments_comment-text').addClass('error');
                                $('.comment-error').html(data.data.comment);
                                <? if ((int)$settings->is_capcha === 1) : ?>
                                    refreshCaptcha();
                                
                                    $('#comments_captcha').val('');
                                    if($('#comments_captcha').val() == '')
                                    {
                                        $('#comments_captcha').addClass('error');
                                        $('.captcha-error').html('<?=Yii::t('app','Необходимо заполнить поле Капча.');?>');
                                    }
                                    else
                                    {
                                        $('.captcha-error').html('');
                                        $('#comments_captcha').removeClass('error');
                                    }
                                <? endif; ?>
                            }
                            else
                            {
                                $('#comments_comment-text').removeClass('error');
                                $('.comment-error').html('');
                            }
                        }
                    }
                });
            };
            
            this.answer = function(event){
                event.preventDefault();

                var textBlock = $(this).closest('.comments_comment-block'),
                textEditBlock = $('<div class="answer-block" style="display: none;"></div>').html($('#answer_template').text());
                
                if (textBlock.find('.answer-block').length)
                {
                    textBlock.find('.answer-block').hide(300, function(){
                        $(this).remove();
                    });
                    return;
                }
                
                textBlock.append(textEditBlock);

                textEditBlock.stop(false, true).show(300);
                
                textEditBlock.find('.captcha-img').attr('src','/comments/captcha/get?'+Math.random());
                
                $(textEditBlock).on('click', 'button.cancell', function(){
                    $(this).closest('.comments_comment-block').find('.answer-block').hide(300, function(){
                        $(this).remove();
                    });
                    return;
                });
                
                $(textEditBlock).on('click', 'button.submit', function(){
                    $.ajax({
                        url : event.data.object.settings.urlAnswer,
                        dataType : 'json',
                        type : 'POST',
                        data : {
                            YII_CSRF_TOKEN : app.csrfToken,
                            object_id   : event.data.object.object_id,
                            object_alias   : event.data.object.object_alias,
                            comment_id     : $(this).closest('.comments_comment-block').attr('data-message-id'),
                            comment_text   : textEditBlock.find('textarea').val(),
                            <? if ((int)$settings->is_capcha === 1) : ?>
                            captcha        : $(textEditBlock).find('.comment_answer_captcha').val(),
                            <? endif; ?>
                        },
                        success : function(data){
                            if(data.data == 'success'){
                                event.data.object.get();
                                textEditBlock.find('.comments_comment-text').removeClass('error');
                                textEditBlock.find('.comment-answer-error').html('');
                                $('#comments_comment-text').val('');
                                textBlock.find('.answer-block').hide(300, function(){
                                    $(this).remove();
                                });
                                <? if ((int)$settings->is_capcha === 1) : ?>
                                    textEditBlock.find('.captcha-error').html('');
                                    textEditBlock.find('.captcha-img').attr('src','/comments/captcha/get?'+Math.random());
                                    textEditBlock.find('.comment_answer_captcha').val('');
                                    refreshCaptcha();
                                <? endif; ?>
                            }else{
                                <? if ((int)$settings->is_capcha === 1) : ?>
                                    if(data.data.verifyCode)
                                    {
                                        textEditBlock.find('.comment_answer_captcha').addClass('error');
                                        textEditBlock.find('.captcha-error').html(data.data.verifyCode);
                                        refreshCaptcha();
                                        textEditBlock.find('.comment_answer_captcha').val('');
                                    }
                                    else
                                    {
                                        textEditBlock.find('.captcha-error').html('');
                                        refreshCaptcha();
                                        textEditBlock.find('.comment_answer_captcha').val('');
                                    }
                                    <? endif; ?>
                                    if(data.data.comment)
                                    {
                                        textEditBlock.find('.comments_comment-text').addClass('error');
                                        textEditBlock.find('.comment-answer-error').html(data.data.comment);
                                        <? if ((int)$settings->is_capcha === 1) : ?>
                                            refreshCaptcha();
                                        
                                            textEditBlock.find('.comment_answer_captcha').val('');
                                            if(textEditBlock.find('.comment_answer_captcha').val() == '')
                                            {
                                                textEditBlock.find('.comment_answer_captcha').addClass('error');
                                                textEditBlock.find('.captcha-error').html('<?=Yii::t('app','Необходимо заполнить поле Капча.');?>');
                                            }
                                            else
                                            {
                                                textEditBlock.find('.captcha-error').html('');
                                                textEditBlock.find('.comment_answer_captcha').removeClass('error');
                                            }
                                        <?endif;?>
                                    }
                                    else
                                    {
                                        textEditBlock.find('.comments_comment-text').removeClass('error');
                                        textEditBlock.find('.comment-answer-error').html('');
                                    }
                            }
                        }
                    });
                });                
            };
            
            this.edit = function(event){
                event.preventDefault();
                
                var button = $(this);
                if(button.closest('.comments_comment-block').find('.comment-edit-wrapper').length == 0){
                    
                    button.hide();
                    var textBlock = $(this).closest('.comments_comment-block').find('.comments_comment-text');
                    
                    textBlock.hide();

                    var textEditBlock = $('<div class="comment-edit-wrapper"></div>').html($('#edit_template').text());
                    textEditBlock.find('textarea').text(textBlock.text());
                    
                    $(textEditBlock).on('click', 'button.cancell', {textEditBlock : textEditBlock, textBlock : textBlock, self : event.data.object}, function(event){
                        $(this).closest('.comment-edit-wrapper').remove();
                        textBlock.show();
                        button.show();
                    });
                    
                    $(textEditBlock).on('click', 'button.submit', {textEditBlock : textEditBlock, textBlock : textBlock, self : event.data.object}, function(event){
                        event.preventDefault();
                        
                        button.show();
                        event.data.textBlock
                        .text(event.data.textEditBlock.find('textarea').val())
                        .show();
                        
                        event.data.textEditBlock.remove();
                        
                        $.ajax({
                            url : event.data.self.settings.urlEdit,
                            type : 'POST',
                            data : {
                                YII_CSRF_TOKEN : app.csrfToken,
                                object_id   : event.data.self.object_id,
                                object_alias  : event.data.self.object_alias,
                                comment_id     : event.data.textBlock.closest('.comments_comment-block').attr('data-message-id'),
                                comment_text   : event.data.textBlock.text(),
                            },
                            success : function(data){
                                event.data.self.get();
                            }
                        });
                        
                        $(this).remove();
                    });
                    
                    textBlock.after(textEditBlock);
                
                }
            };

            this.get = function(){
                $.ajax({
                    url : this.settings.urlGet,
                    dataType : 'json',
                    type : 'POST',
                    data : {
                        YII_CSRF_TOKEN  : app.csrfToken,
                        object_id       : this.object_id,
                        object_alias    : this.object_alias,
                        template        : 'file_moderation_office_comments',
                        isRatio         : <?=(int)$this->isRatio?>
                    },
                    success : function(data){
                        if (data && data.html)
                        {
                            $('#comments_list-comments').empty().html(data.html);
                        }
                    }
                });
            };
            
            this.changeStatus = function(event){
                event.preventDefault();
                if(event.data.status == <?=Comments::COMMENT_STATUS_DELETED_FOREVER?>)
                {
                    if(!confirm(T('Вы действительно хотите удалить комментарий!')))
                    {
                        return false;
                    }
                }
                $.ajax({
                    url : event.data.object.settings.changeStatus,
                    dataType : 'json',
                    type : 'POST',
                    data : {
                        YII_CSRF_TOKEN : app.csrfToken,
                        object_id   : event.data.object.object_id,
                        object_alias   : event.data.object.object_alias,
                        status   : event.data.status,
                        comment_id   : $(this).closest('.comments_comment-block').attr('data-message-id'),
                    },
                    success : function(data){
                        event.data.object.get();
                        //$('#comments_comment-text').val('');
                    }
                });
            };

this.init(params);
}

$(function(){
    var comments = new Comments({
        object_id : "<?=$object_id ?>",
        object_alias : "<?=$object_alias ?>"
    });
});
})();
</script>
<div id="comments_list-comments">
    <?php include ('file_moderation_office_comments_list.php'); ?>
</div>

<?php if (Yii::app()->user->checkAccess('Comments') && CommentsSettings::canPublishComments($object_alias)) : ?>
    <?php include('file_moderation_office_add.php') ?>
<?php endif; ?>
</div>