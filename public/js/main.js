var map;
var partners = [];
var italyProvince = null;
var currentPolygons = L.layerGroup();
var partnerMarkers = {};
var activePartner = null;
var coordinatesCache = JSON.parse(localStorage.getItem('coordinatesCache') || '{}');
const delayBetweenRequests = 3000;

document.addEventListener('DOMContentLoaded', () => {
    const partnerListDiv = document.querySelector('.scrollable-list');
    const infoContentDiv = document.querySelector('.info-content');

    map = L.map('map', {
        zoomControl: false
    }).setView([41.8719, 12.5674], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    currentPolygons.addTo(map);

    var createCustomIcon = (color) => {
        return L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            shadowSize: [41, 41],
            shadowAnchor: [12, 41]
        });
    };

    fetch('public/data/limits_IT_provinces.geojson')
        .then(response => response.json())
        .then(data => {
            italyProvince = data;
            console.log('Province caricate:', italyProvince);
        })
        .catch(error => console.error('Errore durante il caricamento delle province:', error));


    function loadProvince(partner) {
        currentPolygons.clearLayers();

        if (!italyProvince) {
            console.error('Dati delle province non ancora caricati.');
            return;
        }

        const bounds = L.latLngBounds();

        partner.provinces.forEach(provinceName => {
            const province = italyProvince.features.find(feature =>
                feature.properties.prov_name.toLowerCase() === provinceName.toLowerCase()
            );

            if (province) {
                const geoJsonLayer = L.geoJSON(province, {
                    style: {
                        color: partner.color || 'blue',
                        weight: 2,
                        fillOpacity: 0.3,
                        fillColor: partner.color || 'blue'
                    },
                    onEachFeature: function (feature, layer) {
                        layer.bindPopup(`<strong>${feature.properties.prov_name}</strong>`);
                    }
                }).addTo(currentPolygons);

                bounds.extend(geoJsonLayer.getBounds());
            } else {
                console.warn(`Provincia "${provinceName}" non trovata.`);
            }
        });

        if (bounds.isValid()) {
            map.flyToBounds(bounds, {
                maxZoom: 9,
                padding: [50, 50],
                duration: 1.5
            });
        }
    }

    map.on('click', function () {
        currentPolygons.clearLayers();
        map.setView([41.8719, 12.5674], 6);
        activePartner = null;
        showAllMarkers();
        updatePartnersList();
    });

    function loadClientInfo(partner) {
        infoContentDiv.classList.add('loading');

        setTimeout(() => {
            infoContentDiv.innerHTML = `
                <h3>${partner.name}</h3>
                <p><strong>Indirizzo:</strong> ${partner.street}, ${partner.zip_code} ${partner.city}</p>
                <p><strong>Province:</strong> ${partner.provinces.join(', ')}</p>
                <p><strong>Nazione:</strong> ${partner.country}</p>
                <p><strong>Telefono:</strong> ${partner.phone}</p>
                <p><strong>Email:</strong> ${partner.email}</p>
                <p><strong>Sito Web:</strong> ${partner.web_site}</p>
            `;
            infoContentDiv.classList.remove('loading');
        }, 300);
    }

    function loadPartners() {
        fetch('src/php/partners/get_partners.php')
            .then(response => response.json())
            .then(data => {
                partners = data;
                // Inizializzo l'oggetto partnerMarkers
                partners.forEach(partner => {
                    partnerMarkers[partner.name] = null; // inizializzo ogni marker come null
                });
                updatePartnersList();
                updateMap();
            })
            .catch(error => {
                console.error('Errore durante il caricamento dei partner:', error);
                partnerListDiv.innerHTML = 'Errore nel caricamento dei partner.';
            });
    }

    function updatePartnersList() {
        partnerListDiv.innerHTML = '';

        partners.forEach(partner => {
            const partnerCard = document.createElement('div');
            partnerCard.className = 'partner-card';
            if (activePartner && activePartner.name === partner.name) {
                partnerCard.classList.add('selected');
            }
            partnerCard.innerHTML = `
                <h3>${partner.name}</h3>
                <p>${partner.city}</p>
            `;
            partnerCard.addEventListener('click', function () {
                activePartner = partner;
                highlightPartner(partnerCard);
                loadProvince(partner);
                loadClientInfo(partner);
                showPartnerMarker(partner);
                updatePartnersList();
            });
            partnerListDiv.appendChild(partnerCard);
        });
    }

    async function getCoordinatesWithDelay(city, country) {
        if (coordinatesCache[city + country]) {
            return coordinatesCache[city+country];
        }

        return new Promise((resolve) => {
            setTimeout(async () => {
                try {
                    const coordinates = await getCoordinates(city, country);
                    if (coordinates) {
                        coordinatesCache[city + country] = coordinates;
                        localStorage.setItem('coordinatesCache', JSON.stringify(coordinatesCache));
                    }
                    resolve(coordinates);
                } catch (error) {
                    console.error('Errore in getCoordinatesWithDelay:', error);
                    resolve(null);
                }
            }, delayBetweenRequests);
        });
    }


    async function updateMap() {
        for (const partner of partners) {
            const coordinates = await getCoordinatesWithDelay(partner.city, partner.country);
            if (coordinates) {
                createPartnerMarker(partner, coordinates);
            } else {
                console.warn(`Non è stato possibile ottenere le coordinate per ${partner.name}`);
            }
        }
    }

    function createPartnerMarker(partner, coordinates) {
        var marker = L.marker([coordinates.latitude, coordinates.longitude], {
            icon: createCustomIcon(partner.color || 'red')
        }).addTo(map); // Aggiungo immediatamente il marker alla mappa


        marker.bindPopup(`<strong>${partner.name}</strong><br />Indirizzo: ${partner.street}, ${partner.city}`);


        marker.on('click', function () {
            activePartner = partner;
            loadProvince(partner);
            loadClientInfo(partner);
            showPartnerMarker(partner);
            updatePartnersList();
        });

        partnerMarkers[partner.name] = marker; // Salvo il marker nell'oggetto
    }

    function getCoordinates(city, country) {
        return fetch(`https://nominatim.openstreetmap.org/search?q=${city},${country}&format=json&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    return {
                        latitude: parseFloat(data[0].lat),
                        longitude: parseFloat(data[0].lon)
                    };
                } else {
                    console.warn(`Coordinate non trovate per la città "${city}, ${country}".`);
                    return null;
                }
            })
            .catch(error => {
                console.error('Errore durante il recupero delle coordinate:', error);
                return null;
            });
    }

    function closeAllPopups() {
        Object.values(partnerMarkers).forEach(marker => {
            if (marker && marker.isPopupOpen()) { // controllo se il marker esiste
                marker.closePopup();
            }
        });
    }


    function showPartnerMarker(partner) {
        closeAllPopups();
        // Rimuove tutti i marker
        Object.values(partnerMarkers).forEach(marker => {
            if(marker){
                map.removeLayer(marker);
            }
        });

        // Aggiungi solo il marker del partner selezionato
        if (partnerMarkers[partner.name]) {
            map.addLayer(partnerMarkers[partner.name]);
            partnerMarkers[partner.name].openPopup();
        }
    }

    function showAllMarkers() {
        Object.values(partnerMarkers).forEach(marker => {
            if (marker){
                map.addLayer(marker);
            }
        });
    }


    function highlightPartner(partnerCard) {
        document.querySelectorAll('.partner-card').forEach(card => {
            card.classList.remove('selected');
        });
        partnerCard.classList.add('selected');
    }

    loadPartners();
});