<?php
session_start();

// If user is already logged in, go straight to the dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RAILCONTROL – Railway Operations &amp; Track Maintenance Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="welcome-fullscreen">

    <!-- Full-Screen Dark Navy Hero + Full-Width Moving Train -->
    <section class="hero-fullscreen">
        <div class="hero-inner">
            <!-- Brand Header Row inside Hero -->
            <div class="hero-brand-row">
                <div class="hero-brand">
                    <div class="brand-badge">R</div>
                    <div>
                        <div class="brand-title">RAILCONTROL</div>
                        <div class="brand-sub">OPERATIONS PLATFORM</div>
                    </div>
                </div>
                <span class="hero-pill">
                    <span class="status-dot"></span> REAL-TIME CORRIDOR &amp; ASSET TELEMETRY
                </span>
            </div>

            <div class="hero-copy">
                <h1>Keep every kilometre of track safe and on schedule</h1>
                <p>
                    Monitor trains, stations, routes, and track infrastructure in one unified control platform. 
                    Sign in to access live schedules, signal control, defect grading, and maintenance dispatch.
                </p>

                <div class="hero-cta-group">
                    <a class="btn-primary-teal" href="login.php">Log in to Dashboard &rarr;</a>
                    <a class="btn-secondary-glass" href="login.php?action=signup">Create Operator Account</a>
                </div>
            </div>
        </div>

        <!-- Edge-to-Edge Full-Width Animated Bullet Train Stage -->
        <div class="train-stage-full">
            <svg viewBox="0 0 1440 165" width="100%" preserveAspectRatio="xMidYMax slice" role="img" aria-label="Modern electric train moving across the screen">
                <defs>
                    <linearGradient id="skyGlow" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#14B8A6" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#14B8A6" stop-opacity="0.16"/>
                    </linearGradient>

                    <linearGradient id="headlightBeam" x1="0" y1="0.5" x2="1" y2="0.5">
                        <stop offset="0%" stop-color="#5EEAD4" stop-opacity="0.6"/>
                        <stop offset="60%" stop-color="#14B8A6" stop-opacity="0.15"/>
                        <stop offset="100%" stop-color="#14B8A6" stop-opacity="0"/>
                    </linearGradient>

                    <linearGradient id="trainBody" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#FFFFFF"/>
                        <stop offset="78%" stop-color="#E2E8F0"/>
                        <stop offset="100%" stop-color="#94A3B8"/>
                    </linearGradient>

                    <linearGradient id="tealStripe" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#0D9488"/>
                        <stop offset="50%" stop-color="#14B8A6"/>
                        <stop offset="100%" stop-color="#5EEAD4"/>
                    </linearGradient>

                    <filter id="neonGlow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="3" result="blur"/>
                        <feMerge>
                            <feMergeNode in="blur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>

                <!-- Ambient Horizon Glow -->
                <rect x="0" y="25" width="1440" height="118" fill="url(#skyGlow)"/>

                <!-- Distant Skyline / Bridge Silhouettes -->
                <g fill="#16293D" opacity="0.75">
                    <rect x="80" y="82" width="30" height="61" rx="2"/>
                    <rect x="118" y="64" width="42" height="79" rx="2"/>
                    <rect x="168" y="90" width="26" height="53" rx="2"/>
                    <rect x="640" y="74" width="34" height="69" rx="2"/>
                    <rect x="682" y="56" width="48" height="87" rx="2"/>
                    <rect x="1160" y="78" width="36" height="65" rx="2"/>
                    <rect x="1204" y="62" width="32" height="81" rx="2"/>
                </g>

                <!-- Overhead Catenary Support Masts & Green Trackside Signals -->
                <g stroke="#334155" stroke-width="2.5" fill="none">
                    <?php foreach ([160, 520, 880, 1240] as $x): ?>
                        <line x1="<?php echo $x; ?>" y1="24" x2="<?php echo $x; ?>" y2="143"/>
                        <path d="M<?php echo $x; ?> 35 L<?php echo $x + 30; ?> 35 L<?php echo $x; ?> 24" stroke="#475569" stroke-width="2"/>
                        <rect x="<?php echo $x - 14; ?>" y="98" width="8" height="18" rx="4" fill="#0F172A" stroke="#475569" stroke-width="1.5"/>
                        <circle cx="<?php echo $x - 10; ?>" cy="103" r="2.2" fill="#10B981" filter="url(#neonGlow)"/>
                    <?php endforeach; ?>
                </g>

                <!-- Overhead Contact Wire & Messenger Cable -->
                <path d="M0 35 Q180 40 360 35 T720 35 T1080 35 T1440 35" stroke="#14B8A6" stroke-width="1.5" fill="none" opacity="0.75"/>
                <line x1="0" y1="39" x2="1440" y2="39" stroke="#5EEAD4" stroke-width="1.3" opacity="0.88"/>

                <!-- Ballast & Sleepers -->
                <rect x="0" y="143" width="1440" height="22" fill="#09131E"/>
                <g stroke="#293B4E" stroke-width="4">
                    <?php for ($i = 0; $i < 72; $i++): ?>
                        <line x1="<?php echo $i * 20; ?>" y1="141" x2="<?php echo $i * 20 + 6; ?>" y2="151"/>
                    <?php endfor; ?>
                </g>

                <!-- Steel Rails -->
                <line x1="0" y1="141" x2="1440" y2="141" stroke="#94A3B8" stroke-width="2.5"/>
                <line x1="0" y1="145" x2="1440" y2="145" stroke="#475569" stroke-width="2"/>

                <!-- MOVING MODERN ELECTRIC BULLET TRAIN (Native SVG SMIL Animation so it always moves) -->
                <g>
                    <animateTransform
                        attributeName="transform"
                        type="translate"
                        from="-460 0"
                        to="1520 0"
                        dur="9s"
                        repeatCount="indefinite" />

                    <!-- Sweeping Headlight Beam -->
                    <polygon points="392,121 570,102 570,140 392,130" fill="url(#headlightBeam)"/>

                    <!-- Rear Passenger Coach -->
                    <rect x="0" y="92" width="185" height="42" rx="8" fill="url(#trainBody)"/>
                    <rect x="0" y="119" width="185" height="6" fill="url(#tealStripe)"/>
                    <g fill="#0F1E2E">
                        <rect x="16" y="100" width="28" height="13" rx="3"/>
                        <rect x="52" y="100" width="28" height="13" rx="3"/>
                        <rect x="88" y="100" width="28" height="13" rx="3"/>
                        <rect x="124" y="100" width="28" height="13" rx="3"/>
                        <rect x="160" y="100" width="14" height="13" rx="3"/>
                    </g>

                    <!-- Flexible Gangway Connector -->
                    <rect x="185" y="96" width="12" height="36" fill="#1E293B"/>
                    <line x1="189" y1="96" x2="189" y2="132" stroke="#475569" stroke-width="1.5"/>
                    <line x1="193" y1="96" x2="193" y2="132" stroke="#475569" stroke-width="1.5"/>

                    <!-- Pantograph Touching Overhead Wire (y=39) -->
                    <g stroke="#CBD5E1" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="232,92 248,66 234,40"/>
                        <line x1="224" y1="39" x2="246" y2="39" stroke="#5EEAD4" stroke-width="2.5"/>
                    </g>
                    <circle cx="235" cy="39" r="3" fill="#FFFFFF" filter="url(#neonGlow)">
                        <animate attributeName="opacity" values="0.2;1;0.2" dur="0.5s" repeatCount="indefinite" />
                    </circle>

                    <!-- Aerodynamic Lead Locomotive -->
                    <path d="M197,92 L335,92 Q362,92 388,116 Q396,123 394,130 Q392,134 382,134 L197,134 Z" fill="url(#trainBody)"/>
                    <path d="M197,119 L389,119 L393,125 L197,125 Z" fill="url(#tealStripe)" filter="url(#neonGlow)"/>

                    <!-- Wrap-Around Cockpit Windshield -->
                    <path d="M332,96 L354,96 Q372,102 383,114 L332,114 Z" fill="#0F1E2E"/>
                    <path d="M342,99 L354,99 L368,111 L356,111 Z" fill="#38BDF8" opacity="0.35"/>

                    <!-- Passenger Windows on Lead Car -->
                    <g fill="#0F1E2E">
                        <rect x="212" y="100" width="28" height="13" rx="3"/>
                        <rect x="248" y="100" width="28" height="13" rx="3"/>
                        <rect x="284" y="100" width="28" height="13" rx="3"/>
                    </g>

                    <!-- Glowing LED Headlight -->
                    <ellipse cx="390" cy="125" rx="4.5" ry="2.5" fill="#FFFFFF" filter="url(#neonGlow)"/>

                    <!-- Bogies & Wheels -->
                    <g fill="#1E293B" stroke="#64748B" stroke-width="1.5">
                        <rect x="22" y="132" width="38" height="6" rx="2"/>
                        <circle cx="30" cy="138" r="4.5" fill="#0F172A"/>
                        <circle cx="52" cy="138" r="4.5" fill="#0F172A"/>

                        <rect x="128" y="132" width="38" height="6" rx="2"/>
                        <circle cx="136" cy="138" r="4.5" fill="#0F172A"/>
                        <circle cx="158" cy="138" r="4.5" fill="#0F172A"/>

                        <rect x="216" y="132" width="38" height="6" rx="2"/>
                        <circle cx="224" cy="138" r="4.5" fill="#0F172A"/>
                        <circle cx="246" cy="138" r="4.5" fill="#0F172A"/>

                        <rect x="322" y="132" width="38" height="6" rx="2"/>
                        <circle cx="330" cy="138" r="4.5" fill="#0F172A"/>
                        <circle cx="352" cy="138" r="4.5" fill="#0F172A"/>
                    </g>
                </g>
            </svg>
        </div>
    </section>

    <!-- Bottom 4 Harmonized Dark-Navy Cards -->
    <section class="bottom-cards-wrap">
        <div class="feature-grid">
            <div class="feature-card card-teal">
                <div class="card-icon-box">&#9783;</div>
                <h3>Asset monitoring</h3>
                <p>Tracks, catenary, bridges and tunnels with live condition ratings and structural logs.</p>
            </div>

            <div class="feature-card card-sky">
                <div class="card-icon-box">&#128197;</div>
                <h3>Inspection scheduling</h3>
                <p>Assign recording vehicles, trains, and engineering crews to active corridor segments.</p>
            </div>

            <div class="feature-card card-rose">
                <div class="card-icon-box">&#9888;</div>
                <h3>Defect lifecycle</h3>
                <p>From field detection and severity risk grading to verified repair sign-off.</p>
            </div>

            <div class="feature-card card-amber">
                <div class="card-icon-box">&#9673;</div>
                <h3>GIS mapping &amp; tracking</h3>
                <p>Live train positions, signal controls, and active maintenance work zones on one map.</p>
            </div>
        </div>
    </section>

</body>
</html>