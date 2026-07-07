<?php
// --- admin/add_product.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $name        = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category    = sanitize_input($_POST['category'] ?? '');
    $image_path  = null;

    if (!empty($_FILES['image']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('prod_', true) . '.' . $ext;
            $target   = ROOT_PATH . 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_path = 'uploads/' . $filename;
            }
        }
    }

    if (empty($name) || $price <= 0) {
        $error = "Product name and a valid price are required.";
    } else {
        $conn = get_db_connection();
        $stmt = $conn->prepare(
            "INSERT INTO products (name, description, price, stock, image_path, category) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $image_path, $category);
        if ($stmt->execute()) {
            set_flash('success', "Product '{$name}' added successfully.");
            header("Location: products.php");
            exit();
        }
        $error = "Failed to add product. Try again.";
    }
}

$page_title = 'Add Product — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Add Product</h1>
  <a href="<?= BASE_URL ?>admin/products.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to products</a>

  <?php if ($error): ?>
    <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="admin-form">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div>
        <label>Product Name *</label>
        <input type="text" name="name" placeholder="e.g. Shea Butter Body Cream" required>
      </div>

      <div>
        <label>Description</label>
        <textarea name="description" placeholder="Describe the product, key ingredients, benefits..."></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
          <label>Price (₦) *</label>
          <input type="number" name="price" placeholder="0.00" step="0.01" min="0" required>
        </div>
        <div>
          <label>Stock Quantity *</label>
          <input type="number" name="stock" placeholder="0" min="0" required>
        </div>
      </div>

      <div>
        <label>Category</label>
        <input type="text" name="category" placeholder="e.g. Body Lotion, Perfume, Cream">
      </div>

      <div>
        <label>Product Image</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <p style="font-size:.75rem;color:var(--text-soft);margin-top:4px;">JPG, PNG or WEBP. Max recommended: 2MB.</p>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Add Product</button>
    </form>
  </div>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>