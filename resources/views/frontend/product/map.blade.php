<div class="hidden bg-[#000000dd] absolute top-0 left-0 px-[3rem] pt-[2rem] pb-[4rem] h-full w-full z-[100000]" id="map-viewer">
  <div class="cursor-pointer text-white w-max px-[1rem] ml-auto py-[.4rem] z-[100] transition delay-150 ease-in-out bg-[#F28C28] hover:bg-[#D4751C]" onclick="mapClose()">
    <h1>X</h1>
  </div>
  <div id="map"></div>
</div>

<style>
  /* ensure map gets a real height (adjust padding if needed) */
  #map { height: calc(100vh - 6rem); width: 100%; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
/* Globals */
let map = null;
let marker = null;
let isMapInitialized = false;

/* Called when user clicks address input */
function mapShow() {
  const mapViewer = document.getElementById('map-viewer');
  mapViewer.classList.remove('hidden');
  document.body.style.overflow = 'hidden';

  // Delay a tick so the container is visible, then initialize or refresh the map
  setTimeout(() => {
    initOrUpdateMap();
  }, 100); // small delay to allow CSS/layout changes
}

function mapClose() {
  document.getElementById('map-viewer').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

/* Initialize map first time or update existing map if already created */
function initOrUpdateMap() {
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');

  // parse values (may be empty)
  const latVal = parseFloat(latInput?.value);
  const lngVal = parseFloat(lngInput?.value);

  if (!isMapInitialized) {
    // First-time initialization
    const start = (latVal && lngVal) ? [latVal, lngVal] : [27.7172, 85.3240]; // default (Kathmandu) fallback

    map = L.map('map', { center: start, zoom: (latVal && lngVal) ? 18 : 13 });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // create marker (use provided coords if present; else place at center)
    marker = L.marker(start, { draggable: true }).addTo(map);

    // marker dragend -> update inputs and reverse geocode
    marker.on('dragend', function () {
      const p = marker.getLatLng();
      updatePositionInputs(p.lat, p.lng);
      reverseGeocodeAndSetAddress(p.lat, p.lng);
    });

    // map click -> move marker
    map.on('click', function (e) {
      marker.setLatLng(e.latlng);
      updatePositionInputs(e.latlng.lat, e.latlng.lng);
      reverseGeocodeAndSetAddress(e.latlng.lat, e.latlng.lng);
    });

    isMapInitialized = true;

    // If lat/lng not present, try geolocation (once)
    if (!latVal || !lngVal) {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          const { latitude, longitude } = pos.coords;
          map.setView([latitude, longitude], 16);
          marker.setLatLng([latitude, longitude]);
          updatePositionInputs(latitude, longitude);
          reverseGeocodeAndSetAddress(latitude, longitude);
        }, () => {
          // geolocation failed/denied — don't crash, we've already got default center
        });
      }
    } else {
      // we had lat/lng — make sure address is filled
      reverseGeocodeAndSetAddress(latVal, lngVal);
    }
  } else {
    // Map already initialized — just re-center to given coords or keep current
    if (latVal && lngVal) {
      const newLatLng = [latVal, lngVal];
      marker.setLatLng(newLatLng);
      map.setView(newLatLng, 18);
      reverseGeocodeAndSetAddress(latVal, lngVal);
    }
    // ensure Leaflet redraws correctly when container was hidden
    setTimeout(() => { map.invalidateSize(); }, 50);
  }

  // Always call invalidateSize once shown
  setTimeout(() => {
    try { map.invalidateSize(); } catch (e) {}
  }, 200);
}

function updatePositionInputs(lat, lng) {
  const latEl = document.getElementById('latitude');
  const lngEl = document.getElementById('longitude');
  if (latEl) latEl.value = lat;
  if (lngEl) lngEl.value = lng;
}

// Reverse geocode using Nominatim
function reverseGeocodeAndSetAddress(lat, lng) {
  const addressEl = document.getElementById('address');
  if (!addressEl) return;

  // optional: show a loading hint
  addressEl.value = 'Finding address...';

  fetch(`https://nominatim.openstreetmap.org/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&format=jsonv2`)
    .then(r => r.json())
    .then(data => {
      if (data && (data.display_name || data.address)) {
        addressEl.value = data.display_name || buildAddressString(data.address);
      } else {
        addressEl.value = 'Address not found';
      }
    })
    .catch(() => {
      addressEl.value = 'Error fetching address';
    });
}

function buildAddressString(addrObj) {
  // helpful fallback to build readable string from parts
  if (!addrObj) return '';
  const parts = [
    addrObj.road,
    addrObj.neighbourhood,
    addrObj.suburb,
    addrObj.city || addrObj.town || addrObj.village,
    addrObj.state,
    addrObj.postcode,
    addrObj.country
  ].filter(Boolean);
  return parts.join(', ');
}
</script>
