/**
 * Geofencing Logic for Staff - AlfarezMart
 * Memvalidasi lokasi staff secara agresif.
 * Jika GPS tidak tersedia/ditolak/timeout/diluar radius → staff LANGSUNG di-logout
 * dan tidak bisa login kembali sampai berada di radius toko.
 */

(function () {
    'use strict';

    if (typeof window.GEO_CONFIG === 'undefined' || window.GEO_CONFIG.role !== 'staff') {
        return;
    }

    const { lat, lng, radius, logoutUrl } = window.GEO_CONFIG;
    const storeLat = parseFloat(lat);
    const storeLng = parseFloat(lng);
    const maxRadius = parseFloat(radius);

    if (!lat || !lng || isNaN(storeLat) || isNaN(storeLng) || isNaN(maxRadius) || maxRadius <= 0) {
        console.warn('[Geofencing] Koordinat/Radius toko belum diatur. Geofencing dinonaktifkan.');
        return;
    }

    let isViolated = false;
    let watchId = null;

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const rad = Math.PI / 180;
        const dLat = (lat2 - lat1) * rad;
        const dLon = (lon2 - lon1) * rad;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * rad) * Math.cos(lat2 * rad) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function showBlockingAlert(reason, onClose) {
        // Bangun overlay full-screen ala app modal — tidak pakai alert() browser default
        const existing = document.getElementById('geofenceBlockOverlay');
        if (existing) existing.remove();
        const overlay = document.createElement('div');
        overlay.id = 'geofenceBlockOverlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:999999;display:flex;align-items:center;justify-content:center;padding:24px;font-family:Inter,-apple-system,sans-serif;';
        overlay.innerHTML = `
            <div style="max-width:380px;width:100%;background:#16213e;border:1px solid #e63946;border-radius:16px;padding:24px;text-align:center;box-shadow:0 16px 48px rgba(230,57,70,0.35);">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(230,57,70,0.18);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-geo-alt-fill" style="font-size:2rem;color:#e63946;"></i>
                </div>
                <h3 style="color:#fff;font-size:1.05rem;font-weight:700;margin:0 0 10px;">Anda berada di luar radius yang ditentukan!</h3>
                <p style="color:#cbd5e1;font-size:0.85rem;line-height:1.5;margin:0 0 20px;white-space:pre-line;">${reason}</p>
                <button type="button" id="geofenceContinueBtn" style="width:100%;padding:12px;background:linear-gradient(135deg,#e63946,#b8202e);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.9rem;">
                    Mengerti, Lanjutkan Logout
                </button>
            </div>
        `;
        document.body.appendChild(overlay);
        document.getElementById('geofenceContinueBtn').onclick = () => {
            overlay.remove();
            if (typeof onClose === 'function') onClose();
        };
        // Safety: tetap auto-logout setelah 4 detik walau user belum klik
        setTimeout(() => { if (document.getElementById('geofenceBlockOverlay')) onClose && onClose(); }, 4000);
    }

    function forceLogout(reason) {
        if (isViolated) return;
        isViolated = true;
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }
        // Hapus jejak auto-login supaya tidak by-pass cek lokasi setelah dilempar ke /login
        try {
            localStorage.removeItem('alfarezmart_logged_in');
            localStorage.removeItem('alfarezmart_user');
        } catch (e) { /* ignore */ }
        showBlockingAlert(reason, () => {
            window.location.href = logoutUrl + '?reason=' + encodeURIComponent('Anda berada di luar radius yang ditentukan! ' + reason);
        });
    }

    function onPositionSuccess(position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        const distance = calculateDistance(storeLat, storeLng, userLat, userLng);
        console.log('[Geofencing] Jarak dari toko: ' + Math.round(distance) + 'm (Maks: ' + maxRadius + 'm)');
        if (distance > maxRadius) {
            forceLogout(
                'Jarak Anda: ' + Math.round(distance) + ' meter\nRadius Maksimal: ' + maxRadius + ' meter'
            );
        }
    }

    function onPositionError(error) {
        let reason;
        switch (error.code) {
            case error.PERMISSION_DENIED:
                reason = 'Izin akses lokasi ditolak. Staff wajib mengizinkan akses lokasi.';
                break;
            case error.POSITION_UNAVAILABLE:
                reason = 'GPS/Lokasi tidak tersedia. Pastikan GPS menyala dan coba di area terbuka.';
                break;
            case error.TIMEOUT:
                reason = 'Waktu pengecekan lokasi habis. Pastikan GPS aktif dan punya sinyal.';
                break;
            default:
                reason = 'Gagal mendapatkan lokasi. Pastikan GPS menyala.';
        }
        forceLogout(reason);
    }

    function startGeofencing() {
        if (!navigator.geolocation) {
            forceLogout('Browser/Perangkat Anda tidak mendukung GPS. Hubungi administrator.');
            return;
        }
        navigator.geolocation.getCurrentPosition(onPositionSuccess, onPositionError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
        watchId = navigator.geolocation.watchPosition(onPositionSuccess, onPositionError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 30000
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startGeofencing);
    } else {
        startGeofencing();
    }
})();
