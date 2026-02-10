<?php
function start_session_if_needed()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token()
{
    start_session_if_needed();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf()
{
    start_session_if_needed();
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo "CSRF token invalide.";
        exit;
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function sanitize_filename($name)
{
    $base = basename($name);
    $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
    return $sanitized ?: 'file';
}

function ensure_upload_dir($dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function validate_upload_or_throw($file, array $allowedMime, $maxBytes)
{
    if (empty($file) || !isset($file['tmp_name'])) {
        throw new Exception("Fichier manquant.");
    }
    if (!empty($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors de l'upload.");
    }
    if (!empty($file['size']) && $file['size'] > $maxBytes) {
        throw new Exception("Fichier trop volumineux.");
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        throw new Exception("Type de fichier non autorisé.");
    }
}

function render_pagination($currentPage, $totalPages, $baseParams = [])
{
    if ($totalPages <= 1) {
        return '';
    }

    $currentPage = max(1, (int)$currentPage);
    $totalPages = max(1, (int)$totalPages);

    $buildLink = function ($page) use ($baseParams) {
        $params = $baseParams;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    };

    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';

    $prev = max(1, $currentPage - 1);
    $next = min($totalPages, $currentPage + 1);

    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $buildLink($prev) . '">«</a></li>';

    $pages = [];
    $pages[] = 1;
    if ($currentPage - 1 > 1) $pages[] = $currentPage - 1;
    if ($currentPage !== 1 && $currentPage !== $totalPages) $pages[] = $currentPage;
    if ($currentPage + 1 < $totalPages) $pages[] = $currentPage + 1;
    if ($totalPages > 1) $pages[] = $totalPages;
    $pages = array_values(array_unique($pages));
    sort($pages);

    $last = 0;
    foreach ($pages as $p) {
        if ($last && $p > $last + 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        $active = $p === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $buildLink($p) . '">' . $p . '</a></li>';
        $last = $p;
    }

    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $buildLink($next) . '">»</a></li>';

    $html .= '</ul></nav>';
    return $html;
}
