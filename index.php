<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Navbar - Go Safe!</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <span>Go Safe!</span>
            </div>

            <div class="nav-menu" id="nav-menu">
                <a href="#beranda" class="nav-link active" data-section="beranda">Beranda</a>
                <a href="#tentang-kami" class="nav-link" data-section="tentang-kami">Tentang Kami</a>
                <a href="#fitur" class="nav-link" data-section="fitur">Fitur</a>
                <a href="#cara-kerja" class="nav-link" data-section="cara-kerja">Cara Kerja</a>
            </div>

            <div class="nav-right">
                <div class="nav-login">
                    <a href="auth/login.php" class="login-btn" id="login-btn">
                        <i class="bi bi-person-circle"></i>
                        <span>Masuk</span>
                    </a>
                </div>

                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-header">
                <span>Menu</span>
                <button class="close-btn" id="close-btn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="sidebar-menu">
                <a href="#beranda" class="sidebar-link active" data-section="beranda">
                    <i class="bi bi-house"></i>
                    <span>Beranda</span>
                </a>
                <a href="#Tentang-Kami" class="sidebar-link" data-section="tentang-kami">
                    <i class="bi bi-info-circle"></i>
                    <span>Tentang Kami</span>
                </a>
                <a href="#fitur" class="sidebar-link" data-section="fitur">
                    <i class="bi bi-star"></i>
                    <span>Fitur</span>
                </a>
                <a href="#cara-kerja" class="sidebar-link" data-section="cara-kerja">
                    <i class="bi bi-gear"></i>
                    <span>Cara Kerja</span>
                </a>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

<section id="beranda" class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-subtitle">Perkenalkan</div>
                <h1 class="hero-title">GO SAFE!</h1>
                <p class="hero-slogan">Laporkan Dan Temukan Rute Aman Dikotamu.</p>
                <div class="hero-line"></div>
                <p class="hero-description">
                    GO SAFE adalah platform komunitas untuk menandai rute aman dan area berbahaya
                    membantu orang lain memilih jalur terbaik dan lebih aman.
                </p>
                <button class="hero-btn">
                    <span>Lihat Peta!</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <div class="hero-image">
           <img src="assets/img/ilustrasi1.png" alt="">
        </div>
    </div>
    
    <div class="background-accents">
        <div class="accent-line accent-1"></div>
        <div class="accent-line accent-2"></div>
        <div class="accent-line accent-3"></div>
        <div class="accent-line accent-4"></div>
        <div class="accent-line accent-5"></div>
        <div class="accent-grid"></div>
    </div>
</section>

<section class="tentang-kami" id="tentang-kami">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Tentang Kami</h2>
      <div class="underline"></div>
    </div>
    
    <div class="content-wrapper">
      <div class="text-content">
        <h3 class="tagline">Go Safe!</h3>
        <p class="description">GO SAFE adalah platform berbasis komunitas yang hadir untuk membantu masyarakat melaporkan kondisi jalan, menandai area berbahaya, serta membagikan rute perjalanan yang lebih aman.</p>
        <p class="description">Kami percaya bahwa keamanan di jalan bukan hanya tanggung jawab individu, melainkan hasil dari kolaborasi seluruh warga kota.</p>
        <div class="features">
          <div class="feature-card">
            <h4 class="feature-title">Membangun Mobilitas Yang Berkelanjutan</h4>
            <p class="feature-desc">Dengan memudahkan warga menemukan rute yang lebih aman, GO SAFE tidak hanya menjaga keselamatan, tetapi juga mendorong orang untuk berjalan kaki, bersepeda, atau menggunakan transportasi umum. Hal ini turut berkontribusi pada terciptanya lingkungan perkotaan yang lebih sehat dan ramah lingkungan.</p>
          </div>
          
          <div class="feature-card">
            <h4 class="feature-title">Komunitas Peduli Keselamatan</h4>
            <p class="feature-desc">GO SAFE dibangun dari semangat kolaborasi. Setiap laporan yang masuk berasal dari pengguna di lapangan yang benar-benar melihat kondisi jalan. Dengan begitu, data yang ditampilkan selalu relevan, akurat, dan lahir dari kepedulian bersama terhadap keselamatan di kota.</p>
        </div>
        
        <div class="expertise">
          <div class="expertise-number">#1</div>
          <div class="expertise-text">Pertama Di Indonesia</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="fitur" class="fitur-section">
  <div class="fitur-header">
    <h2 class="fitur-title">Fitur Kami</h2>
    <div class="fitur-underline"></div>
  </div>

  <div class="fitur-container">
    <!-- Card 1 -->
    <div class="fitur-card">
      <div class="fitur-icon">
        <i class="bi bi-file-earmark-bar-graph"></i>
      </div>
      <h3 class="fitur-card-title">Laporan</h3>
      <p>Laporkan jika melihat informasi palsu atau tidak jelas</p>
    </div>

    <!-- Card 2 -->
    <div class="fitur-card">
      <div class="fitur-icon">
        <i class="bi bi-hand-thumbs-up"></i>
        <i class="bi bi-hand-thumbs-down"></i>
      </div>
      <h3 class="fitur-card-title">Like & Dislike</h3>
      <p>Like jika merasa informasi benar, Dislike jika tidak benar untuk tahu informasi mana yang akurat</p>
    </div>

    <!-- Card 3 -->
    <div class="fitur-card">
      <div class="fitur-icon">
        <i class="bi bi-geo-alt-fill"></i>
      </div>
      <h3 class="fitur-card-title">Real-time Map</h3>
      <p>Peta interaktif untuk memantau lokasi secara langsung.</p>
    </div>
  </div>
</section>


 <section id="cara-kerja" class="cara-kerja-section">
        <div class="container">
            <div class="section-title">
                <h2>Cara Kerja</h2>
                <div class="title-line"></div>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 01</span>
                        <h3 class="step-title">Silahkan masuk atau daftar akun terlebih dahulu</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 02</span>
                        <h3 class="step-title">Klik tombol lihat peta diatas</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-map"></i>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 03</span>
                        <h3 class="step-title">Klik tombol di bawah kanan layar untuk menambahkan laporan</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 04</span>
                        <h3 class="step-title">Silahkan tentukan titik awal dan titik akhir yang ingin dikasih tanda</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 05</span>
                        <h3 class="step-title">Isi form yang tersedia seperti kategori, deskripsi dll</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-card">
                        <span class="step-number">STEP 06</span>
                        <h3 class="step-title">Klik kirim dan selesai</h3>
                    </div>
                    <div class="step-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

<script src="assets/js/script.js"></script>
</body>
<footer class="site-footer">
  <p>
    &copy; 2025 | <span class="footer-brand">Go Safe!</span>
  </p>
</footer>
</html>