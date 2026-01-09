<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scanner Código de Barras</title>

<script src="assets/js/html5-qrcode.min.js"></script>

<style>
/* ===== BASE ===== */
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    background: #000;
    overflow: hidden;
    font-family: Arial, sans-serif;
}

/* ===== CÁMARA ===== */
#reader {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
}

/* Ocultar UI */
#reader__dashboard_section {
    display: none !important;
}

/* ===== MÁSCARA ===== */
.mask {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 5;
}

/* Oscurecido */
.mask-dark {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.0);
}

/* Ventana */
.mask-window {
    position: absolute;
    top: 50.850%;
    left: 50%;
    width: 280px;
    height: 280px;
    transform: translate(-50%, -100%);
    border-radius: 14px;
    overflow: hidden; /* CLAVE */
}

/* Recorte */
.mask-window::before {
    content: "";
    position: absolute;
    inset: -9999px;
    box-shadow: 0 0 0 9999px rgba(0,0,0,0.1);
    border-radius: 14px;
}

/* Borde */
.mask-window::after {
    content: "";
    position: absolute;
    inset: 0;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    z-index: 3;
}

/* ===== LÁSER (CORRECTO) ===== */
.laser {
    position: absolute;
    left: 8%;
    width: 84%;
    height: 2px;
    background: #ff2b2b;
    box-shadow: 0 0 10px #ff2b2b;
    animation: scan 2s infinite ease-in-out;
    z-index: 2;
}

@keyframes scan {
    0%   { top: 8%;  opacity: 0.4; }
    50%  { top: 92%; opacity: 1; }
    100% { top: 8%;  opacity: 0.4; }
}

/* ===== TEXTO ===== */
.scan-text {
    position: absolute;
    top: calc(50% + 145px);
    left: 50%;
    transform: translateX(-50%);
    color: #fff;
    font-size: 1rem;
    text-align: center;
    opacity: 0.85;
    z-index: 10;
    width: 90%;
}

/* ===== RESULTADO ===== */
.result {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.65);
    padding: 10px 20px;
    border-radius: 10px;
    color: #00ff00;
    font-size: 1.3rem;
    z-index: 10;
}
</style>
</head>

<body>

<div id="reader"></div>

<div class="mask">
    <div class="mask-dark"></div>

    <div class="mask-window">
        <div class="laser"></div>
    </div>

    <div class="scan-text">
        Enfoca el código de <b>BARRAS</b> dentro del recuadro
    </div>
</div>

<div class="result" id="result">Esperando...</div>


<audio id="beep" preload="auto">
    <source src="data:audio/wav;base64,
UklGRlQAAABXQVZFZm10IBAAAAABAAEAESsAACJWAAACABAAZGF0YRAAAAAA
AAAAAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD/
/wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//
AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8A
AP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA
//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD/
/wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//
AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8A">
</audio>



<script>
const result = document.getElementById("result");
let locked = false;

// 🔊 AUDIO CONTEXT
let audioCtx = null;

function playBeep() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }

    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();

    osc.type = "square";       // sonido tipo escáner
    osc.frequency.value = 1200;

    gain.gain.value = 0.15;

    osc.connect(gain);
    gain.connect(audioCtx.destination);

    osc.start();
    osc.stop(audioCtx.currentTime + 0.08); // duración corta
}

function onScanSuccess(decodedText) {
    if (locked) return;
    locked = true;

    result.textContent = decodedText;

    // 🔊 SONIDO
    playBeep();

    // 📳 VIBRACIÓN
    if (navigator.vibrate) navigator.vibrate(200);

    setTimeout(() => locked = false, 1500);
}

const scanner = new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        rememberLastUsedCamera: true,
        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
        facingMode: "environment"
    },
    false
);

scanner.render(onScanSuccess);
</script>



</body>
</html>
