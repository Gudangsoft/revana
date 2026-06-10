<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎂 Selamat Ulang Tahun, {{ $name }}!</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 50%, #1a0533 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Confetti Canvas ── */
        #confetti { position: fixed; inset: 0; pointer-events: none; z-index: 10; }

        /* ── Balloons ── */
        .balloon-container {
            position: fixed;
            bottom: -150px;
            width: 100%;
            pointer-events: none;
            z-index: 5;
        }
        .balloon {
            position: absolute;
            font-size: 3rem;
            animation: floatUp linear infinite;
            opacity: 0.85;
        }
        @keyframes floatUp {
            0%   { transform: translateY(0) rotate(-5deg); opacity: 0; }
            10%  { opacity: .85; }
            90%  { opacity: .85; }
            100% { transform: translateY(-110vh) rotate(5deg); opacity: 0; }
        }

        /* ── Stars / Sparkles ── */
        .star {
            position: fixed;
            width: 4px; height: 4px;
            border-radius: 50%;
            background: #fff;
            animation: twinkle 2s infinite alternate;
        }
        @keyframes twinkle {
            from { opacity: .2; transform: scale(1); }
            to   { opacity: 1;  transform: scale(1.6); }
        }

        /* ── Main Card ── */
        .card {
            position: relative;
            z-index: 20;
            background: rgba(255,255,255,.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 28px;
            padding: 3rem 3.5rem;
            text-align: center;
            max-width: 580px;
            width: 92%;
            box-shadow: 0 0 80px rgba(255,200,0,.15), 0 20px 60px rgba(0,0,0,.4);
            animation: cardIn .8s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes cardIn {
            from { opacity:0; transform: scale(.7) translateY(60px); }
            to   { opacity:1; transform: scale(1) translateY(0); }
        }

        .cake-emoji {
            font-size: 5rem;
            display: block;
            animation: bounce 1.2s ease infinite alternate;
            filter: drop-shadow(0 0 20px rgba(255,200,0,.6));
        }
        @keyframes bounce {
            from { transform: translateY(0); }
            to   { transform: translateY(-14px); }
        }

        .headline {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(90deg, #ffd700, #ff6b9d, #c77dff, #4cc9f0);
            background-size: 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 3s linear infinite, fadeSlide .9s .3s both;
            background-position: 0%;
        }
        @keyframes shimmer { to { background-position: 200%; } }

        .name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            margin: .4rem 0 .2rem;
            animation: fadeSlide .9s .5s both;
        }

        .age-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ffd700, #ff9500);
            color: #1a0533;
            font-size: 1.1rem;
            font-weight: 700;
            padding: .45rem 1.4rem;
            border-radius: 50px;
            margin: .6rem 0 1.2rem;
            animation: fadeSlide .9s .7s both, pulse-badge 2s 1.6s ease infinite;
            box-shadow: 0 0 20px rgba(255,215,0,.5);
        }
        @keyframes pulse-badge {
            0%,100% { box-shadow: 0 0 20px rgba(255,215,0,.5); }
            50%      { box-shadow: 0 0 40px rgba(255,215,0,.9); }
        }

        .wishes {
            color: rgba(255,255,255,.85);
            font-size: .95rem;
            line-height: 1.9;
            animation: fadeSlide .9s .9s both;
            text-align: left;
            background: rgba(255,255,255,.05);
            border-radius: 14px;
            padding: 1rem 1.4rem;
            margin-bottom: 1.6rem;
        }
        .wishes li { list-style: none; padding: .1rem 0; }

        .btn-continue {
            display: inline-block;
            background: linear-gradient(135deg, #7b2ff7, #f107a3);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            padding: .85rem 2.4rem;
            border-radius: 50px;
            text-decoration: none;
            letter-spacing: .5px;
            transition: transform .2s, box-shadow .2s;
            animation: fadeSlide .9s 1.1s both;
            box-shadow: 0 4px 20px rgba(123,47,247,.5);
        }
        .btn-continue:hover {
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 8px 30px rgba(123,47,247,.7);
            color: #fff;
        }

        .from-team {
            margin-top: 1.4rem;
            color: rgba(255,255,255,.4);
            font-size: .78rem;
            animation: fadeSlide .9s 1.3s both;
        }

        @keyframes fadeSlide {
            from { opacity:0; transform: translateY(20px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ── Fireworks ── */
        .fw { position: fixed; pointer-events: none; z-index: 8; }
        .fw-spark {
            position: absolute;
            width: 6px; height: 6px;
            border-radius: 50%;
            animation: spark .8s ease-out forwards;
        }
        @keyframes spark {
            0%   { transform: translate(0,0) scale(1); opacity: 1; }
            100% { transform: translate(var(--dx), var(--dy)) scale(0); opacity: 0; }
        }

        /* ── Received Wishes ── */
        .received-wishes {
            margin-top: 1.4rem;
            animation: fadeSlide .9s 1.4s both;
        }
        .received-wishes-title {
            color: rgba(255,255,255,.6);
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: .6rem;
        }
        .wish-item {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            padding: .7rem 1rem;
            margin-bottom: .5rem;
            text-align: left;
        }
        .wish-item .wish-sender {
            font-size: .75rem;
            color: #ffd700;
            font-weight: 600;
            margin-bottom: .2rem;
        }
        .wish-item .wish-message {
            color: rgba(255,255,255,.9);
            font-size: .88rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<canvas id="confetti"></canvas>

<!-- Floating Balloons -->
<div class="balloon-container" id="balloons"></div>

<!-- Fireworks container -->
<div class="fw" id="fw"></div>

<!-- Main celebration card -->
<div class="card">
    <span class="cake-emoji">🎂</span>
    <h1 class="headline">Selamat Ulang Tahun!</h1>
    <div class="name">{{ $name }}</div>
    <div class="age-badge">🎉 {{ $umur }} Tahun 🎉</div>

    <ul class="wishes">
        <li>✨ Semoga panjang umur &amp; selalu sehat</li>
        <li>🌟 Semua impian dan cita-citamu terwujud</li>
        <li>💪 Semakin sukses dalam setiap langkahmu</li>
        <li>❤️&nbsp; Dikelilingi orang-orang yang menyayangimu</li>
        <li>🚀 Tetap semangat berkarya!</li>
    </ul>

    @if(isset($wishes) && $wishes->count() > 0)
    <div class="received-wishes">
        <div class="received-wishes-title">💌 Ucapan dari rekan-rekanmu</div>
        @foreach($wishes as $wish)
        <div class="wish-item">
            <div class="wish-sender">{{ $wish->sender_name }} <span style="color:rgba(255,255,255,.4);font-weight:400">&middot; {{ ucfirst($wish->sender_type) }}</span></div>
            <div class="wish-message">{{ $wish->message }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <a href="{{ $dashboard }}" class="btn-continue">Lanjut ke Dashboard &rarr;</a>
    <div class="from-team">— Ucapan dari Tim SIPERA —</div>
</div>

<script>
/* ── Stars ── */
for (let i = 0; i < 80; i++) {
    const s = document.createElement('div');
    s.className = 'star';
    s.style.cssText = `left:${Math.random()*100}vw;top:${Math.random()*100}vh;`
        + `animation-delay:${Math.random()*3}s;animation-duration:${1.5+Math.random()*2}s;`
        + `width:${2+Math.random()*3}px;height:${2+Math.random()*3}px;opacity:${Math.random()*.5+.1}`;
    document.body.appendChild(s);
}

/* ── Balloons ── */
const balloonEmoji = ['🎈','🎈','🎈','🎊','🎁','⭐','🌟','💛','💜','🩷'];
const bc = document.getElementById('balloons');
function spawnBalloon() {
    const b = document.createElement('div');
    b.className = 'balloon';
    b.textContent = balloonEmoji[Math.floor(Math.random()*balloonEmoji.length)];
    const dur = 5 + Math.random() * 6;
    b.style.cssText = `left:${Math.random()*95}%;`
        + `font-size:${2+Math.random()*2}rem;`
        + `animation-duration:${dur}s;`
        + `animation-delay:0s`;
    bc.appendChild(b);
    setTimeout(() => b.remove(), dur * 1000);
}
for (let i = 0; i < 12; i++) setTimeout(spawnBalloon, i * 400);
setInterval(spawnBalloon, 900);

/* ── Canvas Confetti ── */
const canvas = document.getElementById('confetti');
const ctx    = canvas.getContext('2d');
canvas.width  = window.innerWidth;
canvas.height = window.innerHeight;
window.addEventListener('resize', () => {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
});

const COLORS = ['#ffd700','#ff6b9d','#c77dff','#4cc9f0','#ff9500','#00f5d4','#ffbe0b'];
const pieces = [];

class Piece {
    constructor() { this.reset(true); }
    reset(initial) {
        this.x  = Math.random() * canvas.width;
        this.y  = initial ? Math.random() * -canvas.height : -20;
        this.vx = (Math.random() - .5) * 3;
        this.vy = 2 + Math.random() * 4;
        this.rot = Math.random() * 360;
        this.vr = (Math.random() - .5) * 6;
        this.w  = 8 + Math.random() * 10;
        this.h  = 4 + Math.random() * 6;
        this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
        this.alpha = .9;
        this.shape = Math.random() > .5 ? 'rect' : 'circle';
    }
    update() {
        this.x   += this.vx;
        this.y   += this.vy;
        this.rot += this.vr;
        if (this.y > canvas.height + 20) this.reset(false);
    }
    draw() {
        ctx.save();
        ctx.globalAlpha = this.alpha;
        ctx.fillStyle   = this.color;
        ctx.translate(this.x, this.y);
        ctx.rotate(this.rot * Math.PI / 180);
        if (this.shape === 'circle') {
            ctx.beginPath();
            ctx.arc(0, 0, this.w / 2, 0, Math.PI * 2);
            ctx.fill();
        } else {
            ctx.fillRect(-this.w/2, -this.h/2, this.w, this.h);
        }
        ctx.restore();
    }
}

for (let i = 0; i < 180; i++) pieces.push(new Piece());

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pieces.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
}
animate();

/* ── Fireworks ── */
const fwEl = document.getElementById('fw');
function firework() {
    const cx = Math.random() * window.innerWidth;
    const cy = 80 + Math.random() * (window.innerHeight * .5);
    const colors = ['#ffd700','#ff6b9d','#c77dff','#4cc9f0','#ff9500'];
    for (let i = 0; i < 24; i++) {
        const s = document.createElement('div');
        s.className = 'fw-spark';
        const angle = (i / 24) * 360;
        const dist  = 50 + Math.random() * 80;
        const dx = Math.cos(angle * Math.PI / 180) * dist + 'px';
        const dy = Math.sin(angle * Math.PI / 180) * dist + 'px';
        s.style.cssText = `left:${cx}px;top:${cy}px;background:${colors[i%colors.length]};`
            + `--dx:${dx};--dy:${dy};animation-delay:${Math.random()*.3}s;`
            + `width:${4+Math.random()*4}px;height:${4+Math.random()*4}px`;
        fwEl.appendChild(s);
        setTimeout(() => s.remove(), 1200);
    }
}
firework();
setInterval(firework, 1800);
</script>
</body>
</html>
