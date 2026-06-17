<?php
$conversationInfo = $conversationInfo ?? null;
$currentName = $conversationInfo['customer_name'] ?? 'Chọn khách hàng';
$currentEmail = $conversationInfo['customer_email'] ?? '';
?>
<div class="admin-title gb-chat-title">
  <div>
    <h1>Chat khách hàng</h1>
    <p>Chọn đúng tên khách ở bên trái để xem riêng từng hội thoại AI/Admin.</p>
  </div>
</div>

<div class="gb-admin-chat-wrap gb-admin-chat-pro">
  <aside class="gb-chat-list admin-card">
    <div class="gb-chat-list-head">
      <h3>Hội thoại</h3>
      <small><?= count($conversations ?? []) ?> khách</small>
    </div>

    <?php if(empty($conversations)): ?>
      <div class="gb-chat-empty-mini">Chưa có khách nào nhắn.</div>
    <?php endif; ?>

    <?php foreach($conversations as $c):
      $name = trim((string)($c['customer_name'] ?? ''));
      if ($name === '') $name = 'Khách chưa đăng nhập';
      $initial = mb_substr($name, 0, 1, 'UTF-8');
    ?>
      <a class="gb-conv-item <?= ($conv==$c['conv_key']?'active':'') ?>" href="<?= BASE_URL ?>admin/chats?conv=<?= urlencode($c['conv_key']) ?>">
        <span class="gb-conv-avatar"><?= htmlspecialchars($initial) ?></span>
        <span class="gb-conv-main">
          <b><?= htmlspecialchars($name) ?></b>
          <?php if(!empty($c['customer_email'])): ?><small><?= htmlspecialchars($c['customer_email']) ?></small><?php endif; ?>
          <?php if(!empty($c['last_message'])):
            $lastSender = $c['last_sender'] ?? 'customer';
            $prefix = $lastSender === 'admin' ? 'Admin: ' : ($lastSender === 'ai' ? 'AI: ' : 'Khách: ');
          ?>
            <em><?= htmlspecialchars($prefix . mb_strimwidth($c['last_message'], 0, 42, '...', 'UTF-8')) ?></em>
          <?php endif; ?>
        </span>
        <span class="gb-conv-meta">
          <small><?= htmlspecialchars($c['latest_time']) ?></small>
          <?php if((int)$c['unread']>0): ?><i><?= (int)$c['unread'] ?></i><?php endif; ?>
        </span>
      </a>
    <?php endforeach; ?>
  </aside>

  <section class="gb-chat-panel admin-card">
    <div class="gb-chat-panel-head">
      <div>
        <b><?= htmlspecialchars($currentName) ?></b>
        <?php if($currentEmail): ?><span><?= htmlspecialchars($currentEmail) ?></span><?php endif; ?>
      </div>
      <?php if($conv!==''): ?>
        <div class="gb-chat-head-actions">
          <small>Hội thoại riêng</small>
          <form method="post" action="<?= BASE_URL ?>admin/chat-delete" onsubmit="return confirm('Xoá toàn bộ cuộc trò chuyện này?');">
            <input type="hidden" name="conv" value="<?= htmlspecialchars($conv) ?>">
            <button type="submit" class="gb-chat-delete-btn">🗑️ Xoá trò chuyện</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <div class="gb-chat-messages">
      <?php if(empty($messages)): ?>
        <div class="gb-chat-empty">
          <div class="gb-chat-empty-icon">💬</div>
          <h3>Chưa có cuộc trò chuyện nào</h3>
          <p>Khi khách nhắn với AI Chatbox, từng hội thoại sẽ tự động tách riêng theo tài khoản khách.</p>
        </div>
      <?php endif; ?>

      <?php foreach($messages as $m):
        $sender = $m['sender'] ?? '';
        $label = 'Khách hàng';
        if ($sender === 'customer') $label = $m['display_name'] ?: ($m['customer_name'] ?? 'Khách hàng');
        if ($sender === 'ai') $label = 'AI GlowBeauty';
        if ($sender === 'admin') $label = 'Admin GlowBeauty';
      ?>
        <div class="gb-chat-msg <?= htmlspecialchars($sender) ?>">
          <b><?= htmlspecialchars($label) ?></b>
          <p><?= nl2br(htmlspecialchars($m['message'])) ?></p>
          <span><?= htmlspecialchars($m['created_at']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if($conv!==''): ?>
    <form method="post" action="<?= BASE_URL ?>admin/chat-reply" class="gb-admin-chat-form">
      <input type="hidden" name="conv" value="<?= htmlspecialchars($conv) ?>">
      <textarea name="message" required placeholder="Nhập câu trả lời riêng cho <?= htmlspecialchars($currentName) ?>..."></textarea>
      <button class="btn" type="submit">➤</button>
    </form>
    <?php endif; ?>
  </section>
</div>

<style>
.gb-admin-chat-pro{display:grid;grid-template-columns:340px 1fr;gap:18px}
.gb-chat-list{padding:16px;max-height:680px;overflow:auto}
.gb-chat-list-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.gb-chat-list-head h3{margin:0}
.gb-conv-item{display:grid;grid-template-columns:46px 1fr auto;gap:10px;align-items:center;padding:12px;border:1px solid #f0d2c2;border-radius:16px;margin-bottom:10px;text-decoration:none;color:#3b1d13;background:#fff7f2}
.gb-conv-item.active{background:#ffe8dc;border-color:#df7b45;box-shadow:0 8px 24px rgba(164,84,41,.12)}
.gb-conv-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#e87544,#f7b985);color:#fff;display:flex!important;align-items:center!important;justify-content:center!important;font-weight:800;font-size:18px;line-height:1;overflow:hidden}
.gb-conv-main{min-width:0}
.gb-conv-main b,.gb-conv-main small,.gb-conv-main em{display:block}
.gb-conv-main b{font-size:15px}
.gb-conv-main small{color:#9a6a55;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gb-conv-main em{font-style:normal;color:#7a5649;font-size:12px;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gb-conv-meta{text-align:right}
.gb-conv-meta small{display:block;color:#9a6a55;font-size:11px}
.gb-conv-meta i{display:inline-grid;place-items:center;min-width:22px;height:22px;border-radius:99px;background:#ef3f68;color:white;font-style:normal;font-size:12px;margin-top:5px}
.gb-chat-panel{padding:16px;display:flex;flex-direction:column;min-height:680px;max-height:760px}
.gb-chat-panel-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border:1px solid #f0d2c2;border-radius:16px;background:#fff7f2;margin-bottom:12px}
.gb-chat-panel-head b,.gb-chat-panel-head span{display:block}
.gb-chat-panel-head span{color:#9a6a55;font-size:13px}
.gb-chat-head-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.gb-chat-head-actions form{margin:0}
.gb-chat-panel-head small{color:#0f8f55;background:#dcfce7;border:1px solid #86efac;padding:6px 10px;border-radius:99px}
.gb-chat-delete-btn{height:34px;border:1px solid #f1b8b8;background:#fff1f1;color:#c72b2b;border-radius:99px;padding:0 12px;font-weight:800;cursor:pointer;white-space:nowrap}
.gb-chat-delete-btn:hover{background:#ffe4e4}
.gb-chat-messages{height:520px;overflow-y:auto;padding:12px;border:1px solid #f0d2c2;border-radius:18px;background:#fffaf7;scroll-behavior:smooth}
.gb-chat-msg{max-width:72%;padding:12px 14px;border-radius:16px;margin:0 0 10px;border:1px solid #f0d2c2;background:white}
.gb-chat-msg.customer{margin-right:auto}
.gb-chat-msg.ai,.gb-chat-msg.admin{margin-left:auto;background:#fff0e7}
.gb-chat-msg b{display:block;margin-bottom:5px}
.gb-chat-msg p{margin:0;line-height:1.55}
.gb-chat-msg span{display:block;margin-top:8px;color:#9a6a55;font-size:12px}
.gb-admin-chat-form{display:grid;grid-template-columns:1fr 54px;gap:10px;margin-top:12px}
.gb-admin-chat-form textarea{min-height:54px;resize:vertical;border:1px solid #f0d2c2;border-radius:16px;padding:14px}
.gb-admin-chat-form button{border-radius:16px;font-size:20px}
.gb-chat-empty-mini{padding:18px;border:1px dashed #e8c8b8;border-radius:14px;color:#8a5a49;text-align:center}
@media(max-width:768px){
  .gb-admin-chat-pro{grid-template-columns:1fr;gap:12px}
  .gb-chat-list{display:flex;gap:10px;overflow:auto;max-height:none;padding:10px}
  .gb-chat-list-head{display:none}
  .gb-conv-item{min-width:190px;grid-template-columns:38px 1fr;padding:10px}
  .gb-conv-avatar{width:36px;height:36px;font-size:16px}
  .gb-conv-meta{display:none}
  .gb-chat-panel{min-height:540px;max-height:none;padding:10px}
  .gb-chat-messages{height:430px}.gb-chat-msg{max-width:88%}
  .gb-admin-chat-form{grid-template-columns:1fr 48px}
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function(){
    var chatBox = document.querySelector('.gb-chat-messages');
    if(chatBox){
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>
