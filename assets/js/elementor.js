(($) => {
    "use strict";

    $(document).ready(() => {

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

        elementor.channels.editor.on('BeycanPress:YAIA:Create', (event) => {
            
            let elementor = event.$el.closest("#elementor-panel-inner");
            let items = event.$el.closest("#elementor-controls");
            let prompt = items.find('[data-setting="prompt"]');

            let data = {
                nonce  : YAIA.nonce,
                action : 'yaiaCreateContent',
            };

            data.customPrompt  = prompt.val();
            data.customPromptS = true;

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
                        let content = response.data.replace('\n\n', '');
                        items.find('[data-setting="content"]').val(content);
                        event.options.container.settings.attributes.content = content;
                        elementor.find("#elementor-panel-saver-button-publish").removeClass("elementor-disabled");
                        elementor.find("#elementor-panel-saver-button-save-options").removeClass("elementor-disabled");

                        let root = document.querySelector(`div[data-id="${event.options.container.id}"]`);
                        this.contentWrapper =  root.querySelector(`.elementor-widget-container`);
                        if (root.querySelector(`.elementor-widget-empty-icon`)) {
                            root.querySelector(`.elementor-widget-empty-icon`).remove();
                        }
                        root.classList.remove('elementor-widget-empty');
                        this.contentWrapper.innerText = content;
                        
						Swal.close();
                        alert(response.message);
                    } else {
						Swal.close();
						if (Array.isArray(response.data)) {
							alert(response.data.join("\r\n"));
						} else {
							alert(response.message);
						}
                    }
                },
                error() {
					Swal.close();
                    alert(YAIA.lang.somethingWentWrong);
                }
            });
        });
    });

})(jQuery);