{{-- Apply saved theme synchronously during HTML parse — before CSS/JS --}}
<script>!function(){try{var t=localStorage.getItem('vms-theme')||localStorage.getItem('vms-dash-theme');if('dark'===t){document.documentElement.classList.add('dark');document.documentElement.style.colorScheme='dark'}}catch(e){}}();</script>
<style id="vms-theme-critical">html.dark,html.dark body.dash-body{background:linear-gradient(135deg,#0A2342 0%,#0f172a 52%,#050B14 100%)!important;color:#f1f5f9!important;color-scheme:dark}</style>
<meta name="color-scheme" content="light dark">
