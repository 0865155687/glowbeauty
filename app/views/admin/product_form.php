<div class="admin-title">
    <div>
        <h1><?= $p ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
        <p class="admin-sub">
            Điền đầy đủ tên, thương hiệu, công dụng, giá bán, tồn kho và ảnh sản phẩm để hiển thị đúng trên website.
        </p>
    </div>

    <a class="btn admin-secondary-v34 admin-link-fix" href="<?= BASE_URL ?>admin/products">← Quay lại</a>
</div>

<form method="post" class="admin-form">
    <input type="hidden" name="id" value="<?= $p['id'] ?? '' ?>">

    <div class="form-grid">

        <div class="field">
            <label>Mã sản phẩm</label>
            <input
                name="product_code"
                required
                value="<?= htmlspecialchars($p['product_code'] ?? ('GB-' . date('ymd') . '-' . rand(100,999))) ?>"
                placeholder="VD: GB-250521-001"
            >
            <div class="helper">Dùng để quản lý kho, in bill và tìm kiếm sản phẩm nhanh hơn.</div>
        </div>

        <div class="field">
            <label>Tên sản phẩm</label>
            <input
                name="name"
                required
                value="<?= htmlspecialchars($p['name'] ?? '') ?>"
                placeholder="VD: GlowBeauty 4 Colors Eyeshadow"
            >
            <div class="helper">Tên sẽ hiển thị trên card sản phẩm và trang chi tiết.</div>
        </div>

        <div class="field">
            <label>Thương hiệu</label>
            <input
                name="brand"
                required
                value="<?= htmlspecialchars($p['brand'] ?? 'GlowBeauty') ?>"
                placeholder="GlowBeauty / Merzy / CeraVe..."
            >
        </div>

        <div class="field">
            <label>Danh mục sản phẩm</label>
            <input
                name="category"
                required
                list="category-list"
                value="<?= htmlspecialchars($p['category'] ?? '') ?>"
                placeholder="Gõ để tìm hoặc nhập danh mục mới"
            >

            <datalist id="category-list">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>">
                <?php endforeach; ?>
            </datalist>

            <div class="helper">
                Có thể gõ để tìm danh mục có sẵn hoặc tự nhập danh mục mới nếu chưa có trong danh sách.
            </div>
        </div>

        <div class="field">
            <label>Giá bán VNĐ</label>
            <input
                type="number"
                name="price"
                required
                value="<?= htmlspecialchars($p['price'] ?? 0) ?>"
                placeholder="199000"
            >
            <div class="helper">Đơn vị: VNĐ.</div>
        </div>

        <div class="field">
            <label>Số lượng tồn kho</label>
            <input
                type="number"
                name="stock"
                required
                value="<?= htmlspecialchars($p['stock'] ?? 10) ?>"
            >
            <div class="helper">Số lượng hiện có trong kho.</div>
        </div>

        <div class="field">
            <label>File ảnh sản phẩm</label>
            <input
                name="image"
                required
                value="<?= htmlspecialchars($p['image'] ?? '') ?>"
                placeholder="ten-anh.jpg"
            >
            <div class="helper">Nhập đúng tên file ảnh sản phẩm.</div>

            <?php if (!empty($p['image'])): ?>
                <img
                    src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars($p['image']) ?>"
                    alt="preview"
                >
            <?php endif; ?>
        </div>

        <div class="field full">
            <label>Mô tả sản phẩm</label>
            <textarea
                name="description"
                required
                placeholder="Mô tả ngắn về sản phẩm, thương hiệu, dung tích, phong cách..."
            ><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
        </div>

        <div class="field full">
            <label>Công dụng</label>
            <textarea
                name="benefit"
                required
                placeholder="VD: Làm sạch dầu thừa, cấp ẩm, che khuyết điểm, lên màu chuẩn..."
            ><?= htmlspecialchars($p['benefit'] ?? '') ?></textarea>
        </div>

        <div class="field full">
            <label>Thành phần / điểm nổi bật</label>
            <textarea
                name="ingredients"
                required
                placeholder="VD: Niacinamide, Hyaluronic Acid, kết cấu lì mịn, chống trôi..."
            ><?= htmlspecialchars($p['ingredients'] ?? '') ?></textarea>
        </div>

        <div class="field full">
            <label>Hướng dẫn sử dụng</label>
            <textarea
                name="usage_text"
                required
                placeholder="Cách dùng phù hợp với sản phẩm."
            ><?= htmlspecialchars($p['usage_text'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label>Trạng thái hiển thị</label>
            <select name="status">
                <option value="1" <?= ($p['status'] ?? 1) == 1 ? 'selected' : '' ?>>
                    Hiển thị trên website
                </option>
                <option value="0" <?= ($p['status'] ?? 1) == 0 ? 'selected' : '' ?>>
                    Tạm ẩn
                </option>
            </select>
        </div>
    </div>

    <button class="btn">Lưu sản phẩm</button>
</form>
