// 📁 js/filter.js
let map = null;
let markers = [];

function initMap() {
    map = L.map('coffee-map').setView([41.2565, -95.9345], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
}

function updateMapMarkers(markerData) {
    markers.forEach(marker => marker.remove());
    markers = [];

    markerData.forEach(item => {
        if (item.lat && item.lng) {
            const marker = L.marker([item.lat, item.lng])
                .addTo(map)
                .bindPopup(`<a href="${item.url}">${item.title}</a>`);
            markers.push(marker);
        }
    });
}

jQuery(document).ready(function ($) {
    initMap();

    function fetchShops(page = 1) {
        const form = $('#ocd-filters');
        const data = form.find(':input').serializeArray();
        data.push({ name: 'page', value: page });
        data.push({ name: 'action', value: 'ocd_filter_coffee_shops' });

        $.post(ocd_ajax_obj.ajax_url, data, function (response) {
            $('#ocd-results').html(response.html);
            updateMapMarkers(response.markers);
        });

    }

    $('#clear-filters').on('click', function () {
        $('#ocd-filters')[0].reset();
        fetchShops();
    });

    $('#ocd-filters').on('change input', 'select, input[type=checkbox]', function () {
        fetchShops();
    });

    // Wait for a pause in typing, and skip partial ZIPs like "681" on the way to a full one.
    let zipTimer = null;
    $('#ocd-filters').on('change input', 'input[type=text]', function () {
        const zip = $(this).val().trim();
        clearTimeout(zipTimer);
        zipTimer = setTimeout(function () {
            if (zip === '' || /^\d{5}(-\d{4})?$/.test(zip)) {
                fetchShops();
            }
        }, 300);
    });

    $('#ocd-results').on('click', '.load-more', function () {
        const nextPage = $(this).data('next-page');
        fetchShops(nextPage);
    });

    fetchShops(); // Initial load
});
