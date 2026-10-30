<?php
/**
 * Plugin Name: WordPress Telegram Yorum Bildirimi
 * Description: Yeni WordPress yorumlarını Telegram üzerinden yöneticinize bildirir.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: wordpress-telegram-yorum-eklentisi
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPTCN_OPTION_NAME', 'wptcn_settings');

add_action('admin_menu', 'wptcn_add_settings_page');
add_action('admin_init', 'wptcn_register_settings');
add_action('comment_post', 'wptcn_notify_admin_of_comment', 10, 2);

function wptcn_get_settings()
{
    $settings = get_option(WPTCN_OPTION_NAME, array());

    return is_array($settings) ? $settings : array();
}

function wptcn_add_settings_page()
{
    add_options_page(
        __('Telegram yorum bildirimi', 'wordpress-telegram-yorum-eklentisi'),
        __('Telegram yorum bildirimi', 'wordpress-telegram-yorum-eklentisi'),
        'manage_options',
        'wptcn-settings',
        'wptcn_render_settings_page'
    );
}

function wptcn_register_settings()
{
    register_setting(
        'wptcn_settings_group',
        WPTCN_OPTION_NAME,
        array('sanitize_callback' => 'wptcn_sanitize_settings')
    );

    add_settings_section(
        'wptcn_telegram_section',
        __('Telegram bot ayarları', 'wordpress-telegram-yorum-eklentisi'),
        'wptcn_render_settings_section',
        'wptcn-settings'
    );

    add_settings_field(
        'wptcn_bot_token',
        __('Telegram bot tokenı', 'wordpress-telegram-yorum-eklentisi'),
        'wptcn_render_bot_token_field',
        'wptcn-settings',
        'wptcn_telegram_section',
        array('label_for' => 'wptcn-bot-token')
    );

    add_settings_field(
        'wptcn_chat_id',
        __('Telegram sohbet kimliği (Chat ID)', 'wordpress-telegram-yorum-eklentisi'),
        'wptcn_render_chat_id_field',
        'wptcn-settings',
        'wptcn_telegram_section',
        array('label_for' => 'wptcn-chat-id')
    );
}

function wptcn_render_settings_section()
{
    echo '<p>' . esc_html__(
        'Yeni yorum bildirimlerinin gönderileceği Telegram botunu ve sohbeti yapılandırın.',
        'wordpress-telegram-yorum-eklentisi'
    ) . '</p>';
}

function wptcn_render_bot_token_field()
{
    $settings = wptcn_get_settings();
    $has_token = !empty($settings['bot_token']);
    ?>
    <input
        type="password"
        id="wptcn-bot-token"
        name="<?php echo esc_attr(WPTCN_OPTION_NAME); ?>[bot_token]"
        value=""
        class="regular-text"
        autocomplete="new-password"
        aria-describedby="wptcn-bot-token-description"
        placeholder="<?php echo esc_attr($has_token ? __('Kayıtlı token korunuyor', 'wordpress-telegram-yorum-eklentisi') : ''); ?>"
    />
    <p class="description" id="wptcn-bot-token-description">
        <?php esc_html_e('Bot tokenını girin. Kayıtlı tokenı değiştirmeyecekseniz bu alanı boş bırakın.', 'wordpress-telegram-yorum-eklentisi'); ?>
        <?php if ($has_token) : ?>
            <span class="screen-reader-text"><?php esc_html_e('Bir bot tokenı kayıtlı.', 'wordpress-telegram-yorum-eklentisi'); ?></span>
        <?php endif; ?>
    </p>
    <?php
}

function wptcn_render_chat_id_field()
{
    $settings = wptcn_get_settings();
    $chat_id = isset($settings['chat_id']) && is_string($settings['chat_id'])
        ? $settings['chat_id']
        : '';
    ?>
    <input
        type="text"
        id="wptcn-chat-id"
        name="<?php echo esc_attr(WPTCN_OPTION_NAME); ?>[chat_id]"
        value="<?php echo esc_attr($chat_id); ?>"
        class="regular-text"
        autocomplete="off"
        aria-describedby="wptcn-chat-id-description"
    />
    <p class="description" id="wptcn-chat-id-description">
        <?php esc_html_e('Sayısal sohbet kimliğini veya @ ile başlayan kanal kullanıcı adını girin.', 'wordpress-telegram-yorum-eklentisi'); ?>
    </p>
    <?php
}

function wptcn_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Telegram yorum bildirimi', 'wordpress-telegram-yorum-eklentisi'); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wptcn_settings_group');
            do_settings_sections('wptcn-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wptcn_sanitize_settings($input)
{
    $settings = wptcn_get_settings();

    if (!is_array($input)) {
        add_settings_error(
            WPTCN_OPTION_NAME,
            'invalid_settings',
            __('Ayarlar geçerli biçimde gönderilmedi.', 'wordpress-telegram-yorum-eklentisi')
        );

        return $settings;
    }

    if (isset($input['bot_token']) && is_string($input['bot_token'])) {
        $token = trim(sanitize_text_field($input['bot_token']));
        if ($token !== '' && preg_match('/^\d+:[A-Za-z0-9_-]+$/D', $token)) {
            $settings['bot_token'] = $token;
        } elseif ($token !== '') {
            add_settings_error(
                WPTCN_OPTION_NAME,
                'invalid_bot_token',
                __('Bot tokenı geçerli görünmüyor; önceki değer korundu.', 'wordpress-telegram-yorum-eklentisi')
            );
        }
    }

    if (isset($input['chat_id']) && is_string($input['chat_id'])) {
        $chat_id = trim(sanitize_text_field($input['chat_id']));
        if (
            $chat_id === ''
            || preg_match('/^-?\d+$/D', $chat_id)
            || preg_match('/^@[A-Za-z0-9_]{5,}$/D', $chat_id)
        ) {
            $settings['chat_id'] = $chat_id;
        } else {
            add_settings_error(
                WPTCN_OPTION_NAME,
                'invalid_chat_id',
                __('Sohbet kimliği geçerli değil; önceki değer korundu.', 'wordpress-telegram-yorum-eklentisi')
            );
        }
    }

    return $settings;
}

function wptcn_notify_admin_of_comment($comment_id, $comment_approved = null)
{
    $settings = wptcn_get_settings();
    if (empty($settings['bot_token']) || empty($settings['chat_id'])) {
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment || in_array(wp_get_comment_status($comment_id), array('spam', 'trash'), true)) {
        return;
    }

    $status = wp_get_comment_status($comment_id) === 'approved'
        ? __('Onaylandı', 'wordpress-telegram-yorum-eklentisi')
        : __('Onay bekliyor', 'wordpress-telegram-yorum-eklentisi');
    $review_url = get_edit_comment_link($comment_id);
    $message = implode("\n", array(
        sprintf(
            __('%s sitesine yeni bir yorum yapıldı.', 'wordpress-telegram-yorum-eklentisi'),
            sanitize_text_field(get_bloginfo('name'))
        ),
        sprintf(
            __('Yazar: %s', 'wordpress-telegram-yorum-eklentisi'),
            sanitize_text_field($comment->comment_author)
        ),
        sprintf(
            __('Yazı: %s', 'wordpress-telegram-yorum-eklentisi'),
            sanitize_text_field(get_the_title($comment->comment_post_ID))
        ),
        sprintf(__('Durum: %s', 'wordpress-telegram-yorum-eklentisi'), $status),
        sprintf(__('Yorumu incele: %s', 'wordpress-telegram-yorum-eklentisi'), esc_url_raw($review_url))
    ));

    wp_remote_post(
        'https://api.telegram.org/bot' . $settings['bot_token'] . '/sendMessage',
        array(
            'timeout' => 5,
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => wp_json_encode(array(
                'chat_id' => $settings['chat_id'],
                'text' => $message,
            )),
        )
    );
}
