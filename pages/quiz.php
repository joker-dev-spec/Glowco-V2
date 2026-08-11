<?php
// --- pages/quiz.php ---
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/config.php';
session_start();

$page_title = 'Skin Quiz — Glow Co.';

// Load real products so quiz recommendations link to actual shop items
$quiz_products = [];
$conn = get_db_connection();
$qp = $conn->query("SELECT id, name, price, image_path, description, category FROM products ORDER BY created_at DESC");
while ($r = $qp->fetch_assoc()) {
    $attrs = match ($r['category']) {
        'Perfume'   => ['skin' => ['oily', 'sensitive'], 'concern' => ['glow', 'even'], 'time' => ['night', 'whenever']],
        'Body Lotion' => ['skin' => ['dry', 'combo'], 'concern' => ['moisture', 'glow'], 'time' => ['morning', 'both']],
        default     => ['skin' => ['dry', 'sensitive'], 'concern' => ['moisture', 'calm'], 'time' => ['both', 'night']],
    };
    $quiz_products[] = $r + $attrs;
}

include ROOT_PATH . 'includes/header.php';
?>

<section class="quiz-page">
  <p class="section-eyebrow">Find your match</p>
  <h1>What does your<br><em>skin need?</em></h1>
  <p class="sub">Answer 3 quick questions and we'll recommend the perfect Glow Co. product for your skin.</p>

  <div class="quiz-progress" id="quizProgress">
    <span></span><span></span><span></span>
  </div>

  <div class="quiz-card">

    <!-- Step 1 -->
    <div class="quiz-step active" id="step-1">
      <p class="quiz-q">How does your skin usually feel?</p>
      <div class="quiz-options">
        <button class="quiz-opt" onclick="answer(1,'dry')">Tight and dry, especially after washing</button>
        <button class="quiz-opt" onclick="answer(1,'oily')">Shiny and oily by midday</button>
        <button class="quiz-opt" onclick="answer(1,'sensitive')">Easily irritated, red, or reactive</button>
        <button class="quiz-opt" onclick="answer(1,'combo')">Oily in some areas, dry in others</button>
      </div>
    </div>

    <!-- Step 2 -->
    <div class="quiz-step" id="step-2">
      <p class="quiz-q">What's your main skin concern?</p>
      <div class="quiz-options">
        <button class="quiz-opt" onclick="answer(2,'glow')">I want to glow and look radiant</button>
        <button class="quiz-opt" onclick="answer(2,'even')">Uneven skin tone or dark spots</button>
        <button class="quiz-opt" onclick="answer(2,'moisture')">Deep hydration and softness</button>
        <button class="quiz-opt" onclick="answer(2,'calm')">Calming redness or irritation</button>
      </div>
      <button class="quiz-back" onclick="goBack(2)">← Back</button>
    </div>

    <!-- Step 3 -->
    <div class="quiz-step" id="step-3">
      <p class="quiz-q">When do you usually moisturise?</p>
      <div class="quiz-options">
        <button class="quiz-opt" onclick="answer(3,'morning')">Morning, before I head out</button>
        <button class="quiz-opt" onclick="answer(3,'night')">At night before bed</button>
        <button class="quiz-opt" onclick="answer(3,'both')">Both morning and night</button>
        <button class="quiz-opt" onclick="answer(3,'whenever')">Whenever I remember</button>
      </div>
      <button class="quiz-back" onclick="goBack(3)">← Back</button>
    </div>

    <!-- Result -->
    <div class="quiz-step" id="step-result">
      <div class="quiz-result">
        <div style="font-size:3rem;margin-bottom:12px;" id="resultEmoji"></div>
        <h2 id="resultTitle">Your perfect match</h2>
        <p id="resultDesc"></p>
        <div id="resultProduct" style="margin:24px 0;background:var(--pink-soft);border-radius:16px;padding:20px;"></div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:8px;">
          <button class="btn-primary" id="resultCartBtn">View Product</button>
          <a href="<?= BASE_URL ?>pages/shop.php" class="btn-primary"
             style="background:transparent;border:1.5px solid var(--plum);color:var(--plum);">
            See All Products
          </a>
        </div>
        <button class="quiz-back" onclick="restartQuiz()" style="margin-top:20px;display:block;width:100%;text-align:center;">
          Retake quiz
        </button>
      </div>
    </div>

  </div>
</section>

<?php include ROOT_PATH . 'includes/footer.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const answers = {};
let currentStep = 1;

const PRODUCTS = <?= json_encode($quiz_products) ?>;

function getRecommendation() {
  const skin = answers[1], concern = answers[2], time = answers[3];
  let best = null, bestScore = -1;

  PRODUCTS.forEach(p => {
    let score = 0;
    if (p.skin.includes(skin))    score += 2;
    if (p.concern.includes(concern)) score += 2;
    if (p.time.includes(time))    score += 1;
    if (score > bestScore) { bestScore = score; best = p; }
  });

  return best;
}

function answer(step, val) {
  answers[step] = val;
  if (step === 3) { showStep(4); showResult(); }
  else showStep(step + 1);
}

function showStep(n) {
  currentStep = n;
  document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
  const target = document.getElementById(n === 4 ? 'step-result' : 'step-' + n);
  if (target) target.classList.add('active');
  updateProgress(n > 3 ? 3 : n);
}

function goBack(step) { showStep(step - 1); }

function updateProgress(active) {
  document.querySelectorAll('#quizProgress span').forEach((el, i) => {
    el.classList.toggle('done', i < active);
  });
}

function showResult() {
  const rec = getRecommendation();
  if (!rec) return;

  document.getElementById('resultEmoji').textContent  = rec.category === 'Perfume' ? '🌸' : '✨';
  document.getElementById('resultTitle').textContent  = 'Your match: ' + rec.name;
  document.getElementById('resultDesc').textContent   = rec.description || '';
  document.getElementById('resultProduct').innerHTML  = `
    ${rec.image_path ? `<img src="${BASE_URL}${rec.image_path}" alt="${rec.name}" onerror="this.style.display='none'" style="width:100%;max-width:280px;margin:0 auto 12px;border-radius:12px;display:block;object-fit:cover;aspect-ratio:1/1;">` : ''}
    <div style="font-weight:600;color:var(--plum);font-family:var(--font-display);font-size:1.1rem;margin-bottom:4px;">${rec.name}</div>
    <div style="color:var(--pink-deep);font-family:var(--font-display);font-size:1.2rem;font-weight:600;">₦${Number(rec.price).toLocaleString('en-NG')}</div>`;

  document.getElementById('resultCartBtn').onclick = () => {
    window.location.href = BASE_URL + 'pages/product.php?id=' + rec.id;
  };
}

function restartQuiz() {
  Object.keys(answers).forEach(k => delete answers[k]);
  showStep(1);
  updateProgress(0);
}
</script>