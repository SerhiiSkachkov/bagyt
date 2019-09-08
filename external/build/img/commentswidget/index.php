<?
use uti\AdminComments\models\Comments;
use uti\AdminComments\models\CommentsObjectAliases;
use uti\AdminComments\models\CommentsObjects;
use uti\AdminComments\models\CommentsObjectsAccess;
use uti\AdminComments\models\CommentsSettings;
use uti\Ckeditor\helpers\CkeditorHelper;
use uti\AdminCaptcha\models\Captcha;
use uti\AdminCaptcha\models\CaptchaSettings;

?>

<? $settings = CommentsSettings::getSettingsByAlias($object_alias); ?>
<? $captcha_settings = Captcha::model()->find('active = :active', array(':active' => (int)TRUE)); ?>

<? if ($captcha_settings->alias == 'reCAPTCHA'): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=explicit&hl=<?= Yii::app()->language; ?>"></script>
<? endif; ?>

    <script type="text/template" id="edit_template">

        <div class="form-group">
            <span class="moderation-alert"
                  style="color:inherit;<?= ((int)$settings->is_moderation === 1) ? '' : 'display:none;' ?>"><?= Yii::t('app', 'Комментарий будет отображен после проверки модератором!') ?></span>
            <textarea class="form-control" rows="8"></textarea>
            <div class="comment-error" style="color:red;"></div>
        </div>
        <p>
            <button class="btn btn-primary submit"><?= Yii::t('app', 'Сохранить') ?></button>
            <button class="btn cancell"><?= Yii::t('app', 'Отмена') ?></button>
        </p>
    </script>
    <script type="text/template" id="answer_template">
        <? if (!Yii::app()->isPackageInstall('Ckeditor')) : ?>
            <div class="form-group">
                <span class="moderation-alert"
                      style="color:inherit;<?= ((int)$settings->is_moderation === 1) ? '' : 'display:none;' ?>"><?= Yii::t('app', 'Комментарий будет отображен после проверки модератором!') ?></span>
                <textarea class="form-control comments_comment-text" rows="8"></textarea>
                <div class="comment-answer-error" style="color:red;"></div>
            </div>
        <? endif; ?>
        <? if ((Captcha::captchaConditions('textCAPTCHA', 'comments_news', (int)TRUE) && $object_alias == 'news') || (Captcha::captchaConditions('textCAPTCHA', 'comments_product', (int)TRUE) && $object_alias == 'product') || (Captcha::captchaConditions('textCAPTCHA', 'comments_blog_posts', (int)TRUE) && $object_alias == 'blog_posts')): ?>


            <div class="form-group capcha-field" style="margin-top: 10px; min-height: 40px; overflow: hidden;">
                <div class="col-md-6 col-lg-5">
                            <span class="col-sm-6 col-md-6" style="padding-left: 0">
                                <?php $this->widget('uti\cms\components\UTICaptcha', array(
                                    'buttonLabel' => '<a href="#" onclick="refreshCaptcha();return false;" style="color: #767F88;" class="refreshCaptcha"><i class="fa fa-refresh"></i></a>',
                                    'imageOptions' => array('class' => 'captcha-img'),
                                    'captchaAction' => '/comments/captcha/get?' . rand(10000, 100000),
                                )) ?>
                                <a class="refreshCaptcha" href="#" onclick="refreshCaptcha();return false;"
                                   style="color: #767F88;"><i class="fa fa-refresh"></i></a>
                            </span>
                    <span class="col-sm-6 col-md-6" style="padding: 0">
                                <?= Yii::t('app', 'Пожалуйста, введите код с картинки.'); ?>
                            </span>
                </div>
                <div class="col-md-3 col-lg-3">
                    <?php echo CHtml::textField('verifyCode', '', array('class' => 'form-control comment_answer_captcha')) ?>
                </div>
                <div class="col-md-3 col-lg-4 captcha-error" style="color:red;"></div>
            </div>
        <? endif; ?>
        <? if ((Captcha::captchaConditions('reCAPTCHA', 'comments_news', (int)TRUE) && $object_alias == 'news') || (Captcha::captchaConditions('reCAPTCHA', 'comments_product', (int)TRUE) && $object_alias == 'product') || (Captcha::captchaConditions('reCAPTCHA', 'comments_blog_posts', (int)TRUE) && $object_alias == 'blog_posts')): ?>

            <div class="form-group"
                 style="min-height: 40px; overflow: hidden; <?= ($captcha_settings->alias == 'reCAPTCHA') ? '' : 'display:none;' ?>">
                <div class="g-recaptcha" id="answer-captcha"></div>
                <div class="captcha-error" style="color:red;"></div>
            </div>

        <? endif; ?>

    </script>

    <script type="text/javascript">
        function refreshCaptcha() {
            <? if ($captcha_settings->alias == 'textCAPTCHA'): ?>
            <? foreach ($captcha_settings->settings as $setting): ?>
            <? if ($setting->name == 'comments_news'): ?>
            <? if ($setting->value == (int)TRUE): ?>
            $.ajax({
                url: '<?=Yii::app()->createUrl('/comments/captcha/get');?>',
                dataType: 'json',
                type: 'GET',
                data: {
                    refresh: '1'
                },
                success: function (data) {
                    if (data.url) {
                        $('.captcha-img').attr('src', data.url);
                    }
                }
            });
            <? endif; ?>
            <? endif; ?>
            <? endforeach; ?>
            <? endif; ?>
        }

        (function () {
            var Comments = function (params) {
                this.settings = {
                    urlAdd: app.createAbsoluteUrl('comments/ajax/add'),
                    urlGet: app.createAbsoluteUrl('comments/ajax/get'),
                    urlAnswer: app.createAbsoluteUrl('comments/ajax/answer'),
                    urlAnswerWindow: app.createAbsoluteUrl('comments/ajax/getAnswerWindow'),
                    urlEdit: app.createAbsoluteUrl('comments/ajax/edit'),
                    urlEditWindow: app.createAbsoluteUrl('comments/ajax/getEditWindow'),
                    changeStatus: app.createAbsoluteUrl('comments/ajax/changeStatus'),
                    captcha: '<?= $captcha_settings->alias; ?>',
                    isModeration: <?= ((int)$settings->is_moderation === 1) ? 1 : 0 ?>,
                    sortSelector: '#commentSortField',
                    sortDirection: '0',
                };

                this.object_id = null;
                this.object_alias = null;
                this.status_id = null;

                this.init = function (params) {
                    if (params) {
                        if (params.object_id) this.object_id = params.object_id;
                        if (params.object_alias) this.object_alias = params.object_alias;
                    }

                    this.bindEvents();
                }

                this.setOptions = function (settings) {
                    this.settings.isCapcha = settings.is_capcha;
                    this.settings.isModeration = settings.is_moderation;

                    if (settings.is_moderation == 1) {
                        $('.moderation-alert').show();
                    }
                    else {
                        $('.moderation-alert').hide();
                    }
                }

                this.bindEvents = function () {
                    $('#comments_list-comments')
                        .on('click', '.comment_answer-comment', {object: this}, this.answer)
                        .on('click', '.comment_edit-comment', {object: this}, this.edit)
                        .on('click', '.comment_show-comment', {
                            object: this,
                            status: <?=Comments::COMMENT_STATUS_ACTIVE?>}, this.changeStatus)
                        .on('click', '.comment_hide-comment', {
                            object: this,
                            status: <?=Comments::COMMENT_STATUS_NOT_ACTIVE?>}, this.changeStatus)
                        .on('click', '.comment_delete-comment', {
                            object: this,
                            status: <?=Comments::COMMENT_STATUS_DELETED_FOREVER?>}, this.changeStatus)
                        .on('change', this.settings.sortSelector, {object: this}, this.sort);

                    $('#comment_add-comment').on('click', {object: this}, this.add);
                };

                this.add = function (event) {
                    event.preventDefault();

                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                    $('#comments_comment-text').val(CKEDITOR.instances['textareackeditor'].getData());
                    <? endif; ?>

                    var requestData = {
                        YII_CSRF_TOKEN: app.csrfToken,
                        object_id: event.data.object.object_id,
                        object_alias: event.data.object.object_alias,
                        comment_text: $('#comments_comment-text').val(),
                        recaptcha: $("#g-recaptcha-response").val(),
                    };

                    if (event.data.object.settings.captcha == 'textCAPTCHA') {
                        requestData['captcha'] = $('#comments_captcha').val();
                    }

                    $.ajax({
                        url: event.data.object.settings.urlAdd,
                        dataType: 'json',
                        type: 'POST',
                        data: requestData,
                        success: function (data) {
                            if (data.settings) {
                                event.data.object.setOptions(data.settings);
                            }
                            if (data.data == 'success') {
                                event.data.object.get();
                                $('#comments_comment-text').val('');
                                <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                                CKEDITOR.instances['textareackeditor'].setData('');
                                <? endif; ?>
                                $('#comments_comment-text').removeClass('error');
                                $('.comment-error').html('');
                                if (event.data.object.settings.isModeration == 1) {
                                    $('form.comment-add-form').css('display', 'none');
                                    $('.comment-add-form + .comment-moderation-message').show();
                                }
                                refreshCaptcha();
                                $('.captcha-error').html('');
                                $('#comments_captcha').val('');
                            } else {
                                if (data.settings.captcha == 'textCAPTCHA') {
                                    if (data.data.verifyCode) {
                                        $('#comments_captcha').addClass('error');
                                        $('.captcha-error').html(data.data.verifyCode);
                                        refreshCaptcha();
                                        $('#comments_captcha').val('');
                                    }
                                    else {
                                        $('.captcha-error').html('');
                                        refreshCaptcha();
                                        $('#comments_captcha').val('');
                                    }
                                }
                                if (data.settings.captcha == 'reCAPTCHA') {
                                    if (data.data.reCaptchaGuid) {
                                        $('#comments_captcha').addClass('error');
                                        $('.captcha-error').html(data.data.reCaptchaGuid);
                                        //refreshCaptcha();
                                        $('#comments_captcha').val('');
                                    }
                                    else {
                                        $('.captcha-error').html('');
                                        //refreshCaptcha();
                                        $('#comments_captcha').val('');
                                    }
                                }
                                if (data.data.comment) {
                                    $('#comments_comment-text').addClass('error');
                                    $('.comment-error').html(data.data.comment);
                                    if (event.data.object.settings.isCapcha == 1) {
                                        refreshCaptcha();

                                        $('#comments_captcha').val('');
                                        if ($('#comments_captcha').val() == '') {
                                            $('#comments_captcha').addClass('error');
                                            $('.captcha-error').html('<?=Yii::t('app', 'Необходимо заполнить поле Капча.');?>');
                                        }
                                        else {
                                            $('.captcha-error').html('');
                                            $('#comments_captcha').removeClass('error');
                                        }
                                    }
                                }
                                else {
                                    $('#comments_comment-text').removeClass('error');
                                    $('.comment-error').html('');
                                }
                            }
                        }
                    });
                };

                this.sort = function (event) {
                    console.log(event);
                    $(this).val();
                    event.data.object.settings.sortDirection = $(this).val();
                    event.data.object.get();
                }

                this.answer = function (event) {
                    event.preventDefault();

                    var textBlock = $(this).closest('.comments_comment-block');
                    textBlock.append("<div class = 'comment_captcha'></div>");

                    textBlock.find('.comment-edit-wrapper:visible').find('.cancell').click();

                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                    textBlock.find('.answer-block-ckeditor').addClass('answer-block').show();

                    textEditBlock = $('<div class="answer-block" style="display: none;"></div>').html($('#answer_template').text());

                    setTimeout(function () {
                        textBlock.find('.form-group').append(textEditBlock);
                    }, 1000);

                    $.ajax({
                        url: event.data.object.settings.urlAnswerWindow,
                        type: 'POST',
                        data: {
                            YII_CSRF_TOKEN: app.csrfToken,
                            object_id: event.data.object.object_id,
                            object_alias: event.data.object.object_alias,
                            comment_id: textBlock.closest('.comments_comment-block').attr('data-message-id'),
                        },
                        success: function (data) {
                            if (data.html) {
                                <? if ($captcha_settings->alias == 'reCAPTCHA'): ?>
                                grecaptcha.render(textBlock.find('.comment_captcha')[0], {
                                    'sitekey': '<?= CaptchaSettings::getParams('site_key', 'reCAPTCHA')->value; ?>',
                                });
                                <? endif; ?>
                                var container = textBlock.find('.answer-block-ckeditor');
                                container.addClass('test');
                                container.html(data.html);
                            }
                        }
                    });

                    textEditBlock.show();
                    <? else : ?>
                    textEditBlock = $('<div class="answer-block" style="display: none;"></div>').html($('#answer_template').text());

                    if (textBlock.find('.answer-block').length) {
                        textBlock.find('.answer-block').hide(300, function () {
                            $(this).remove();
                        });
                        return;
                    }

                    textBlock.append(textEditBlock);
                    <? if ($captcha_settings->alias == 'reCAPTCHA'): ?>
                    grecaptcha.render(textEditBlock.find("#answer-captcha")[0], {
                        'sitekey': '<?= CaptchaSettings::getParams('site_key', 'reCAPTCHA')->value; ?>',
                    });
                    <? endif; ?>
                    textEditBlock.stop(false, true).show(300);
                    <? endif; ?>

                    textEditBlock.find('.captcha-img').attr('src', '/comments/captcha/get?' + Math.random());
                    <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                    $(document).on('click', '.answer-block .cancell', function () {
                        $(this).closest('.answer-block-ckeditor').removeClass('answer-block').hide();
                        $(this).closest('.comments_comment-block').find('.comment_captcha').remove();
                        return;
                    });
                    <? else : ?>
                    $(textEditBlock).on('click', 'button.cancell', function () {
                        $(this).closest('.comments_comment-block').find('.answer-block').hide(300, function () {
                            $(this).remove();
                        });
                        return;
                    });
                    <? endif; ?>

                    $(textBlock).on('click', '.answer-block button.submit', function () {
                        var commentText;
                        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                        commentText = CKEDITOR.instances['comment_' + $(this).parent().closest('.comments_comment-block').attr('data-message-id') + '_answer_ckeditor'].getData();
                        if (commentText == '') {
                            $(this).closest('.comments_comment-block').find('.comment-edit-wrapper-ckeditor').find('.comment-error').html('<?=Yii::t('app', 'Необходимо заполнить поле Комментарий.');?>');
                            errors = true;
                        }
                        <? else: ?>
                        commentText = textEditBlock.find('.comments_comment-text').val();
                        <? endif; ?>
                        var requestData = {
                            YII_CSRF_TOKEN: app.csrfToken,
                            object_id: event.data.object.object_id,
                            object_alias: event.data.object.object_alias,
                            comment_id: $(this).closest('.comments_comment-block').attr('data-message-id'),
                            comment_text: commentText,
                            <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                            recaptcha: textBlock.find('.comment_captcha').find('.g-recaptcha-response').val(),
                            <? else: ?>
                            recaptcha: $(this).closest('.answer-block').find('.g-recaptcha-response').val(),
                            <? endif; ?>
                            captcha: $('.comment_answer_captcha').val(),
                        };

                        if (event.data.object.settings.isCapcha) {
                            requestData['captcha'] = $(this).closest('.comments_comment-block').find('.comment_answer_captcha').val();
                        }

                        $.ajax({
                            url: event.data.object.settings.urlAnswer,
                            dataType: 'json',
                            type: 'POST',
                            data: requestData,
                            success: function (data) {
                                if (data.settings) {
                                    event.data.object.setOptions(data.settings);
                                }
                                if (data.data == 'success') {
                                    event.data.object.get();
                                    textEditBlock.find('.comments_comment-text').removeClass('error');
                                    textEditBlock.find('.comment-answer-error').html('');
                                    $('#comments_comment-text').val('');
                                    textBlock.find('.answer-block').hide(300, function () {
                                        $(this).remove();
                                    });
                                    if (event.data.object.settings.isCapcha == 1) {
                                        textEditBlock.find('.captcha-error').html('');
                                        textEditBlock.find('.captcha-img').attr('src', '/comments/captcha/get?' + Math.random());
                                        textEditBlock.find('.comment_answer_captcha').val('');
                                        refreshCaptcha();
                                    }
                                } else {
                                    if (event.data.object.settings.isCapcha == 1) {
                                        if (data.data.verifyCode) {
                                            textEditBlock.find('.comment_answer_captcha').addClass('error');
                                            textEditBlock.find('.captcha-error').html(data.data.verifyCode);
                                            refreshCaptcha();
                                            textEditBlock.find('.comment_answer_captcha').val('');
                                        }
                                        else {
                                            textEditBlock.find('.captcha-error').html('');
                                            refreshCaptcha();
                                            textEditBlock.find('.comment_answer_captcha').val('');
                                        }
                                    }
                                    if (data.data.comment) {
                                        textEditBlock.find('.comments_comment-text').addClass('error');
                                        textEditBlock.find('.comment-answer-error').html(data.data.comment);
                                        if (event.data.object.settings.isCapcha == 1) {
                                            refreshCaptcha();

                                            textEditBlock.find('.comment_answer_captcha').val('');
                                            if (textEditBlock.find('.comment_answer_captcha').val() == '') {
                                                textEditBlock.find('.comment_answer_captcha').addClass('error');
                                                textEditBlock.find('.captcha-error').html('<?=Yii::t('app', 'Необходимо заполнить поле Капча.');?>');
                                            }
                                            else {
                                                textEditBlock.find('.captcha-error').html('');
                                                textEditBlock.find('.comment_answer_captcha').removeClass('error');
                                            }
                                        }
                                    }
                                    else {
                                        textEditBlock.find('.comments_comment-text').removeClass('error');
                                        textEditBlock.find('.comment-answer-error').html('');
                                    }
                                }
                            }
                        });
                    });
                };

                this.edit = function (event) {
                    event.preventDefault();

                    var button = $(this);
                    if (button.closest('.comments_comment-block').find('.comment-edit-wrapper').length == 0) {

                        $(this).closest('.comments_comment-block').find('.answer-block:visible').find('.cancell').click();

                        button.hide();
                        var textBlock = $(this).closest('.comments_comment-block').find('.comments_comment-text');

                        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                        var textEditBlock = $(this).closest('.comments_comment-block').find('.comment-edit-wrapper-ckeditor');
                        $.ajax({
                            url: event.data.object.settings.urlEditWindow,
                            type: 'POST',
                            data: {
                                YII_CSRF_TOKEN: app.csrfToken,
                                object_id: event.data.object.object_id,
                                object_alias: event.data.object.object_alias,
                                comment_id: textBlock.closest('.comments_comment-block').attr('data-message-id'),
                            },
                            success: function (data) {
                                if (data.html) {
                                    textEditBlock.html(data.html);
                                }
                            }
                        });
                        textBlock.hide();
                        textEditBlock.show().addClass('comment-edit-wrapper');
                        <? else : ?>
                        textBlock.hide();
                        var textEditBlock = $('<div class="comment-edit-wrapper"></div>').html($('#edit_template').text());
                        textEditBlock.find('textarea').text(textBlock.text());
                        <? endif; ?>

                        $(textEditBlock).on('click', 'button.cancell', {
                            textEditBlock: textEditBlock,
                            textBlock: textBlock,
                            self: event.data.object
                        }, function (event) {
                            <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                            $(this).closest('.comments_comment-block').find('.comment-edit-wrapper-ckeditor').hide().removeClass('comment-edit-wrapper');
                            $(this).closest('.comments_comment-block').find('.comment_edit-comment').show();
                            textBlock.show();
                            <? else : ?>
                            $(this).closest('.comment-edit-wrapper').remove();
                            textBlock.show();
                            button.show();
                            <? endif; ?>
                        });

                        $(textEditBlock).on('click', 'button.submit', {
                            textEditBlock: textEditBlock,
                            textBlock: textBlock,
                            self: event.data.object
                        }, function (event) {
                            event.preventDefault();
                            var errors = false;
                            <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                            $(this).closest('.comments_comment-block').find('.comment_edit-comment').parent().show();
                            var commentText = CKEDITOR.instances['comment_' + $(this).closest('.comments_comment-block').attr('data-message-id') + '_ckeditor'].getData();
                            if (commentText == '') {
                                $(this).closest('.comments_comment-block').find('.comment-edit-wrapper-ckeditor').find('.comment-error').html('<?=Yii::t('app', 'Необходимо заполнить поле Комментарий.');?>');
                                errors = true;
                            }
                            <? else : ?>
                            button.show();
                            event.data.textBlock
                                .text(event.data.textEditBlock.find('textarea').val())
                                .show();
                            if (event.data.textEditBlock.find('textarea').val() == '' || !event.data.textEditBlock.find('textarea').val()) {
                                event.data.textEditBlock.find('.comment-error').html('<?=Yii::t('app', 'Необходимо заполнить поле Комментарий.');?>');
                                errors = true;
                            }
                            else {
                                event.data.textEditBlock.remove();
                            }
                            <? endif; ?>

                            if (!errors) {
                                $.ajax({
                                    url: event.data.self.settings.urlEdit,
                                    type: 'POST',
                                    data: {
                                        YII_CSRF_TOKEN: app.csrfToken,
                                        object_id: event.data.self.object_id,
                                        object_alias: event.data.self.object_alias,
                                        comment_id: event.data.textBlock.closest('.comments_comment-block').attr('data-message-id'),
                                        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                                        comment_text: commentText,
                                        <? else : ?>
                                        comment_text: event.data.textBlock.text(),
                                        <? endif; ?>
                                    },
                                    success: function (data) {
                                        event.data.self.get();
                                    }
                                });

                                $(this).remove();
                            }
                        });
                        <? if (Yii::app()->isPackageInstall('Ckeditor')) : ?>
                        textBlock.after(textEditBlock);
                        <? endif; ?>
                    }
                };

                this.get = function () {
                    $.ajax({
                        url: this.settings.urlGet,
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            YII_CSRF_TOKEN: app.csrfToken,
                            object_id: this.object_id,
                            object_alias: this.object_alias,
                            template: 'comments',
                            backorder: this.settings.sortDirection,
                            isRatio: <?=(int)$this->isRatio?>
                        },
                        success: function (data) {
                            if (data && data.html) {
                                $('#comments_list-comments').empty().html(data.html);
                            }
                        }
                    });
                };

                this.changeStatus = function (event) {
                    event.preventDefault();
                    if (event.data.status == <?=Comments::COMMENT_STATUS_DELETED_FOREVER?>) {
                        if (!confirm(T('Вы действительно хотите удалить комментарий!'))) {
                            return false;
                        }
                    }
                    $.ajax({
                        url: event.data.object.settings.changeStatus,
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            YII_CSRF_TOKEN: app.csrfToken,
                            object_id: event.data.object.object_id,
                            object_alias: event.data.object.object_alias,
                            status: event.data.status,
                            comment_id: $(this).closest('.comments_comment-block').attr('data-message-id'),
                        },
                        success: function (data) {
                            event.data.object.get();
                            //$('#comments_comment-text').val('');
                        }
                    });
                };

                this.init(params);
            }

            $(function () {
                var comments = new Comments({
                    object_id: "<?=$object_id ?>",
                    object_alias: "<?=$object_alias ?>"
                });
            });
        })();
    </script>

    <style>
        .comment-edit-wrapper-ckeditor,
        .answer-block-ckeditor {
            width: calc(100% - 3px);
            margin-top: 20px;
        }
    </style>
    <div id="comments_list-comments">
        <?php include('comments_list.php'); ?>
    </div>

<?php if (Yii::app()->user->checkAccess('Comments') && CommentsSettings::canPublishComments($object_alias)) : ?>
    <?php include('add.php') ?>
<?php endif; ?>
