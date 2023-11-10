<div id="yaia-chat-gpt">
    <div id="chat-container">
        <div class="wrapper ai">
            <div class="chat">
                <div class="profile">
                    <img src="<?php echo esc_url($this->getImageUrl('bot.svg')) ?>" alt="bot">
                </div>
                <div class="message"><?php echo esc_html__('Hey, what can I do for you today?', 'yaia') ?></div>
            </div>
        </div>
    </div>
    <form>
        <textarea name="prompt" rows="1" cols="1" placeholder="<?php echo esc_html__('Ask me...', 'yaia') ?>"></textarea>
        <button type="submit"><img src="<?php echo esc_url($this->getImageUrl('send.svg')) ?>" alt="send" />
    </form>
</div>