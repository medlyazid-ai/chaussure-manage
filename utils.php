<?php
function start_session_if_needed() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
