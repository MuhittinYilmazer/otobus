<?php
// Helper functions used throughout the application


function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// kullanıcı belli bir role sahip mi kontrol et
function check_permission($allowed_roles = []) {
    if (!is_logged_in() || !in_array($_SESSION['role'], $allowed_roles)) {
        set_flash_message('Bu sayfaya erişim yetkiniz yok.', 'error');
        header('Location: index.php');
        exit;
    }
}

/**
 * Sets a flash message to be displayed on the next page load.
 * @param string $message The message text.
 * @param string $type 'success', 'error', 'info'.
 */
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = ['text' => $message, 'type' => $type];
}

/**
 * Displays and clears a flash message if one exists.
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $color_map = [
            'success' => 'green',
            'error' => 'red',
            'info' => 'blue'
        ];
        $color = $color_map[$message['type']] ?? 'blue';
        echo "<div class='p-4 mb-4 text-sm text-{$color}-700 bg-{$color}-100 rounded-lg' role='alert'>
                <span class='font-medium'>{$message['text']}</span>
              </div>";
        unset($_SESSION['flash_message']);
    }
}


function redirect($url) {
    header('Location: ' . $url);
    exit;
}

