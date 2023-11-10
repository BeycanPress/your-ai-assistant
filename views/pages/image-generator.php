<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Image generator', 'yaia'); ?>
    </h1>
    <hr class="wp-header-end">
    <br>
    <div class="save-all-button"></div>
    <ul class="image-list">
    </ul>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="prompt"><?php echo esc_html__('Prompt', 'yaia') ?></label>
                </th>
                <td>
                    <input name="prompt" type="text" id="prompt" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="imageCount"><?php echo esc_html__('Image count', 'yaia') ?></label>
                </th>
                <td>
                    <input name="imageCount" type="number" id="imageCount" class="regular-text" value="1" min="1">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="imageSizes"><?php echo esc_html__('Image sizes', 'yaia') ?></label>
                </th>
                <td>
                    <select name="imageSizes" id="imageSizes">
                        <option value="256x256">256x256</option>
                        <option value="512x512">512x512</option>
                        <option value="1024x1024">1024x1024</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="yaiaImageGenerator"><?php echo esc_html__('Create', 'yaia') ?></label>
                </th>
                <td>
                    <button id="yaiaImageGenerator" name="yaiaImageGenerator" value="create" type="submit" class="button button-primary">
                        <?php echo esc_html__('Create', 'yaia'); ?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>