<?php

define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'includes/admin_auth.php';

$id   = (int)($_GET['id'] ?? 0);
$conn = get_db_connection();

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    set_flash('error', 'Product not found.');
    header("Location: products.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) die("Invalid request.");

    $name        = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $category    = sanitize_input($_POST['category'] ?? '');
    $image_path  = $product['image_path'];

    if (!empty($_FILES['image']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $error = "Image must be JPG, PNG or WEBP.";
        } elseif ((int)($_FILES['image']['size'] ?? 0) > $max_size) {
            $error = "Image is too large. Max size is 5MB.";
        } elseif (@getimagesize($_FILES['image']['tmp_name']) === false) {
            $error = "The uploaded file is not a valid image.";
        } else {
            $filename = uniqid('prod_', true) . '.' . $ext;
            $target   = ROOT_PATH . 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                if ($product['image_path'] && file_exists(ROOT_PATH . $product['image_path'])) {
                    unlink(ROOT_PATH . $product['image_path']);
                }
                $image_path = 'uploads/' . $filename;
            }
        }
    }

    if (empty($name) || $price <= 0) {
        $error = "Product name and a valid price are required.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE products SET name=?, description=?, price=?, stock=?, image_path=?, category=? WHERE id=?"
        );
        $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $image_path, $category, $id);
        if ($stmt->execute()) {
            set_flash('success', "Product updated.");
            header("Location: products.php");
            exit();
        }
        $error = "Update failed.";
    }
}

$page_title = 'Edit Product — Glow Co. Admin';
include ROOT_PATH . 'includes/admin_header.php';
?>

<div class="admin-main">
  <h1>Edit Product</h1>
  <a href="<?= BASE_URL ?>admin/products.php" style="font-size:.85rem;color:var(--text-soft);display:inline-block;margin-bottom:24px;">← Back to products</a>

  <?php if ($error): ?>
    <div class="flash flash--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="admin-form">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

      <div>
        <label>Product Name *</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
      </div>

      <div>
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
      </div>

      <div class="form-row-2">
        <div>
          <label>Price (₦) *</label>
          <input type="number" name="price" value="<?= $product['price'] ?>" step="0.01" min="0" required>
        </div>
        <div>
          <label>Stock Quantity *</label>
          <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required>
        </div>
      </div>

      <div>
        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category'] ?? '') ?>">
      </div>

      <div>
        <label>Product Image</label>
        <?php if ($product['image_path']): ?>
          <div style="margin-bottom:12px;">
            <img src="<?= BASE_URL . htmlspecialchars($product['image_path']) ?>"
                 alt="Current image"
                 style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--pink-soft);">
            <p style="font-size:.75rem;color:var(--text-soft);margin-top:4px;">Current image. Upload a new one to replace it.</p>
          </div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn-primary" style="flex:1;">Save Changes</button>
        <a href="products.php" class="btn-primary"
           style="flex:1;text-align:center;background:transparent;border:1.5px solid var(--pink);color:var(--plum);">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<?php include ROOT_PATH . 'includes/admin_footer.php'; ?>