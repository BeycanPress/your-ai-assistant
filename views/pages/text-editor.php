<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Text editor (fixer)', 'yaia'); ?>
    </h1>
    <hr class="wp-header-end">
    <br>
    <div class="general">
        <div class="left">
            <label for="input"><?php echo esc_html__('Input:', 'yaia') ?></label>
            <textarea name="input" id="input" cols="30" rows="10"></textarea>
        </div>
        <div class="right">
            <label for="input"><?php echo esc_html__('Output:', 'yaia') ?></label>
            <textarea name="output" id="output" cols="30" rows="10"></textarea>
        </div>
    </div>
    <div class="desc">
        <?php echo esc_html__('What kind of change do you want to make on the text you entered in the input field above? For example, you could say "Fix the spelling mistakes".', 'yaia') ?>
    </div>
    <div class="sub-space">
        <label for="instruction">
            <?php echo esc_html__('Prompt:  ', 'yaia') ?><input type="text" name="instruction" id="instruction">
        </label>
        <button id="yaiaTextEditor" name="yaiaTextEditor" type="submit" class="button button-primary">
            <?php echo esc_html__('Edit', 'yaia'); ?>
        </button>
    </div>
</div>