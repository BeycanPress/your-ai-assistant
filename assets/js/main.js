(($) => {
    "use strict";

    $(document).ready(() => {
        
        function swalPopup(message, type, html = null) {
            return Swal.fire({
                title: message,
                html,
                icon: type,
                didOpen: () => {
                    Swal.hideLoading();
                }
            });
        }
        function infoPopup(message, html = null) {
            return swalPopup(message, 'info', html);
        }
        
        function errorPopup(message, html = null) {
            return swalPopup(message, 'error', html);
        }

        function errorsPopup(response) {
            if (Array.isArray(response.data)) {
                let errors = response.data ? response.data.join("<br>") : null;
                errorPopup(YAIA.lang.errors, errors);
            } else {
                errorPopup(response.message);
            }
        }
        
        function successPopup(message, html = null) {
            return swalPopup(message, 'success', html);
        }
        
        function waitingPopup(title, html = null) {
            Swal.fire({
                title,
                html,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        function checkWpGutengergEditor() {
            return typeof wp !== 'undefined' && typeof wp.data !== 'undefined' && typeof wp.data.select("core/editor") != 'undefined';
        }
        
        $(document).on('click', '#yaiaCreateArticle', function(e) {
            e.preventDefault();

            let postTitle = $("[name='post_title']").val();
            if (checkWpGutengergEditor()) {
                postTitle = wp.data.select("core/editor").getEditedPostAttribute('title');
            }

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaCreateContent',
            };

            data.customPrompt  = $("[name='yaiaMetabox[customPrompt]']").val();
            data.customPromptS = $("[name='yaiaMetabox[customPromptS]']").val();
            data.generateImage = $("[name='yaiaMetabox[generateImage]']").val();
            if (data.generateImage == '1') {
                data.imagePrompt   = $("[name='yaiaMetabox[imagePrompt]']").val();
                data.imageCount   = $("[name='yaiaMetabox[imageCount]']").val();
                data.imageSizes   = $("[name='yaiaMetabox[imageSizes]']").val();
            }

            if (data.customPromptS != '1') {
                data.title           = $("[name='yaiaMetabox[options][title]']").val() || postTitle;
                data.language        = $("[name='yaiaMetabox[options][language]']").val();
                data.paragraphsCount = $("[name='yaiaMetabox[options][paragraphsCount]']").val();
                data.addHeadings     = $("[name='yaiaMetabox[options][addHeadings]']").val();
                data.writingStyle    = $("[name='yaiaMetabox[options][writingStyle]']").val();
                data.addIntroduction = $("[name='yaiaMetabox[options][addIntroduction]']").val();
                data.addConclusion   = $("[name='yaiaMetabox[options][addConclusion]']").val();
            }

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: YAIA.apiUrl,
                data,
                beforeSend() {
                    waitingPopup(YAIA.lang.contentCreationPleaseWait);
                },
                success(response) {
                    if (response.success) {
                        let content = response.data;
                        content = content.replaceAll('<?php', '&lt;?php');
                        content = content.replaceAll('?>', '?&gt;');
                        content = content.replace('\n\n', '');
                        if (tinymce && tinymce.activeEditor) {
                            let old = tinymce.activeEditor.getContent();
                            old = old != '' ? old + '<br>' : '';
                            tinymce.activeEditor.setContent(old + content);
                        } 
                        
                        if ($(".woocommerce-product-description").length > 0) {
                            $(".woocommerce-product-description #content-html").click();
                            let old = $(".woocommerce-product-description .wp-editor-area").val();
                            old = old != '' ? old + '<br>' : '';
                            $(".woocommerce-product-description .wp-editor-area").val(old + content);
                        } 
                        
                        if (checkWpGutengergEditor()) {
                            wp.data.dispatch("core/editor").insertBlocks(wp.blocks.createBlock("core/freeform", {
                                content: content,
                            }));
                        }
                        
                        successPopup(response.message).then(() => {
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        });
                    } else {
                        errorsPopup(response);
                    }
                },
                error() {
                    errorPopup(YAIA.lang.somethingWentWrong);
                }
            });
        });

        $(document).on('click', '#yaiaImageGenerator', function(e) {
            e.preventDefault();

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaImageGenerator',
            };

            data.prompt     = $("#prompt").val();
            data.imageCount = $("#imageCount").val();
            data.imageSizes = $("#imageSizes").val();

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: YAIA.apiUrl,
                data,
                beforeSend() {
                    waitingPopup(YAIA.lang.imagesCreationPleaseWait);
                },
                success(response) {
                    if (response.success) {
                        let images = response.data;

                        $(".save-all-button").html(`
                            <div class="save-all button button-primary">${YAIA.lang.saveAll}</div>
                        `);

                        $(".image-list").html("");
                        images.forEach((image, index) => {
                            $(".image-list").append(`
                                <li class="image-item" style="display: inline-flex; flex-direction: column">
                                    <img id="image-${index}" src="${image.url}" alt="${data.prompt}" style="margin-bottom: 10px">
                                    <div class="save-image button button-primary" style="text-align:center">${YAIA.lang.saveImage}</div>
                                </li>
                            `);
                        });

                        successPopup(response.message).then(() => {
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                        });
                    } else {
                        errorsPopup(response);
                    }
                },
                error() {
                    errorPopup(YAIA.lang.somethingWentWrong);
                }
            });
        });

        $(document).on('click', '.save-all', async function(e) {
            e.preventDefault();

            let images = [];
            $(".image-list li img").each((i, img) => images.push($(img).attr("src")));

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaSaveImages',
                images,
            };

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: YAIA.apiUrl,
                data,
                beforeSend() {
                    waitingPopup(YAIA.lang.imagesSaveProcess);
                },
                success(response) {
                    if (response.success) {
                        successPopup(response.message);
                    } else {
                        errorPopup(response.message);
                    }
                },
                error() {
                    errorPopup(YAIA.lang.somethingWentWrong);
                }
            });
        });
        
        $(document).on('click', '.save-image', async function(e) {
            e.preventDefault();

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaSaveImage',
                image  : $(this).parent().find("img").attr("src"),
            };

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: YAIA.apiUrl,
                data,
                beforeSend() {
                    waitingPopup(YAIA.lang.imageSaveProcess);
                },
                success(response) {
                    if (response.success) {
                        successPopup(response.message);
                    } else {
                        errorPopup(response.message);
                    }
                },
                error() {
                    errorPopup(YAIA.lang.somethingWentWrong);
                }
            });
        });

        $(document).on('click', '#yaiaTextEditor', async function(e) {
            e.preventDefault();

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaTextEditor'
            };

            data.input       = $("#input").val();
            data.instruction = $("#instruction").val();

            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: YAIA.apiUrl,
                data,
                beforeSend() {
                    waitingPopup(YAIA.lang.commandRunning);
                },
                success(response) {
                    if (response.success) {
                        let content = response.data;
                        content = content.replaceAll('<?php', '&lt;?php');
                        content = content.replaceAll('?>', '?&gt;');
                        content = content.replace('\n\n', '');
                        $("#output").html(content);
                        successPopup(response.message);
                    } else {
                        errorsPopup(response);
                    }
                },
                error() {
                    errorPopup(YAIA.lang.somethingWentWrong);
                }
            });
        });
    });

})(jQuery);