<?php
return [
    'admin_email' => 'nn9499008@gmail.com',

    // Brevo API - dùng HTTPS, không dùng SMTP nên tránh lỗi AwardSpace chặn cổng 465/587.
    // Lấy tại Brevo: Settings -> SMTP & API -> API keys & MCP -> Generate API key.
    // LƯU Ý: API key thường bắt đầu bằng xkeysib-. Không dán SMTP key xsmtpsib- vào đây.
    'brevo_api_key' => 'PASTE_BREVO_API_KEY_HERE',
    'brevo_api_url' => 'https://api.brevo.com/v3/smtp/email',

    // Giữ lại thông tin SMTP chỉ để dự phòng, code hiện ưu tiên Brevo API.
    'smtp_host' => 'smtp-relay.brevo.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_username' => 'aed627001@smtp-brevo.com',
    'smtp_password' => '',

    'from_email' => 'info@glowbeauty.id.vn',
    'from_name' => 'GlowBeauty Website',
    'fallback_from_email' => 'info@glowbeauty.id.vn',
    'reply_to' => 'nn9499008@gmail.com',
];
