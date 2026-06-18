<?php
require_once 'includes/config.php';

// Check if user is already logged in
$is_logged_in = false;
$dashboard_url = 'login.php';
$btn_text = 'Masuk Aplikasi';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $is_logged_in = true;
    if ($_SESSION['role'] === 'pengguna') {
        $dashboard_url = 'pages/user/dashboard.php';
        $btn_text = 'Dashboard Saya';
    } elseif ($_SESSION['role'] === 'petugas_keluar') {
        $dashboard_url = 'pages/petugas/dashboard.php';
        $btn_text = 'Dashboard Petugas';
    } elseif ($_SESSION['role'] === 'kepala_loket') {
        $dashboard_url = 'pages/kepala/dashboard.php';
        $btn_text = 'Dashboard Kepala';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SParking UTN — Smart & Integrated Parking System</title>
  
  <script>
    // Theme initialization to prevent flash
    (function() {
      const theme = localStorage.getItem('theme') || 'dark';
      if (theme === 'light') {
        document.documentElement.classList.add('light-mode');
      }
    })();
  </script>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet">
  
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    html {
      scroll-behavior: smooth;
    }
    
    :root {
      --bg: #0b0f19;
      --surface: rgba(255, 255, 255, 0.03);
      --surface-border: rgba(255, 255, 255, 0.08);
      --card-bg: rgba(255, 255, 255, 0.04);
      --card-border: rgba(255, 255, 255, 0.08);
      --text: #f8fafc;
      --text-muted: #94a3b8;
      --primary: #6366f1; /* Purple Indigo */
      --primary-hover: #4f46e5;
      --primary-glow: rgba(99, 102, 241, 0.35);
      --accent: #8b5cf6; /* Violet */
      --success: #10b981; /* Emerald */
      --danger: #ef4444; /* Rose */
      --warning: #f59e0b; /* Amber */
      --glass-bg: rgba(11, 15, 25, 0.65);
      --glass-border: rgba(255, 255, 255, 0.08);
      --shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
      --nav-bg: rgba(11, 15, 25, 0.8);
      --gradient-text: linear-gradient(135deg, #a5b4fc 0%, #6366f1 50%, #8b5cf6 100%);
    }

    :root.light-mode, body.light-mode {
      --bg: #f8fafc;
      --surface: rgba(15, 23, 42, 0.02);
      --surface-border: rgba(15, 23, 42, 0.06);
      --card-bg: #ffffff;
      --card-border: rgba(15, 23, 42, 0.06);
      --text: #0f172a;
      --text-muted: #64748b;
      --primary: #4f46e5;
      --primary-hover: #3730a3;
      --primary-glow: rgba(79, 70, 229, 0.15);
      --accent: #7c3aed;
      --success: #10b981;
      --danger: #ef4444;
      --warning: #f59e0b;
      --glass-bg: rgba(255, 255, 255, 0.75);
      --glass-border: rgba(15, 23, 42, 0.08);
      --shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08);
      --nav-bg: rgba(248, 250, 252, 0.85);
      --gradient-text: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      overflow-x: hidden;
      line-height: 1.6;
      transition: background-color 0.4s ease, color 0.4s ease;
      min-height: 100vh;
      position: relative;
    }

    /* Subtle dot grid bg */
    .grid-bg {
      position: fixed;
      inset: 0;
      z-index: -2;
      pointer-events: none;
      background-image: radial-gradient(rgba(99, 102, 241, 0.08) 1px, transparent 1px);
      background-size: 32px 32px;
      opacity: 0.8;
      transition: background-image 0.4s ease;
    }
    
    body.light-mode .grid-bg {
      background-image: radial-gradient(rgba(79, 70, 229, 0.04) 1px, transparent 1px);
    }

    /* Animated background blobs */
    .blob-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: -1;
      pointer-events: none;
    }
    
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(70px);
      opacity: 0.65;
      transition: opacity 0.4s ease;
    }
    
    body.light-mode .blob {
      opacity: 0.35;
      filter: blur(80px);
    }
    
    .blob-1 {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(99, 102, 241, 0.65) 0%, rgba(99, 102, 241, 0) 70%);
      top: -150px;
      right: -100px;
      animation: floatBlob1 18s ease-in-out infinite alternate;
    }
    
    .blob-2 {
      width: 450px;
      height: 450px;
      background: radial-gradient(circle, rgba(139, 92, 246, 0.55) 0%, rgba(139, 92, 246, 0) 70%);
      bottom: -100px;
      left: -100px;
      animation: floatBlob2 20s ease-in-out infinite alternate;
    }

    .blob-3 {
      width: 380px;
      height: 380px;
      background: radial-gradient(circle, rgba(236, 72, 153, 0.55) 0%, rgba(236, 72, 153, 0) 70%);
      top: 40%;
      right: -50px;
      animation: floatBlob3 22s ease-in-out infinite alternate;
    }

    @keyframes floatBlob1 {
      0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
      50% { transform: translate(100px, 80px) scale(1.15) rotate(180deg); }
      100% { transform: translate(-50px, -60px) scale(0.9) rotate(360deg); }
    }
    
    @keyframes floatBlob2 {
      0% { transform: translate(0px, 0px) scale(1) rotate(0deg); }
      50% { transform: translate(-80px, -80px) scale(0.9) rotate(-180deg); }
      100% { transform: translate(60px, 70px) scale(1.1) rotate(-360deg); }
    }

    @keyframes floatBlob3 {
      0% { transform: translate(0px, 0px) scale(0.9) rotate(0deg); }
      50% { transform: translate(-120px, 60px) scale(1.1) rotate(180deg); }
      100% { transform: translate(40px, -40px) scale(0.95) rotate(360deg); }
    }

    /* ── CONTAINER ── */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ── NAVBAR ── */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 80px;
      background: var(--nav-bg);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--glass-border);
      z-index: 1000;
      display: flex;
      align-items: center;
      transition: background-color 0.4s, border-color 0.4s, transform 0.3s;
    }
    
    .navbar.scrolled {
      height: 70px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    body.light-mode .navbar.scrolled {
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }

    .nav-logos {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    
    .logo-wrapper {
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    
    .nav-logo {
      height: 100%;
      width: auto;
      object-fit: contain;
      transition: filter 0.4s ease;
    }
    
    body.light-mode .logo-utn {
      filter: drop-shadow(0px 2px 4px rgba(0, 0, 0, 0.1));
    }
    
    .nav-divider {
      width: 1px;
      height: 24px;
      background-color: var(--surface-border);
    }
    
    .nav-brand-text {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 19px;
      color: var(--text);
      letter-spacing: -0.5px;
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    /* Theme Toggle */
    .theme-toggle-btn {
      background: var(--surface);
      border: 1px solid var(--surface-border);
      color: var(--text);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      backdrop-filter: blur(8px);
      transition: all 0.3s ease;
      outline: none;
    }
    
    .theme-toggle-btn:hover {
      background: rgba(99, 102, 241, 0.1);
      border-color: var(--primary);
      transform: scale(1.05);
    }

    .icon-svg {
      width: 20px;
      height: 20px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* Glassmorphic Button */
    .btn-glass {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: var(--text);
      padding: 10px 20px;
      border-radius: 12px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    body.light-mode .btn-glass {
      background: rgba(15, 23, 42, 0.04);
      border-color: rgba(15, 23, 42, 0.08);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }
    
    .btn-glass:hover {
      background: var(--primary);
      border-color: var(--primary);
      color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px var(--primary-glow);
    }
    
    .btn-glass:active {
      transform: translateY(0);
    }

    /* ── HERO SECTION ── */
    .hero {
      padding-top: 160px;
      padding-bottom: 80px;
      position: relative;
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 48px;
      align-items: center;
    }

    .hero-content {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(99, 102, 241, 0.1);
      border: 1px solid rgba(99, 102, 241, 0.2);
      color: var(--primary);
      padding: 6px 14px;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 20px;
      animation: pulseGlow 3s infinite;
    }
    
    body.light-mode .hero-badge {
      background: rgba(79, 70, 229, 0.06);
    }
    
    @keyframes pulseGlow {
      0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.2); }
      70% { box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
      100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
    }

    .hero-title {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(36px, 5vw, 54px);
      font-weight: 900;
      line-height: 1.15;
      letter-spacing: -1.5px;
      color: var(--text);
      margin-bottom: 20px;
    }
    
    .hero-title span {
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-desc {
      font-size: clamp(15px, 2.5vw, 17px);
      color: var(--text-muted);
      margin-bottom: 36px;
      max-width: 540px;
    }

    .hero-btns {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      border: none;
      color: #ffffff;
      padding: 14px 28px;
      border-radius: 14px;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
      box-shadow: 0 10px 25px var(--primary-glow);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 30px rgba(99, 102, 241, 0.5);
    }
    
    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--surface-border);
      color: var(--text);
      padding: 14px 28px;
      border-radius: 14px;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }
    
    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.05);
      border-color: var(--text-muted);
      transform: translateY(-2px);
    }
    
    body.light-mode .btn-outline:hover {
      background: rgba(15, 23, 42, 0.03);
    }
    
    .btn-outline:active {
      transform: translateY(0);
    }

    /* ── INTERACTIVE SIMULATOR CARD ── */
    .simulator-card {
      background: var(--glass-bg);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      padding: 26px;
      box-shadow: var(--shadow);
      display: flex;
      flex-direction: column;
      gap: 18px;
      width: 100%;
      position: relative;
      animation: fadeUp .8s ease both;
      overflow: visible;
    }
    
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .simulator-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--surface-border);
      padding-bottom: 14px;
    }
    
    .simulator-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--text);
    }
    
    .live-dot {
      width: 8px;
      height: 8px;
      background-color: var(--danger);
      border-radius: 50%;
      display: inline-block;
      animation: live-pulse 1s infinite alternate;
    }
    
    @keyframes live-pulse {
      0% { transform: scale(0.9); opacity: 0.5; }
      100% { transform: scale(1.2); opacity: 1; }
    }
    
    .stat-badge {
      background: var(--surface);
      border: 1px solid var(--surface-border);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* 3D Wrapper for the Parking Lot Grid */
    .parking-lot-3d-container {
      perspective: 1200px;
      perspective-origin: 50% 30%;
      width: 100%;
      padding: 10px 0;
      overflow: visible;
    }

    .parking-lot-grid {
      display: grid;
      grid-template-columns: 1fr 44px 1fr;
      gap: 10px;
      background: linear-gradient(180deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.9) 100%);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 24px 12px;
      position: relative;
      transform-style: preserve-3d;
      transform: rotateX(50deg) rotateY(0deg) rotateZ(-30deg);
      box-shadow: 
        -12px 12px 0px rgba(0, 0, 0, 0.35),
        -20px 20px 30px rgba(0, 0, 0, 0.5);
      transition: box-shadow 0.3s ease, border-color 0.3s ease;
      will-change: transform;
    }
    
    body.light-mode .parking-lot-grid {
      background: linear-gradient(180deg, rgba(241, 245, 249, 0.85) 0%, rgba(226, 232, 240, 0.95) 100%);
      border-color: rgba(0, 0, 0, 0.08);
      box-shadow: 
        -12px 12px 0px rgba(15, 23, 42, 0.08),
        -20px 20px 30px rgba(15, 23, 42, 0.12);
    }

    .parking-row {
      display: flex;
      flex-direction: column;
      gap: 12px;
      position: relative;
      z-index: 1;
      transform-style: preserve-3d;
    }
    
    .parking-row:hover {
      z-index: 10;
    }
    
    .parking-slot {
      height: 64px;
      position: relative;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
      user-select: none;
      transform-style: preserve-3d;
    }
    
    .row-left .parking-slot {
      border-top: 1.5px dashed var(--surface-border);
      border-bottom: 1.5px dashed var(--surface-border);
      border-left: 1.5px dashed var(--surface-border);
      border-right: none;
      padding-right: 6px;
    }
    
    .row-right .parking-slot {
      border-top: 1.5px dashed var(--surface-border);
      border-bottom: 1.5px dashed var(--surface-border);
      border-right: 1.5px dashed var(--surface-border);
      border-left: none;
      padding-left: 6px;
    }

    .parking-slot::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 4px;
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 0;
    }
    
    .parking-slot:hover::after {
      opacity: 0.08;
    }
    
    .parking-slot.available:hover::after {
      background-color: var(--success);
    }
    
    .parking-slot.occupied:hover::after {
      background-color: var(--danger);
    }
    
    .parking-slot:hover {
      transform: translateZ(8px);
      z-index: 20;
    }
    
    .row-left .parking-slot:hover {
      box-shadow: -4px 0 12px rgba(99, 102, 241, 0.08);
    }
    
    .row-right .parking-slot:hover {
      box-shadow: 4px 0 12px rgba(99, 102, 241, 0.08);
    }

    .slot-marker {
      position: absolute;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      opacity: 0.5;
    }
    
    .row-left .slot-marker {
      left: 8px;
      top: 6px;
    }
    
    .row-right .slot-marker {
      right: 8px;
      top: 6px;
    }

    .slot-indicator {
      position: absolute;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      z-index: 1;
    }
    
    .row-left .slot-indicator {
      left: 8px;
      bottom: 8px;
    }
    
    .row-right .slot-indicator {
      right: 8px;
      bottom: 8px;
    }

    .parking-slot.available .slot-indicator {
      background-color: var(--success);
      box-shadow: 0 0 8px var(--success);
      animation: pulse-green 2s infinite;
    }
    
    .parking-slot.occupied .slot-indicator {
      background-color: var(--danger);
      box-shadow: 0 0 8px var(--danger);
      animation: pulse-red 2s infinite;
    }

    @keyframes pulse-green {
      0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.8); }
      70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
      100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    
    @keyframes pulse-red {
      0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
      70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
      100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .slot-car {
      position: absolute;
      width: 30px;
      height: 56px;
      z-index: 2;
      opacity: 0;
      transform: translateZ(0px) scale(0.7);
      transition: all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
      pointer-events: none;
      filter: drop-shadow(-4px 8px 5px rgba(0, 0, 0, 0.4));
    }
    
    .row-left .slot-car {
      right: 8px;
      transform: translateZ(0px) scale(0.7) rotate(90deg);
    }
    
    .row-right .slot-car {
      left: 8px;
      transform: translateZ(0px) scale(0.7) rotate(-90deg);
    }
    
    .parking-slot.occupied .slot-car {
      opacity: 1;
    }
    
    .row-left .parking-slot.occupied .slot-car {
      transform: translateZ(15px) scale(1) rotate(90deg);
    }
    
    .row-right .parking-slot.occupied .slot-car {
      transform: translateZ(15px) scale(1) rotate(-90deg);
    }
    
    .parking-slot.occupied:hover .slot-car {
      filter: drop-shadow(-8px 16px 8px rgba(0, 0, 0, 0.5));
    }
    
    .row-left .parking-slot.occupied:hover .slot-car {
      transform: translateZ(25px) scale(1.05) rotate(90deg);
    }
    
    .row-right .parking-slot.occupied:hover .slot-car {
      transform: translateZ(25px) scale(1.05) rotate(-90deg);
    }

    .parking-road {
      background: rgba(255, 255, 255, 0.02);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      position: relative;
      padding: 10px 0;
      transform-style: preserve-3d;
    }
    
    body.light-mode .parking-road {
      background: rgba(0, 0, 0, 0.02);
    }
    
    .road-markings {
      width: 0px;
      height: 100%;
      border-left: 1.5px dashed rgba(255, 255, 255, 0.18);
    }
    
    body.light-mode .road-markings {
      border-left: 1.5px dashed rgba(0, 0, 0, 0.12);
    }
    
    .parking-road::before, .parking-road::after {
      font-size: 13px;
      font-weight: bold;
      color: rgba(255, 255, 255, 0.15);
      font-family: Arial, sans-serif;
    }
    
    .parking-road::before {
      content: '▲';
    }
    
    .parking-road::after {
      content: '▼';
    }
    
    body.light-mode .parking-road::before, body.light-mode .parking-road::after {
      color: rgba(0, 0, 0, 0.1);
    }

    /* Tooltip */
    .slot-tooltip {
      position: absolute;
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #ffffff;
      padding: 8px 12px;
      border-radius: 10px;
      font-size: 11px;
      pointer-events: none;
      opacity: 0;
      visibility: hidden;
      z-index: 100;
      white-space: nowrap;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
      transition: all 0.2s ease;
      font-family: inherit;
      line-height: 1.4;
      transform: translateZ(30px);
    }
    
    .row-left .slot-tooltip {
      left: 105%;
      top: 50%;
      transform: translateY(-50%) translateZ(30px) translateX(10px);
    }
    
    .row-right .slot-tooltip {
      right: 105%;
      top: 50%;
      transform: translateY(-50%) translateZ(30px) translateX(-10px);
    }
    
    .parking-slot:hover .slot-tooltip {
      opacity: 1;
      visibility: visible;
    }
    
    .row-left .parking-slot:hover .slot-tooltip {
      transform: translateY(-50%) translateZ(30px) translateX(0);
    }
    
    .row-right .parking-slot:hover .slot-tooltip {
      transform: translateY(-50%) translateZ(30px) translateX(0);
    }

    .simulator-log {
      background: rgba(0, 0, 0, 0.15);
      border-radius: 12px;
      padding: 14px;
      font-family: 'Courier New', Courier, monospace;
      font-size: 11.5px;
      color: var(--text-muted);
      border: 1px solid var(--surface-border);
    }
    
    body.light-mode .simulator-log {
      background: rgba(0, 0, 0, 0.02);
    }
    
    .log-title {
      font-weight: 700;
      margin-bottom: 8px;
      color: var(--text);
      font-size: 10.5px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .log-lines {
      display: flex;
      flex-direction: column;
      gap: 5px;
      max-height: 85px;
      overflow-y: auto;
    }
    
    .log-line {
      line-height: 1.4;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
      display: block;
    }
    
    .log-time {
      color: var(--primary);
    }

    /* ── STATS SECTION ── */
    .stats {
      padding: 60px 0;
      background: var(--surface);
      border-top: 1px solid var(--surface-border);
      border-bottom: 1px solid var(--surface-border);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      text-align: center;
    }

    .stat-item {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .stat-val {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(32px, 4.5vw, 44px);
      font-weight: 800;
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      line-height: 1;
    }

    .stat-lbl {
      font-size: 13.5px;
      color: var(--text-muted);
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* ── FEATURES SECTION ── */
    .features {
      padding: 100px 0;
      position: relative;
    }

    .section-header {
      text-align: center;
      margin-bottom: 60px;
    }

    .section-badge {
      color: var(--primary);
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 12px;
      display: inline-block;
    }

    .section-title {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(28px, 4vw, 36px);
      font-weight: 800;
      color: var(--text);
      letter-spacing: -0.8px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 28px;
    }

    .feature-card {
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 20px;
      padding: 36px 30px;
      transition: border-color 0.4s, box-shadow 0.4s, transform 0.1s ease-out;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      gap: 16px;
      transform-style: preserve-3d;
    }
    
    .feature-card:hover {
      border-color: var(--primary);
      box-shadow: 0 15px 35px var(--primary-glow);
    }
    
    .feature-icon-wrapper {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: rgba(99, 102, 241, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      transition: all 0.3s ease;
      transform: translateZ(20px);
    }
    
    .feature-card:hover .feature-icon-wrapper {
      background: var(--primary);
      color: #ffffff;
      transform: translateZ(25px) scale(1.05);
    }

    .feature-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 18px;
      font-weight: 700;
      color: var(--text);
      transform: translateZ(15px);
    }

    .feature-card p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.5;
      transform: translateZ(10px);
    }

    /* ── SCROLL CTA SECTION ── */
    .scroll-cta {
      padding: 100px 0;
      position: relative;
    }

    .cta-banner {
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
      border: 1px solid var(--glass-border);
      border-radius: 28px;
      padding: 60px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: var(--shadow);
    }
    
    body.light-mode .cta-banner {
      background: linear-gradient(135deg, rgba(79, 70, 229, 0.03) 0%, rgba(124, 58, 237, 0.02) 100%);
    }

    .cta-title {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(26px, 4vw, 36px);
      font-weight: 800;
      color: var(--text);
      margin-bottom: 16px;
      letter-spacing: -0.5px;
    }

    .cta-desc {
      font-size: 15px;
      color: var(--text-muted);
      margin-bottom: 32px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    /* ── FOOTER ── */
    .footer {
      padding: 40px 0;
      border-top: 1px solid var(--surface-border);
      text-align: center;
      color: var(--text-muted);
      font-size: 13.5px;
    }
    
    .footer-content {
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: center;
    }
    
    .footer-logos {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
      opacity: 0.75;
    }
    
    .footer-logo {
      height: 30px;
      width: auto;
    }

    /* ── RESPONSIVE STYLES ── */
    @media (max-width: 992px) {
      .hero-grid {
        grid-template-columns: 1fr;
        gap: 56px;
      }
      
      .hero-content {
        align-items: center;
        text-align: center;
      }
      
      .hero-desc {
        margin-left: auto;
        margin-right: auto;
      }
      
      .hero-btns {
        justify-content: center;
      }
      
      .features-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .navbar {
        height: 70px;
      }
      .nav-divider, .nav-brand-text {
        display: none;
      }
      .hero {
        padding-top: 130px;
        padding-bottom: 60px;
      }
      .stats-grid {
        grid-template-columns: 1fr;
        gap: 24px;
      }
      .cta-banner {
        padding: 40px 20px;
      }
      .hero-btns {
        flex-direction: column;
        width: 100%;
      }
      .hero-btns .btn {
        width: 100%;
        justify-content: center;
      }
      .simulator-card {
        padding: 16px;
      }
      .parking-lot-grid {
        transform: rotateX(45deg) rotateY(0deg) rotateZ(-25deg) scale(0.88);
        box-shadow: 
          -8px 8px 0px rgba(0, 0, 0, 0.35),
          -12px 12px 20px rgba(0, 0, 0, 0.5);
      }
      body.light-mode .parking-lot-grid {
        box-shadow: 
          -8px 8px 0px rgba(15, 23, 42, 0.08),
          -12px 12px 20px rgba(15, 23, 42, 0.12);
      }
      .row-left .slot-tooltip, .row-right .slot-tooltip {
        left: 50%;
        right: auto;
        top: auto;
        bottom: 115%;
        transform: translateY(-50%) translateZ(30px) translateX(-50%) translateY(10px);
      }
      .row-left .parking-slot:hover .slot-tooltip, .row-right .parking-slot:hover .slot-tooltip {
        transform: translateY(-50%) translateZ(30px) translateX(-50%) translateY(0);
      }
    }
  </style>
</head>
<body>

  <!-- Background Layer -->
  <div class="grid-bg"></div>
  <div class="blob-container">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar" id="mainNavbar">
    <div class="container nav-container">
      <a href="#" class="nav-logos">
        <div class="logo-wrapper">
          <img src="logo_utn_nobg.png" alt="Logo UTN" class="nav-logo logo-utn">
        </div>
        <div class="nav-divider"></div>
        <div class="logo-wrapper">
          <img src="logo_sparking_nobg.png" alt="Logo SParking" class="nav-logo logo-sparking">
        </div>
        <span class="nav-brand-text">SParking UTN</span>
      </a>
      
      <div class="nav-actions">
        <!-- Theme Toggle -->
        <button id="themeToggleBtn" class="theme-toggle-btn" title="Ganti Tema">
          <svg class="icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>
        <!-- Login Button -->
        <a href="<?= $dashboard_url ?>" class="btn-glass">
          <svg class="icon-svg" style="width:16px; height:16px;" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          <span><?= $is_logged_in ? 'Dashboard' : 'Masuk' ?></span>
        </a>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <header class="hero">
    <div class="container hero-grid">
      <!-- Hero Copywriting -->
      <div class="hero-content">
        <div class="hero-badge">
          <span class="live-dot" style="background-color: var(--success); animation: pulse-green 1.5s infinite;"></span>
          Sistem Parkir Pintar Universitas
        </div>
        <h1 class="hero-title">Parkir Kampus Lebih <span>Cepat & Nyaman.</span></h1>
        <p class="hero-desc">SParking UTN merupakan platform informasi parkir terintegrasi Universitas Teknologi Nusantara. Pantau ketersediaan slot parkir secara real-time sebelum Anda tiba.</p>
        
        <div class="hero-btns">
          <a href="<?= $dashboard_url ?>" class="btn-primary">
            <span>Mulai Sekarang</span>
            <svg class="icon-svg" style="width:16px; height:16px;" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <?php if (!$is_logged_in): ?>
            <a href="pages/user/register.php" class="btn-outline">Daftar Akun</a>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Hero Interactive Parking Simulator -->
      <div class="simulator-card">
        <div class="simulator-header">
          <div class="simulator-title">
            <span class="live-dot"></span> Live Slot Simulator
          </div>
          <div class="stat-badge">
            <svg class="icon-svg" style="width:14px; height:14px; color:var(--success);" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="avail-count">6</span>/12 Tersedia
          </div>
        </div>
        
        <div class="parking-lot-3d-container">
          <div class="parking-lot-grid">
            <!-- Column Left: Slots A1 - A6 -->
            <div class="parking-row row-left">
              <div class="parking-slot available" data-slot="A1">
                <div class="slot-marker">A1</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A1</strong><br>Status: Tersedia</div>
              </div>
              <div class="parking-slot occupied" data-slot="A2">
                <div class="slot-marker">A2</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A2</strong><br>Status: Terisi</div>
              </div>
              <div class="parking-slot available" data-slot="A3">
                <div class="slot-marker">A3</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A3</strong><br>Status: Tersedia</div>
              </div>
              <div class="parking-slot occupied" data-slot="A4">
                <div class="slot-marker">A4</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A4</strong><br>Status: Terisi</div>
              </div>
              <div class="parking-slot available" data-slot="A5">
                <div class="slot-marker">A5</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A5</strong><br>Status: Tersedia</div>
              </div>
              <div class="parking-slot available" data-slot="A6">
                <div class="slot-marker">A6</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot A6</strong><br>Status: Tersedia</div>
              </div>
            </div>
            
            <!-- Mid road driveway -->
            <div class="parking-road">
              <div class="road-markings"></div>
            </div>
            
            <!-- Column Right: Slots B1 - B6 -->
            <div class="parking-row row-right">
              <div class="parking-slot occupied" data-slot="B1">
                <div class="slot-marker">B1</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B1</strong><br>Status: Terisi</div>
              </div>
              <div class="parking-slot available" data-slot="B2">
                <div class="slot-marker">B2</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B2</strong><br>Status: Tersedia</div>
              </div>
              <div class="parking-slot occupied" data-slot="B3">
                <div class="slot-marker">B3</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B3</strong><br>Status: Terisi</div>
              </div>
              <div class="parking-slot available" data-slot="B4">
                <div class="slot-marker">B4</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B4</strong><br>Status: Tersedia</div>
              </div>
              <div class="parking-slot occupied" data-slot="B5">
                <div class="slot-marker">B5</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B5</strong><br>Status: Terisi</div>
              </div>
              <div class="parking-slot available" data-slot="B6">
                <div class="slot-marker">B6</div>
                <div class="slot-indicator"></div>
                <div class="slot-car"></div>
                <div class="slot-tooltip"><strong>Slot B6</strong><br>Status: Tersedia</div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Live Log Console -->
        <div class="simulator-log">
          <div class="log-title">
            <svg class="icon-svg" style="width:12px; height:12px;" viewBox="0 0 24 24"><polyline points="4 17 10 11 15 16 20 8"/><polyline points="14 8 20 8 20 14"/></svg>
            Live Logs
          </div>
          <div class="log-lines" id="log-lines">
            <div class="log-line"><span class="log-time">[System]</span> Klik slot mana saja untuk mensimulasikan gerak kendaraan.</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- STATISTICS SECTION -->
  <section class="stats">
    <div class="container stats-grid">
      <div class="stat-item">
        <div class="stat-val">5.000+</div>
        <div class="stat-lbl">Pengguna Terdaftar</div>
      </div>
      <div class="stat-item">
        <div class="stat-val">250+</div>
        <div class="stat-lbl">Kapasitas Slot</div>
      </div>
      <div class="stat-item">
        <div class="stat-val">99.9%</div>
        <div class="stat-lbl">Tingkat Akurasi</div>
      </div>
    </div>
  </section>

  <!-- FEATURES SECTION -->
  <section class="features" id="features">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">Teknologi Modern</span>
        <h2 class="section-title">Fitur Unggulan SParking</h2>
      </div>
      
      <div class="features-grid">
        <!-- Feature 1 -->
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </div>
          <h3>Slot Real-time</h3>
          <p>Melihat visualisasi slot parkir kosong secara real-time langsung dari gawai Anda. Menghemat waktu mencari parkir di area kampus.</p>
        </div>
        
        <!-- Feature 2 -->
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <svg class="icon-svg" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <h3>E-Wallet Terintegrasi</h3>
          <p>Lakukan pembayaran parkir secara non-tunai secara aman melalui integrasi e-wallet populer seperti GoPay, OVO, dan DANA.</p>
        </div>
        
        <!-- Feature 3 -->
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <svg class="icon-svg" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h.01"/><path d="M17 7h.01"/><path d="M7 17h.01"/><path d="M17 17h.01"/><path d="M12 7h.01"/><path d="M12 12h.01"/><path d="M12 17h.01"/><path d="M7 12h.01"/><path d="M17 12h.01"/></svg>
          </div>
          <h3>QR Code Tiket</h3>
          <p>Gunakan tiket parkir digital berbasis QR Code untuk memudahkan proses pemindaian pada loket masuk dan loket keluar kampus.</p>
        </div>
        
        <!-- Feature 4 -->
        <div class="feature-card">
          <div class="feature-icon-wrapper">
            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          </div>
          <h3>Struk Digital</h3>
          <p>Mendukung pengurangan penggunaan kertas (paperless) dengan struk digital transaksi parkir yang tersimpan aman pada riwayat akun.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SCROLL CTA SECTION -->
  <section class="scroll-cta" id="ctaSection">
    <div class="container">
      <div class="cta-banner">
        <h2 class="cta-title">Siap Memulai Kemudahan Parkir?</h2>
        <p class="cta-desc">Daftarkan kendaraan Anda sekarang atau masuk dengan akun universitas Anda untuk mengakses fitur pelacakan slot secara real-time.</p>
        <a href="<?= $dashboard_url ?>" class="btn-primary" style="padding: 16px 36px; font-size: 16px;">
          <span>Masuk Sekarang</span>
          <svg class="icon-svg" style="width:18px; height:18px;" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        </a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container footer-content">
      <div class="footer-logos">
        <img src="logo_utn_nobg.png" alt="UTN Logo" class="footer-logo" style="height: 25px; width: auto; object-fit: contain; filter: grayscale(1) opacity(0.7);">
        <span style="font-size: 14px; opacity: 0.5;">|</span>
        <img src="logo_sparking_nobg.png" alt="SParking Logo" class="footer-logo" style="height: 25px; width: auto; object-fit: contain; filter: grayscale(1) opacity(0.7);">
      </div>
      <p>© 2026 SParking UTN. Universitas Teknologi Nusantara. Hak Cipta Dilindungi Undang-Undang.</p>
    </div>
  </footer>

  <!-- GSAP for 3D and Scroll Animations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

  <!-- SCRIPT FOR SIMULATOR AND INTERACTION -->
  <script>
    // Theme Toggle Handler
    (function() {
      const btn = document.getElementById('themeToggleBtn');
      const updateTheme = (isLight) => {
        if (isLight) {
          document.documentElement.classList.add('light-mode');
          document.body.classList.add('light-mode');
          btn.innerHTML = '<svg class="icon-svg" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>'; // Moon icon (switch to dark)
          btn.title = "Ganti ke Tema Gelap";
        } else {
          document.documentElement.classList.remove('light-mode');
          document.body.classList.remove('light-mode');
          btn.innerHTML = '<svg class="icon-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>'; // Sun icon (switch to light)
          btn.title = "Ganti ke Tema Terang";
        }
      };

      // Initial Sync
      const initialLight = localStorage.getItem('theme') === 'light';
      updateTheme(initialLight);

      btn.addEventListener('click', () => {
        const isCurrentLight = document.documentElement.classList.contains('light-mode');
        const nextLight = !isCurrentLight;
        localStorage.setItem('theme', nextLight ? 'light' : 'dark');
        updateTheme(nextLight);
      });
    })();

    // Navbar Scroll Animation
    window.addEventListener('scroll', () => {
      const navbar = document.getElementById('mainNavbar');
      if (window.scrollY > 40) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Interactive Parking Lot Simulator Logic
    document.addEventListener('DOMContentLoaded', () => {
      const slots = document.querySelectorAll('.parking-slot');
      const countEl = document.getElementById('avail-count');
      const logLines = document.getElementById('log-lines');
      
      const licensePrefixes = ['B', 'D', 'F', 'L', 'H', 'AB'];
      const colors = [
        { base: '#6366f1', roof: '#4f46e5' }, // Indigo
        { base: '#3b82f6', roof: '#2563eb' }, // Blue
        { base: '#ef4444', roof: '#dc2626' }, // Red
        { base: '#f59e0b', roof: '#d97706' }, // Amber
        { base: '#10b981', roof: '#059669' }, // Emerald
        { base: '#8b5cf6', roof: '#7c3aed' }, // Violet
        { base: '#64748b', roof: '#475569' }  // Slate Gray
      ];
      
      // Inline top-down 3D detailed car SVG markup generator
      const getCarMarkup = (color, roofColor) => `
        <svg viewBox="0 0 40 80" width="100%" height="100%">
          <!-- Shadow -->
          <rect x="2" y="6" width="36" height="68" rx="10" fill="rgba(0,0,0,0.35)" filter="blur(2px)" />
          <!-- Wheels -->
          <rect x="0" y="14" width="3" height="12" rx="1" fill="#0f172a" />
          <rect x="37" y="14" width="3" height="12" rx="1" fill="#0f172a" />
          <rect x="0" y="54" width="3" height="12" rx="1" fill="#0f172a" />
          <rect x="37" y="54" width="3" height="12" rx="1" fill="#0f172a" />
          <!-- Body -->
          <rect x="3" y="6" width="34" height="68" rx="9" fill="${color}" />
          <!-- Headlights -->
          <rect x="6" y="7" width="6" height="4" rx="1" fill="#fffb00" />
          <rect x="28" y="7" width="6" height="4" rx="1" fill="#fffb00" />
          <!-- Taillights -->
          <rect x="6" y="71" width="6" height="3" rx="1" fill="#ef4444" />
          <rect x="28" y="71" width="6" height="3" rx="1" fill="#ef4444" />
          <!-- Windshield & Glass -->
          <path d="M7 25 C7 17, 33 17, 33 25 L31 52 C31 56, 9 56, 9 52 Z" fill="#1e293b" />
          <!-- Roof -->
          <rect x="9" y="27" width="22" height="20" rx="3" fill="${roofColor}" />
          <!-- Windshield highlights -->
          <path d="M9 20 L31 20" stroke="#64748b" stroke-width="1.5" stroke-linecap="round" />
          <path d="M12 25 L20 18" stroke="rgba(255,255,255,0.2)" stroke-width="2" stroke-linecap="round" />
        </svg>
      `;

      function getRandomPlate() {
        const prefix = licensePrefixes[Math.floor(Math.random() * licensePrefixes.length)];
        const num = Math.floor(Math.random() * 8999) + 1000;
        const suffix = String.fromCharCode(65 + Math.floor(Math.random() * 26)) + String.fromCharCode(65 + Math.floor(Math.random() * 26));
        return `${prefix} ${num} ${suffix}`;
      }
      
      function addLog(message) {
        const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const line = document.createElement('div');
        line.className = 'log-line';
        line.innerHTML = `<span class="log-time">[${time}]</span> ${message}`;
        logLines.insertBefore(line, logLines.firstChild);
        
        while (logLines.children.length > 5) {
          logLines.removeChild(logLines.lastChild);
        }
      }
      
      function updateCount() {
        const availCount = document.querySelectorAll('.parking-slot.available').length;
        countEl.textContent = availCount;
      }
      
      // Initialize slots - Pre-populate all slots so CSS transitions work nicely
      slots.forEach(slot => {
        const carContainer = slot.querySelector('.slot-car');
        const color = colors[Math.floor(Math.random() * colors.length)];
        carContainer.innerHTML = getCarMarkup(color.base, color.roof);
        
        if (slot.classList.contains('occupied')) {
          const plate = getRandomPlate();
          slot.setAttribute('data-plate', plate);
          slot.querySelector('.slot-tooltip').innerHTML = `<strong>Slot ${slot.dataset.slot}</strong><br>Status: Terisi<br>Plat: ${plate}`;
        } else {
          slot.querySelector('.slot-tooltip').innerHTML = `<strong>Slot ${slot.dataset.slot}</strong><br>Status: Tersedia`;
        }
        
        // Slot Click Handler
        slot.addEventListener('click', () => {
          const isOccupied = slot.classList.contains('occupied');
          const slotName = slot.dataset.slot;
          
          if (isOccupied) {
            // Vacate Slot
            slot.classList.remove('occupied');
            slot.classList.add('available');
            const plate = slot.getAttribute('data-plate') || '';
            slot.removeAttribute('data-plate');
            slot.querySelector('.slot-tooltip').innerHTML = `<strong>Slot ${slotName}</strong><br>Status: Tersedia`;
            addLog(`Kendaraan <strong style="color:var(--danger);">${plate}</strong> keluar dari Slot ${slotName}.`);
          } else {
            // Occupy Slot
            slot.classList.remove('available');
            slot.classList.add('occupied');
            // Generate a fresh random car color
            const color = colors[Math.floor(Math.random() * colors.length)];
            carContainer.innerHTML = getCarMarkup(color.base, color.roof);
            const plate = getRandomPlate();
            slot.setAttribute('data-plate', plate);
            slot.querySelector('.slot-tooltip').innerHTML = `<strong>Slot ${slotName}</strong><br>Status: Terisi<br>Plat: ${plate}`;
            addLog(`Kendaraan <strong style="color:var(--success);">${plate}</strong> memasuki Slot ${slotName}.`);
          }
          updateCount();
        });
      });
      
      updateCount();

      // Automate random events to make page alive
      setInterval(() => {
        if (Math.random() > 0.4) {
          const randomIndex = Math.floor(Math.random() * slots.length);
          slots[randomIndex].click();
        }
      }, 7000);

      // GSAP Entrance Animations
      if (typeof gsap !== 'undefined') {
        gsap.from('.hero-badge', { opacity: 0, y: -20, duration: 0.8, delay: 0.2 });
        gsap.from('.hero-title', { opacity: 0, y: 30, duration: 1, delay: 0.4, ease: 'power3.out' });
        gsap.from('.hero-desc', { opacity: 0, y: 20, duration: 0.8, delay: 0.6 });
        gsap.from('.hero-btns', { opacity: 0, y: 20, duration: 0.8, delay: 0.8 });
        gsap.from('.simulator-card', { opacity: 0, scale: 0.95, duration: 1.2, delay: 0.5, ease: 'power4.out' });

        // Scroll reveals
        gsap.registerPlugin(ScrollTrigger);

        gsap.from('.stat-item', {
          scrollTrigger: {
            trigger: '.stats',
            start: 'top 85%',
            toggleActions: 'play none none reverse'
          },
          opacity: 0,
          y: 40,
          stagger: 0.15,
          duration: 0.8,
          ease: 'back.out(1.7)'
        });

        gsap.from('.feature-card', {
          scrollTrigger: {
            trigger: '.features',
            start: 'top 80%',
            toggleActions: 'play none none reverse'
          },
          opacity: 0,
          y: 50,
          stagger: 0.15,
          duration: 0.8,
          ease: 'power2.out'
        });

        gsap.from('.cta-banner', {
          scrollTrigger: {
            trigger: '.scroll-cta',
            start: 'top 85%',
            toggleActions: 'play none none reverse'
          },
          opacity: 0,
          scale: 0.9,
          duration: 0.8,
          ease: 'power3.out'
        });

        // 3D Scroll-linked Rotation for the parking lot grid
        gsap.to('.parking-lot-grid', {
          scrollTrigger: {
            trigger: '.hero',
            start: 'top top',
            end: 'bottom top',
            scrub: 1
          },
          rotateX: 15,
          rotateZ: 0,
          y: 50,
          boxShadow: '-2px 2px 0px rgba(0, 0, 0, 0.1), 0px 10px 15px rgba(0, 0, 0, 0.2)',
          ease: 'none'
        });
      }

      // 3D Parallax Tilt Effect for Feature Cards
      const fCards = document.querySelectorAll('.feature-card');
      fCards.forEach(card => {
        card.addEventListener('mousemove', e => {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          
          const centerX = rect.width / 2;
          const centerY = rect.height / 2;
          
          const rotateX = ((centerY - y) / centerY) * 12; // max tilt 12 degrees
          const rotateY = ((x - centerX) / centerX) * 12;
          
          card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });
        
        card.addEventListener('mouseleave', () => {
          card.style.transform = '';
        });
      });
    });
  </script>
</body>
</html>
