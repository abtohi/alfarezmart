/**
 * Geofencing Logic for Staff
 * Runs in the background to monitor location and internet connection.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Only run for staff
    if (typeof USER_ROLE !== 'undefined' && USER_ROLE === 'staff') {
        initGeofencing();
    }
});

function initGeofencing() {
    const storeLat = parseFloat(STORE_LATITUDE);
    const storeLng = parseFloat(STORE_LONGITUDE);
    const radiusMeters = parseFloat(STORE_RADIUS);

    // If radius is 0 or not configured, disable geofencing
    if (!storeLat || !storeLng || isNaN(radiusMeters) || radiusMeters <= 0) {
        return;
    }

    // 1. Offline Check dihapus agar fitur Kasir POS Offline tetap bisa digunakan oleh Staff.
    // Geolocation API HTML5 tetap bisa bekerja secara offline dengan mengandalkan chip GPS perangkat.

    // 2. Location Tracking
    if (!navigator.geolocation) {
        handleGeofenceViolation('Browser/Perangkat Anda tidak mendukung pelacakan lokasi.');
        return;
    }

    // Watch position continuously
    const watchId = navigator.geolocation.watchPosition(
        (position) => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const distance = calculateDistance(storeLat, storeLng, userLat, userLng);

            if (distance > radiusMeters) {
                // Clear watch to prevent multiple redirects
                navigator.geolocation.clearWatch(watchId);
                handleGeofenceViolation(`Anda terdeteksi berada di luar area toko (${Math.round(distance)} meter). Radius maksimal adalah ${radiusMeters} meter.`);
            }
        },
        (error) => {
            let msg = 'Gagal mendapatkan lokasi.';
            if (error.code === error.PERMISSION_DENIED) {
                msg = 'Akses lokasi ditolak. Staff diwajibkan untuk mengizinkan akses lokasi browser.';
            }
            handleGeofenceViolation(msg);
        },
        {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        }
    );
}

function handleGeofenceViolation(reason) {
    // Prevent multiple alerts
    if (window.isGeofenceViolated) return;
    window.isGeofenceViolated = true;

    alert('AKSES DITOLAK: ' + reason);
    
    // Redirect to logout API or route
    // Add a parameter so the login page can show why they were logged out
    const reasonEncoded = encodeURIComponent(reason);
    window.location.href = BASE_URL + 'auth/logout?reason=' + reasonEncoded;
}

/**
 * Haversine formula to calculate distance between two coordinates in meters
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth radius in meters
    const rad = Math.PI / 180;
    const dLat = (lat2 - lat1) * rad;
    const dLon = (lon2 - lon1) * rad;
    
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * rad) * Math.cos(lat2 * rad) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}
