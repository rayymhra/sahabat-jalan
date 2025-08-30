<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go Safe! - Peta Jalan Aman</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5c99ee;
            --primary-hover: #4a8de8;
            --secondary: #64748b;
            --accent: #7db3f0;
            --surface: #ffffff;
            --surface-elevated: #f8fafc;
            --surface-hover: #f1f5f9;
            --border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--surface);
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4rem;
        }

        .nav-logo {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: color 0.2s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-primary);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -0.5rem;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
            border-radius: 1px;
        }

        .nav-cta {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--surface-hover);
            color: var(--text-primary);
        }

        .mobile-menu {
            display: none;
        }

        /* Hero Section */
        .hero {
            padding: 8rem 1.5rem 4rem;
            text-align: center;
            max-width: 1280px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(92, 153, 238, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(125, 179, 240, 0.03) 0%, transparent 50%);
            animation: subtleFloat 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes subtleFloat {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            33% {
                transform: translateY(-10px) rotate(1deg);
            }
            66% {
                transform: translateY(5px) rotate(-1deg);
            }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--surface-elevated);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease 0.2s forwards;
        }

        .hero-badge i {
            color: var(--primary);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
            letter-spacing: -0.025em;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease 0.4s forwards;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.5;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease 0.6s forwards;
        }

        .hero-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 4rem;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease 0.8s forwards;
        }

        .btn-large {
            padding: 0.875rem 2rem;
            font-size: 1rem;
            border-radius: var(--radius);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(92, 153, 238, 0.3);
        }

        .hero-preview {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            background: var(--surface-elevated);
            border: 1px solid var(--border);
            opacity: 0;
            transform: translateY(40px);
            animation: fadeInUp 1.2s ease 1s forwards;
        }

        .hero-preview::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }
            50% {
                left: 100%;
            }
            100% {
                left: 100%;
            }
        }

        .hero-preview img {
            width: 100%;
            height: auto;
            display: block;
        }

        .hero-mockup {
            aspect-ratio: 16/10; 
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--text-muted);
            position: relative;
        }

        .hero-mockup i {
            font-size: 3rem;
            animation: gentleBounce 3s ease-in-out infinite;
        }

        @keyframes gentleBounce {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        /* Floating elements */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(92, 153, 238, 0.1), rgba(125, 179, 240, 0.05));
        }

        .shape-1 {
            width: 60px;
            height: 60px;
            top: 20%;
            left: 10%;
            animation: float 6s ease-in-out infinite;
        }

        .shape-2 {
            width: 80px;
            height: 80px;
            top: 60%;
            right: 15%;
            animation: float 8s ease-in-out infinite reverse;
        }

        .shape-3 {
            width: 40px;
            height: 40px;
            top: 40%;
            left: 80%;
            animation: float 7s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) translateX(0px);
            }
            33% {
                transform: translateY(-20px) translateX(10px);
            }
            66% {
                transform: translateY(10px) translateX(-5px);
            }
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Problem Section */
        .problem-section {
            padding: 4rem 1.5rem;
            background: var(--surface-elevated);
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .section-description {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
        }

        .problem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .problem-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            transition: all 0.2s ease;
        }

        .problem-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .problem-icon {
            width: 3rem;
            height: 3rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
            color: var(--danger);
        }

        .problem-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .problem-text {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Solution Section */
        .solution-section {
            padding: 4rem 1.5rem;
        }

        .solution-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .solution-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-top: 4rem;
        }

        .solution-text {
            space-y: 2rem;
        }

        .solution-point {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .solution-point-icon {
            width: 2rem;
            height: 2rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: var(--success);
            flex-shrink: 0;
            margin-top: 0.25rem;
        }

        .solution-point-content h4 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .solution-point-content p {
            color: var(--text-secondary);
        }

        .solution-visual {
            position: relative;
        }

        .solution-mockup {
            width: 100%;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
        }

        /* Features Section */
        .features-section {
            padding: 4rem 1.5rem;
            background: var(--surface-elevated);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .feature-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .feature-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(37, 99, 235, 0.1);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .feature-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* How it Works */
        .how-it-works {
            padding: 4rem 1.5rem;
        }

        .steps-container {
            max-width: 800px;
            margin: 3rem auto 0;
        }

        .step {
            display: flex;
            gap: 2rem;
            margin-bottom: 3rem;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 1.25rem;
            top: 3.5rem;
            width: 2px;
            height: calc(100% + 1rem);
            background: var(--border);
        }

        .step-number {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .step-content h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .step-content p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* FAQ */
        .faq-section {
            padding: 4rem 1.5rem;
            background: var(--surface-elevated);
        }

        .faq-container {
            max-width: 800px;
            margin: 3rem auto 0;
        }

        .faq-item {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .faq-question {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-primary);
            transition: background 0.2s ease;
        }

        .faq-question:hover {
            background: var(--surface-hover);
        }

        .faq-icon {
            transition: transform 0.2s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 200px;
            padding: 0 2rem 1.5rem;
        }

        .faq-answer p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 4rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
        }

        .cta-section .section-title {
            color: white;
        }

        .cta-section .section-description {
            color: rgba(255, 255, 255, 0.9);
        }

        .cta-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-white:hover {
            background: var(--surface-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Footer */
        .footer {
            background: var(--text-primary);
            color: white;
            padding: 3rem 1.5rem 1.5rem;
        }

        .footer-content {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            line-height: 1.6;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .mobile-menu {
                display: block;
                background: none;
                border: none;
                color: var(--text-primary);
                font-size: 1.5rem;
                cursor: pointer;
            }

            .hero {
                padding: 6rem 1rem 3rem;
            }

            .hero-cta {
                flex-direction: column;
                align-items: stretch;
            }

            .solution-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .solution-visual {
                order: -1;
            }

            .step {
                gap: 1rem;
            }

            .step-content {
                flex: 1;
            }

            .faq-question {
                padding: 1rem 1.5rem;
            }

            .faq-item.active .faq-answer {
                padding: 0 1.5rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 5rem 0.75rem 2rem;
            }

            .section-title {
                font-size: 1.75rem;
            }

            .problem-card,
            .feature-card {
                padding: 1.5rem;
            }
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Smooth scrolling offset for fixed navbar */
        section {
            scroll-margin-top: 5rem;
        }

        .solution-mockup {
    width: 100%;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-lg);
    overflow: hidden; /* keeps image inside rounded corners */
}

.solution-mockup img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* makes it fill nicely */
    display: block;
}

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="" class="nav-logo">Go Safe!</a>
            
            <div class="nav-menu">
                <a href="#beranda" class="nav-link active">Beranda</a>
                <a href="#masalah" class="nav-link">Masalah</a>
                <a href="#solusi" class="nav-link">Solusi</a>
                <a href="#fitur" class="nav-link">Fitur</a>
                <a href="#cara-kerja" class="nav-link">Cara Kerja</a>
            </div>
            
            <div class="nav-cta">
                <a href="main" class="btn btn-primary">Lihat Peta</a>
                <button class="mobile-menu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="hero">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        
        <div class="hero-badge">
            <i class="bi bi-shield-check"></i>
            Platform Keamanan Jalan Pertama di Indonesia
        </div>
        
        <h1 class="hero-title">
            Temukan Rute Aman<br>
            Untuk Perjalanan Anda
        </h1>
        
        <p class="hero-subtitle">
            Peta interaktif berisi laporan warga tentang lokasi rawan kejahatan dan kecelakaan. 
            Bantu warga lain dengan berbagi informasi keamanan jalan di sekitar Anda.
        </p>
        
        <div class="hero-cta">
            <a href="main" class="btn btn-primary btn-large">
                <i class="bi bi-map"></i>
                Lihat Peta Sekarang
            </a>
            <a href="#solusi" class="btn btn-ghost btn-large">
                Pelajari Lebih Lanjut
            </a>
        </div>
        
        <div class="hero-preview">
            <div class="hero-mockup">
                <i class="bi bi-map"></i>
                <img src="assets/img/interface.PNG" alt="" style="width: fit-content;">
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section id="masalah" class="problem-section">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-badge">Masalah</div>
                <h2 class="section-title">Tantangan Keamanan Jalan di Indonesia</h2>
                <p class="section-description">
                    Data keamanan jalan tersebar dan tidak terpusat, membuat warga kesulitan memilih rute yang aman
                </p>
            </div>
            
            <div class="problem-grid">
                <div class="problem-card fade-in">
                    <div class="problem-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h3 class="problem-title">Data Tidak Terpusat</h3>
                    <p class="problem-text">
                        Informasi tentang lokasi rawan kejahatan tersebar di berbagai platform dan sulit diakses masyarakat umum
                    </p>
                </div>
                
                <div class="problem-card fade-in">
                    <div class="problem-icon">
                        <i class="bi bi-question-circle"></i>
                    </div>
                    <h3 class="problem-title">Kebingungan Pemilihan Rute</h3>
                    <p class="problem-text">
                        Warga bingung memilih jalur aman untuk aktivitas seperti jogging, pulang malam, atau bepergian bersama keluarga
                    </p>
                </div>
                
                <div class="problem-card fade-in">
                    <div class="problem-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h3 class="problem-title">Informasi Tidak Real-time</h3>
                    <p class="problem-text">
                        Kondisi keamanan jalan berubah dinamis, namun informasi yang tersedia seringkali sudah tidak relevan
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section id="solusi" class="solution-section">
        <div class="container">
            <div class="section-header fade-in">
                <div class="section-badge solution-badge">Solusi</div>
                <h2 class="section-title">Go Safe! Hadir Sebagai Solusi</h2>
                <p class="section-description">
                    Platform berbasis masyarakat untuk melaporkan, memverifikasi, dan berbagi informasi keamanan jalan
                </p>
            </div>
            
            <div class="solution-content">
                <div class="solution-text">
                    <div class="solution-point fade-in">
                        <div class="solution-point-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="solution-point-content">
                            <h4>Berbasis Masyarakat</h4>
                            <p>Setiap laporan berasal dari warga yang benar-benar mengalami atau melihat kondisi di lapangan, menciptakan data yang akurat dan terpercaya.</p>
                        </div>
                    </div>
                    
                    <div class="solution-point fade-in">
                        <div class="solution-point-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="solution-point-content">
                            <h4>Sistem Verifikasi</h4>
                            <p>Fitur like/dislike membantu memverifikasi keakuratan laporan, sementara sistem moderasi menjaga kualitas informasi.</p>
                        </div>
                    </div>
                    
                    <div class="solution-point fade-in">
                        <div class="solution-point-icon">
                            <i class="bi bi-lightning"></i>
                        </div>
                        <div class="solution-point-content">
                            <h4>Real-time & Mudah Diakses</h4>
                            <p>Peta interaktif yang dapat diakses kapan saja dengan informasi terkini tentang kondisi keamanan jalan di seluruh kota.</p>
                        </div>
                    </div>
                </div>
                
                <div class="solution-visual fade-in">
                    <div class="solution-mockup" style="aspect-ratio: 4/5; background: linear-gradient(135deg, var(--surface-elevated) 0%, var(--surface-hover) 100%); display: flex; align-items: center; justify-content: center;">
                        <!-- <i class="bi bi-phone" style="font-size: 3rem; color: var(--primary);"></i> -->
                        <img src="assets/img/43188a93-d4e7-45f0-82ab-bbf5a1344db5.jpeg" alt="" >
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="features-section">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Fitur Utama</h2>
                <p class="section-description">
                    Fitur lengkap untuk membantu Anda menemukan dan berbagi informasi keamanan jalan
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h3 class="feature-title">Peta Interaktif</h3>
                    </div>
                    <p class="feature-description">
                        Peta real-time yang menampilkan laporan keamanan jalan dari seluruh masyarakat dengan visualisasi yang mudah dipahami.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <h3 class="feature-title">Tambah Laporan</h3>
                    </div>
                    <p class="feature-description">
                        Laporkan kondisi jalan yang berbahaya dengan mudah. Tambahkan foto, deskripsi, dan lokasi secara otomatis atau manual.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-hand-thumbs-up"></i>
                        </div>
                        <h3 class="feature-title">Sistem Validasi</h3>
                    </div>
                    <p class="feature-description">
                        Fitur like/dislike membantu masyarakat memverifikasi keakuratan laporan dan mengidentifikasi informasi yang dapat dipercaya.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-funnel"></i>
                        </div>
                        <h3 class="feature-title">Filter Canggih</h3>
                    </div>
                    <p class="feature-description">
                        Saring laporan berdasarkan jenis bahaya (kejahatan, kecelakaan, dll), waktu kejadian, dan tingkat kepercayaan laporan.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-flag"></i>
                        </div>
                        <h3 class="feature-title">Sistem Pelaporan</h3>
                    </div>
                    <p class="feature-description">
                        Laporkan informasi palsu atau tidak akurat untuk menjaga kualitas data dan kredibilitas platform.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <h3 class="feature-title">Halaman Profil</h3>
                    </div>
                    <p class="feature-description">
                        Kelola semua kontribusi Anda dalam satu tempat. Lihat riwayat rute, laporan, dan komentar yang telah dibuat, serta pantau interaksi masyarakat.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="feature-title">Pencarian Lokasi</h3>
                    </div>
                    <p class="feature-description">
                        Cari lokasi atau tempat tujuan dengan mudah. Sistem juga dapat mendeteksi lokasi Anda saat ini untuk kemudahan navigasi dan pembuatan rute.
                    </p>
                </div>
                
                <div class="feature-card fade-in">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Moderasi Masyarakat</h3>
                    </div>
                    <p class="feature-description">
                        Sistem moderasi terintegrasi untuk memastikan semua laporan relevan dan membantu menjaga keamanan masyarakat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section id="cara-kerja" class="how-it-works">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Cara Menggunakan Go Safe!</h2>
                <p class="section-description">
                    Ikuti langkah sederhana ini untuk mulai berkontribusi pada keamanan jalan di kota Anda
                </p>
            </div>
            
            <div class="steps-container">
                <div class="step fade-in">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Daftar & Masuk</h3>
                        <p>Buat akun Go Safe! atau masuk dengan akun yang sudah ada untuk mulai menggunakan semua fitur platform.</p>
                    </div>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Cari Lokasi & Tambah Rute</h3>
                        <p>Gunakan fitur pencarian untuk menemukan tempat yang ingin Anda tuju, atau biarkan sistem mendeteksi lokasi Anda saat ini. Klik "Tambah Rute Baru" untuk membuat rute perjalanan.</p>
                    </div>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Jelajahi Rute & Laporan</h3>
                        <p>Klik pada garis rute di peta untuk melihat detail rute dan membaca laporan keamanan yang sudah ada dari pengguna lain.</p>
                    </div>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Tambah Laporan Keamanan</h3>
                        <p>Di bagian bawah detail rute, klik "Tambah Laporan Baru" untuk melaporkan kondisi keamanan. Sertakan deskripsi, kategori bahaya, dan foto jika diperlukan.</p>
                    </div>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h3>Kelola Kontribusi Anda</h3>
                        <p>Kunjungi halaman profil untuk melihat semua rute, laporan, dan komentar yang telah Anda buat. Pantau feedback dari masyarakat dan tetap aktif berkontribusi.</p>
                    </div>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">6</div>
                    <div class="step-content">
                        <h3>Verifikasi & Berinteraksi</h3>
                        <p>Bantu masyarakat dengan memberikan like/dislike pada laporan dan laporkan informasi yang tidak akurat untuk menjaga kualitas data platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Pertanyaan Umum</h2>
                <p class="section-description">
                    Temukan jawaban atas pertanyaan yang sering diajukan tentang Go Safe!
                </p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Apa itu Go Safe dan bagaimana cara kerjanya?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Go Safe adalah platform berbasis masyarakat yang memungkinkan warga melaporkan kondisi keamanan jalan secara real-time. Platform ini bekerja dengan mengumpulkan laporan dari pengguna, memverifikasinya melalui sistem voting, dan menampilkannya dalam peta interaktif untuk membantu orang lain memilih rute yang lebih aman.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Bagaimana cara menambahkan laporan keamanan jalan?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Pertama, buat rute baru dengan menggunakan fitur pencarian atau deteksi lokasi otomatis. Kemudian klik pada garis rute di peta untuk melihat detail rute. Di bagian bawah halaman detail, Anda akan menemukan tombol "Tambah Laporan Baru". Klik tombol tersebut, isi form dengan kategori bahaya, deskripsi detail, dan tambahkan foto jika diperlukan.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Bagaimana cara mengetahui laporan mana yang dapat dipercaya?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Setiap laporan memiliki sistem voting like/dislike dari masyarakat. Laporan dengan banyak like umumnya lebih dapat dipercaya. Anda juga dapat melihat profil pelapor dan riwayat kontribusinya. Sistem moderasi kami juga secara aktif memverifikasi dan menghapus laporan yang tidak akurat.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Apa yang harus dilakukan jika menemukan laporan palsu?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Jika menemukan laporan yang mencurigakan atau tidak akurat, Anda dapat memberikan dislike dan melaporkan konten tersebut melalui tombol "Laporkan" pada detail laporan. Tim moderasi akan meninjau dan mengambil tindakan yang diperlukan untuk menjaga kualitas informasi di platform.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Bisakah saya melihat kontribusi yang telah saya buat?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Ya, melalui halaman profil Anda dapat melihat semua rute yang telah dibuat, laporan keamanan yang telah dikirim, dan komentar yang telah Anda berikan. Halaman ini juga menampilkan statistik kontribusi dan feedback dari masyarakat terhadap laporan Anda.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Apakah Go Safe gratis untuk digunakan?</span>
                        <i class="bi bi-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Ya, Go Safe sepenuhnya gratis untuk digunakan. Anda dapat mengakses peta, menambahkan laporan, dan menggunakan semua fitur tanpa biaya. Misi kami adalah membantu menciptakan masyarakat yang lebih aman melalui berbagi informasi tanpa hambatan finansial.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Mulai Berkontribusi Hari Ini</h2>
                <p class="section-description">
                    Bergabunglah dengan ribuan warga yang telah membantu menciptakan jalan yang lebih aman untuk semua
                </p>
            </div>
            
            <div class="cta-buttons">
                <a href="map" class="btn btn-white btn-large">
                    <i class="bi bi-map"></i>
                    Lihat Peta Sekarang
                </a>
                <a href="auth/register.php" class="btn btn-outline btn-large">
                    <i class="bi bi-person-plus"></i>
                    Daftar Gratis
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Go Safe!</h3>
                <p>Platform berbasis masyarakat untuk melaporkan dan berbagi informasi keamanan jalan, menciptakan lingkungan yang lebih aman untuk semua warga.</p>
            </div>
            
            <div class="footer-section">
                <h3>Navigasi</h3>
                <ul class="footer-links">
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#masalah">Masalah</a></li>
                    <li><a href="#solusi">Solusi</a></li>
                    <li><a href="#fitur">Fitur</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Tim Pengembang</h3>
                <ul class="footer-links">
                    <li><a href="https://github.com/rayymhra" target="_blank">@rayymhra</a></li>
                    <li><a href="https://github.com/Attrynn" target="_blank">@Attrynn</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Go Safe! Semua hak dilindungi undang-undang.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Active navigation link
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -80% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const sectionId = entry.target.getAttribute('id');
                    
                    // Remove active class from all nav links
                    navLinks.forEach(link => link.classList.remove('active'));
                    
                    // Add active class to current section's nav link
                    const activeLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        }, observerOptions);

        sections.forEach(section => {
            observer.observe(section);
        });

        // Fade in animation
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(element => {
            fadeObserver.observe(element);
        });

        // FAQ toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const isActive = faqItem.classList.contains('active');
                
                // Close all FAQ items
                document.querySelectorAll('.faq-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    faqItem.classList.add('active');
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.8)';
            }
        });
    </script>
</body>
</html>