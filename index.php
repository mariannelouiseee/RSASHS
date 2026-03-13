<?php
include 'connect.php';

$published = [];
$scheduled = [];

$pub_result = mysqli_query(
    $conn,
    "SELECT * FROM announcements
     WHERE status = 'published'
     ORDER BY created_at DESC
     LIMIT 5"
);
if ($pub_result && mysqli_num_rows($pub_result) > 0) {
    $published = mysqli_fetch_all($pub_result, MYSQLI_ASSOC);
}

$sch_result = mysqli_query(
    $conn,
    "SELECT * FROM announcements
     WHERE status = 'scheduled'
       AND scheduled_at > NOW()
     ORDER BY scheduled_at ASC
     LIMIT 5"
);
if ($sch_result && mysqli_num_rows($sch_result) > 0) {
    $scheduled = mysqli_fetch_all($sch_result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSASHS E-PORTAL</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <header>
        <img src="./img/logo.jpg" alt="RSASHS Logo">
        <h2>RSASHS E-PORTAL</h2>
        <a href="login.php">LOGIN / SIGNUP</a>
    </header>

    <!-- ═══════ ANNOUNCEMENTS ═══════ -->
    <section class="ann-section">

        <div class="ann-section-head">
            <h2>Latest News &amp; <em>Updates</em></h2>
            <p>Stay informed with the latest announcements, events, and upcoming activities from RSASHS.</p>
        </div>

        <div class="ann-tabs">
            <?php if (!empty($scheduled)): ?>
                <button class="ann-tab-btn sched-tab" data-panel="scheduled">
                    <i class="fas fa-clock"></i> Upcoming
                    <span class="badge"><?= count($scheduled) ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- ── PUBLISHED PANEL ── -->
        <div class="ann-panel active" id="panel-published">
            <?php if (!empty($published)): ?>

                <div class="pub-carousel-wrap">

                    <button class="pub-carousel-btn prev" id="carPrev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="pub-carousel-btn next" id="carNext" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <div class="pub-carousel-viewport">
                        <div class="pub-carousel-track" id="carTrack">

                            <?php foreach ($published as $idx => $ann):
                                $modalData = htmlspecialchars(json_encode([
                                    'title'   => $ann['title'],
                                    'message' => $ann['message'],
                                    'date'    => date('F d, Y', strtotime($ann['created_at'])),
                                    'time'    => date('g:i A', strtotime($ann['created_at'])),
                                    'image'   => !empty($ann['image']) ? 'uploads/' . $ann['image'] : './img/announcement.png',
                                ]), ENT_QUOTES);
                            ?>
                                <div class="pub-slide <?= $idx === 0 ? 'is-active' : '' ?>"
                                    onclick="openModal(<?= $modalData ?>)">

                                    <?php if ($idx === 0): ?>
                                        <span class="feat-badge">
                                            <i class="fas fa-star"></i> Latest
                                        </span>
                                    <?php endif; ?>

                                    <span class="pub-slide-num"><?= $idx + 1 ?> / <?= count($published) ?></span>

                                    <div class="pub-slide-inner">
                                        <!-- Image column -->
                                        <div class="pub-slide-img-wrap">
                                            <?php if (!empty($ann['image'])): ?>
                                                <img src="uploads/<?= htmlspecialchars($ann['image']) ?>" alt="">
                                            <?php else: ?>
                                                <img src="./img/announcement.png" alt="Announcement">
                                            <?php endif; ?>
                                            <div class="img-shimmer"></div>
                                        </div>

                                        <!-- Content column -->
                                        <div class="pub-slide-body">
                                            <div class="pub-slide-meta">
                                                <span class="pub-slide-date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <?= date('F d, Y', strtotime($ann['created_at'])) ?>
                                                </span>
                                                <span class="pub-slide-date">
                                                    <i class="fas fa-clock"></i>
                                                    <?= date('g:i A', strtotime($ann['created_at'])) ?>
                                                </span>
                                            </div>
                                            <div class="slide-number-large"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></div>
                                            <h3><?= htmlspecialchars($ann['title']) ?></h3>
                                            <p class="excerpt"><?= htmlspecialchars($ann['message']) ?></p>
                                            <span class="read-more">
                                                Read full announcement <i class="fas fa-arrow-right"></i>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        </div>

                        <div class="pub-carousel-progress" id="carProgress"></div>
                    </div>

                    <!-- Dot indicators -->
                    <div class="pub-carousel-controls">
                        <div class="pub-carousel-dots" id="carDots">
                            <?php foreach ($published as $idx => $ann): ?>
                                <button class="pub-dot <?= $idx === 0 ? 'active' : '' ?>"
                                    data-index="<?= $idx ?>"
                                    aria-label="Go to slide <?= $idx + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Thumbnail strip -->
                        <div class="pub-thumb-strip" id="carThumbs">
                            <?php foreach ($published as $idx => $ann): ?>
                                <div class="pub-thumb <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
                                    <?php if (!empty($ann['image'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($ann['image']) ?>" alt="">
                                    <?php else: ?>
                                        <img src="./img/announcement.png" alt="">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p class="pub-swipe-hint">← Swipe to browse →</p>

                </div>

            <?php else: ?>
                <div class="ann-empty">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <p>No published announcements yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── SCHEDULED PANEL ── -->
        <?php if (!empty($scheduled)): ?>
            <div class="ann-panel" id="panel-scheduled">
                <div class="sch-layout">
                    <?php foreach ($scheduled as $ann):
                        $ts  = strtotime($ann['scheduled_at']);
                        $now = time();
                        $diff = $ts - $now;
                        $days = floor($diff / 86400);
                        $hrs  = floor(($diff % 86400) / 3600);
                        if ($days > 0)      $countdown = "in {$days}d {$hrs}h";
                        elseif ($hrs > 0)   $countdown = "in {$hrs}h";
                        else                $countdown = "very soon";
                    ?>
                        <div class="sch-item" onclick="openModal(<?= htmlspecialchars(json_encode([
                                                                        'title'   => $ann['title'],
                                                                        'message' => $ann['message'],
                                                                        'date'    => date('F d, Y · g:i A', $ts),
                                                                        'time'    => '',
                                                                        'image'   => !empty($ann['image']) ? 'uploads/' . $ann['image'] : './img/announcement.png',
                                                                    ]), ENT_QUOTES) ?>)">
                            <div class="sch-dot-wrap">
                                <div class="sch-dot"></div>
                            </div>
                            <div class="sch-card">
                                <?php if (!empty($ann['image'])): ?>
                                    <img class="sch-img" src="uploads/<?= htmlspecialchars($ann['image']) ?>" alt="">
                                <?php else: ?>
                                    <img class="sch-img" src="./img/announcement.png" alt="Announcement">
                                <?php endif; ?>
                                <div class="sch-when">
                                    <i class="fas fa-clock"></i>
                                    <?= date('M d, Y · g:i A', $ts) ?>
                                </div>
                                <h4><?= htmlspecialchars($ann['title']) ?></h4>
                                <p class="sch-excerpt"><?= htmlspecialchars($ann['message']) ?></p>
                                <div class="countdown-chip">
                                    <i class="fas fa-hourglass-half"></i> Publishes <?= $countdown ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <!-- Fullscreen image overlay -->
    <div class="overlay" id="imageOverlay">
        <span class="closeBtn" id="overlayClose">&times;</span>
        <img id="fullImage" src="" alt="Announcement Image">
    </div>

    <!-- Detail modal -->
    <div class="ann-modal-overlay" id="annModal">
        <div class="ann-modal" id="annModalBox">
            <button class="ann-modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            <div class="ann-modal-image-wrap" id="annModalImageWrap">
                <img id="annModalImage" src="" alt="" onclick="openImageOverlay(this.src, event)">
                <div class="image-zoom-hint"><i class="fas fa-search-plus"></i> Click to enlarge</div>
            </div>
            <div class="ann-modal-body">
                <div class="ann-modal-meta" id="annModalMeta"></div>
                <h2 id="annModalTitle"></h2>
                <p id="annModalMessage"></p>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-inner">
            <p class="footer-copy">&copy; <?= date('Y') ?> RSASHS E-Portal. All rights reserved.</p>
        </div>
    </footer>

    <script>
        /* ══ TAB SWITCHING ══ */
        document.querySelectorAll('.ann-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ann-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.ann-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                const panel = document.getElementById('panel-' + btn.dataset.panel);
                if (panel) panel.classList.add('active');
            });
        });

        /* ══ CAROUSEL ══ */
        (() => {
            const track = document.getElementById('carTrack');
            if (!track) return;

            const slides = Array.from(track.querySelectorAll('.pub-slide'));
            const dots = Array.from(document.querySelectorAll('#carDots .pub-dot'));
            const thumbs = Array.from(document.querySelectorAll('#carThumbs .pub-thumb'));
            const prevBtn = document.getElementById('carPrev');
            const nextBtn = document.getElementById('carNext');
            const progress = document.getElementById('carProgress');

            const TOTAL = slides.length;
            const AUTO_MS = 5500;
            let current = 0;
            let autoTimer = null;
            let progTimer = null;
            let progStart = null;

            function goTo(idx, resetAuto = true) {
                slides[current].classList.remove('is-active');
                dots[current]?.classList.remove('active');
                thumbs[current]?.classList.remove('active');
                current = (idx + TOTAL) % TOTAL;
                slides[current].classList.add('is-active');
                dots[current]?.classList.add('active');
                thumbs[current]?.classList.add('active');
                track.style.transform = `translateX(-${current * 100}%)`;
                if (resetAuto) startAuto();
            }

            function startAuto() {
                clearInterval(autoTimer);
                clearInterval(progTimer);
                if (TOTAL <= 1) return;
                progress.style.width = '0%';
                progress.style.transition = 'none';
                progStart = performance.now();
                progTimer = setInterval(() => {
                    const elapsed = performance.now() - progStart;
                    const pct = Math.min((elapsed / AUTO_MS) * 100, 100);
                    progress.style.transition = 'none';
                    progress.style.width = pct + '%';
                }, 80);
                autoTimer = setTimeout(() => {
                    clearInterval(progTimer);
                    progress.style.width = '100%';
                    setTimeout(() => goTo(current + 1), 120);
                }, AUTO_MS);
            }

            prevBtn?.addEventListener('click', () => goTo(current - 1));
            nextBtn?.addEventListener('click', () => goTo(current + 1));

            dots.forEach(dot => dot.addEventListener('click', () => goTo(+dot.dataset.index)));
            thumbs.forEach(thumb => thumb.addEventListener('click', () => goTo(+thumb.dataset.index)));

            let touchStartX = 0,
                touchDeltaX = 0;
            track.addEventListener('touchstart', e => {
                touchStartX = e.touches[0].clientX;
                clearTimeout(autoTimer);
                clearInterval(progTimer);
            }, {
                passive: true
            });

            track.addEventListener('touchmove', e => {
                touchDeltaX = e.touches[0].clientX - touchStartX;
                const offset = -(current * 100) + (touchDeltaX / track.parentElement.offsetWidth) * 100;
                track.style.transition = 'none';
                track.style.transform = `translateX(${offset}%)`;
            }, {
                passive: true
            });

            track.addEventListener('touchend', () => {
                track.style.transition = '';
                goTo(Math.abs(touchDeltaX) > 50 ? (touchDeltaX < 0 ? current + 1 : current - 1) : current);
                touchDeltaX = 0;
            });

            track.parentElement.addEventListener('mouseenter', () => {
                clearTimeout(autoTimer);
                clearInterval(progTimer);
            });
            track.parentElement.addEventListener('mouseleave', startAuto);

            document.addEventListener('keydown', e => {
                if (document.getElementById('annModal').style.display === 'flex') return;
                if (e.key === 'ArrowLeft') goTo(current - 1);
                if (e.key === 'ArrowRight') goTo(current + 1);
            });

            if (TOTAL <= 1) {
                prevBtn && (prevBtn.style.display = 'none');
                nextBtn && (nextBtn.style.display = 'none');
            }
            startAuto();
        })();

        /* ══ MODAL ══ */
        function openModal(data) {
            document.getElementById('annModalTitle').textContent = data.title;
            document.getElementById('annModalMessage').textContent = data.message;
            const meta = document.getElementById('annModalMeta');
            meta.innerHTML = '';
            if (data.date) {
                const s1 = document.createElement('span');
                s1.innerHTML = `<i class="fas fa-calendar-alt"></i> ${data.date}`;
                meta.appendChild(s1);
            }
            if (data.time) {
                const s2 = document.createElement('span');
                s2.innerHTML = `<i class="fas fa-clock"></i> ${data.time}`;
                meta.appendChild(s2);
            }
            const imgWrap = document.getElementById('annModalImageWrap');
            const imgEl = document.getElementById('annModalImage');
            if (data.image) {
                imgEl.src = data.image;
                imgWrap.style.display = 'block';
            } else {
                imgWrap.style.display = 'none';
            }
            document.getElementById('annModal').style.display = 'flex';
            document.getElementById('annModalBox').scrollTop = 0;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('annModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('annModal').addEventListener('click', e => {
            if (e.target === document.getElementById('annModal')) closeModal();
        });

        /* ══ FULLSCREEN OVERLAY ══ */
        function openImageOverlay(src, e) {
            if (e) e.stopPropagation();
            if (!src) return;
            document.getElementById('fullImage').src = src;
            document.getElementById('imageOverlay').style.display = 'flex';
        }

        document.getElementById('overlayClose').addEventListener('click', () => {
            document.getElementById('imageOverlay').style.display = 'none';
        });

        document.getElementById('imageOverlay').addEventListener('click', e => {
            if (e.target === document.getElementById('imageOverlay'))
                document.getElementById('imageOverlay').style.display = 'none';
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeModal();
                document.getElementById('imageOverlay').style.display = 'none';
                document.body.style.overflow = '';
            }
        });

        /* ══ SCROLL REVEAL ══ */
        const revealEls = document.querySelectorAll('.sch-item, .ann-section-head');
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    io.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        revealEls.forEach(el => io.observe(el));
    </script>
</body>

</html>