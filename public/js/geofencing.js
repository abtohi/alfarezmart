/**
 * Geofencing Logic for Staff - AlfarezMart
 * Memvalidasi lokasi staff secara agresif.
 * Jika GPS tidak tersedia/ditolak/timeout, staff LANGSUNG di-logout.
 */

(function () {
    'use strict';

    // Hanya jalankan untuk staff
    if (typeof window.GEO_CONFIG === 'undefined' || window.GEO_CONFIG.role !== 'staff') {
        return;
    }

    const { lat, lng, radius, logoutUrl } = window.GEO_CONFIG;

    // Jika koordinat toko belum diatur, nonaktifkan geofencing
    const storeLat = parseFloat(lat);
    const storeLng = parseFloat(lng);
    const maxRadius = parseFloat(radius);

    if (!lat || !lng || isNaN(storeLat) || isNaN(storeLng) || isNaN(maxRadius) || maxRadius <= 0) {
        console.warn('[Geofencing] Koordinat/Radius toko belum diatur. Geofencing dinonaktifkan.');
        return;
    }

    // Flag untuk mencegah logout ganda
    let isViolated = false;
    let watchId = null;

    /**
     * Haversine formula — menghitung jarak dua koordinat dalam meter
     */
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

    /**
     * Paksa logout dengan pesan alasan
     */
    function forceLogout(reason) {
        if (isViolated) return;
        isViolated = true;

        // Stop watching
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }

        // Tampilkan peringatan sebelum redirect
        alert('\u26a0\ufe0f AKSES DITOLAK\n\n' + reason + '\n\nAnda akan dialihkan ke halaman login.');
        window.location.href = logoutUrl + '?reason=' + encodeURIComponent(reason);
    }

    /**
     * Callback berhasil mendapatkan posisi
     */
    function onPositionSuccess(position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        const distance = calculateDistance(storeLat, storeLng, userLat, userLng);

        console.log('[Geofencing] Jarak dari toko: ' + Math.round(distance) + 'm (Maksimal: ' + maxRadius + 'm)');

        if (distance > maxRadius) {
            forceLogout(
                'Anda terdeteksi berada di luar area toko.\nJarak Anda: ' + Math.round(distance) + ' meter.\nRadius Maksimal: ' + maxRadius + ' meter.'
            );
        }
    }

    /**
     * Callback error GPS
     */
    function onPositionError(error) {
        let reason;
        switch (error.code) {
            case error.PERMISSION_DENIED:
                reason = 'Izin akses lokasi (GPS) ditolak. Staff diwajibkan mengizinkan akses lokasi untuk menggunakan aplikasi.';
                break;
            case error.POSITION_UNAVAILABLE:
                reason = 'GPS/Lokasi tidak tersedia. Pastikan GPS Anda menyala dan coba lagi dari area terbuka.';
                break;
            case error.TIMEOUT:
                reason = 'Waktu pengecekan lokasi habis (timeout). Pastikan GPS Anda aktif dan memiliki sinyal.';
                break;
            default:
                reason = 'Gagal mendapatkan lokasi. Pastikan GPS Anda menyala.';
        }
        forceLogout(reason);
    }

    /**
     * Mulai pemantauan GPS
     */
    function startGeofencing() {
        if (!navigator.geolocation) {
            forceLogout('Browser/Perangkat Anda tidak mendukung GPS. Hubungi administrator.');
            return;
        }

        // Cek posisi SEGERA saat halaman dibuka
        navigator.geolocation.getCurrentPosition(onPositionSuccess, onPositionError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });

        // Kemudian pantau secara terus-menerus
        watchId = navigator.geolocation.watchPosition(onPositionSuccess, onPositionError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 30000 // Cache posisi maks 30 detik
        });
    }

    // Jalankan setelah DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startGeofencing);
    } else {
        startGeofencing();
    }

})();
