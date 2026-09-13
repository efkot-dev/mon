// Ініціалізація карти з білим фоном (без картографічного фону)
const map = L.map('map', {
    crs: L.CRS.Simple,
    dragging: true,
    zoomControl: false,
    preferCanvas: false,
    noWrap: true,
    scrollWheelZoom: false,  // Заборона зуму при прокручуванні колеса миші
    doubleClickZoom: false,  // Заборона зуму при подвійному кліку
    touchZoom: false,        // Заборона зуму для мобільних пристроїв
}).setView([0, 0], 1);  // Використовуємо стандартні значення для центру та масштабу

const bounds = [[-1000, -1000], [1000, 1000]]; // Ваші межі для карти

map.fitBounds(bounds);

L.rectangle(bounds, { color: "#fff", weight: 0 }).addTo(map);

const polygon = L.polygon([
    [-1000, -1000], 
    [1000, -1000], 
    [1000, 1000], 
    [-1000, 1000]
], {
    color: 'red',          // Колір обводки
    weight: 1,             // Товщина лінії
    opacity: 0.7,          // Прозорість лінії
    fillColor: 'transparent' // Без заповнення, тільки обводка
}).addTo(map);

let svgLayer = L.svg({ clickable: true }).addTo(map);
const svg = d3.select(svgLayer._container);

let lastSwitches = [];
let lastConnections = [];
let selectedPort = null; 
let connections = [];

function selectPort(switchId, port) {
    svg.selectAll(".port").classed("selected", false); 
    if (selectedPort) {
        createConnection(selectedPort.switchId, selectedPort.port, switchId, port);
        selectedPort = null;
    } else {
        selectedPort = { switchId, port };
        d3.select(d3.event.target).classed("selected", true);
    }
}
// Функція для створення з'єднання між двома портами
function createConnection(switch1, port1, switch2, port2) {
    connections.push({ switch1, port1, switch2, port2 });
    drawConnections(); 
    fetch('/?do=topology&act=addConn', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ switch1, port1, switch2, port2 })
    }).then(() => fetchData());
}
// Функція для малювання ліній між з'єднаними портами
function drawConnections() {
    svg.selectAll(".line").remove();
    connections.forEach(conn => {
        const switch1 = switches.find(s => s.id === conn.switch1);
        const switch2 = switches.find(s => s.id === conn.switch2);
        const port1Position = getPortPosition(switch1, conn.port1);
        const port2Position = getPortPosition(switch2, conn.port2);
        // Малюємо лінію між портами
        svg.append("line")
            .attr("class", "line")
            .attr("x1", port1Position.x)
            .attr("y1", port1Position.y)
            .attr("x2", port2Position.x)
            .attr("y2", port2Position.y)
            .attr("stroke", "yellow")
            .attr("stroke-width", 2);
    });
}
// Функція для збереження позиції карти в куки
function saveMapPosition() {
    const center = map.getCenter();
    const zoom = map.getZoom();
    const mapPosition = {
        lat: center.lat,
        lng: center.lng,
        zoom: zoom
    };
    document.cookie = `mapPosition=${JSON.stringify(mapPosition)}; path=/`;
}

// Функція для завантаження позиції карти з куки
function loadMapPosition() {
    const cookies = document.cookie.split(';');
    for (let cookie of cookies) {
        if (cookie.trim().startsWith('mapPosition=')) {
            const mapPosition = JSON.parse(cookie.trim().substring('mapPosition='.length));
            return mapPosition;
        }
    }
    return null;  // Якщо немає позиції в куках, повертаємо null
}

// Функція для встановлення початкової позиції карти
function setInitialMapPosition() {
    const savedPosition = loadMapPosition();
    if (savedPosition) {
        map.setView([savedPosition.lat, savedPosition.lng], savedPosition.zoom);
    } else {
        map.setView([0, 0], 1);  // Якщо немає збереженої позиції, ставимо стандартну
    }
}

async function fetchData() {
    const switches = await fetch('/?do=topology&act=get').then(res => res.json());
    const connections = await fetch('/?do=topology&act=conn').then(res => res.json());
    if (JSON.stringify(switches) !== JSON.stringify(lastSwitches) || JSON.stringify(connections) !== JSON.stringify(lastConnections)) {
        drawNetwork(switches, connections);
        lastSwitches = switches;
        lastConnections = connections;
    }
}

// Функція для малювання мережі
function drawNetwork(switches, connections) {
    svg.selectAll("*").remove();
    const switchElements = svg.selectAll(".switch")
        .data(switches)
        .enter()
        .append("g")
        .attr("class", "switch")
        .attr("transform", d => {
            const point = map.latLngToLayerPoint([d.lat, d.lng]);
            return `translate(${point.x}, ${point.y})`;
        });

    switchElements.call(
		d3.drag()
			.on("start", function (event, d) {
				const startPoint = map.latLngToLayerPoint([d.lat, d.lng]);
				d3.select(this).raise();
			})
			.on("drag", function (event, d) {
				const mouseLatLng = map.mouseEventToLatLng(event.sourceEvent);
				const point = map.latLngToLayerPoint(mouseLatLng);
				d3.select(this).attr("transform", `translate(${point.x}, ${point.y})`);
				debouncedUpdateSwitchPosition(d.id, point.x, point.y);
			})
			.on("end", function (event, d) {
				const mouseLatLng = map.mouseEventToLatLng(event.sourceEvent);
				const point = map.latLngToLayerPoint(mouseLatLng);
				debouncedUpdateSwitchPosition(d.id, point.x, point.y);  // Оновлення після завершення перетягування
				d3.select(this).attr("stroke", null);
			})
	);
	const debouncedUpdateSwitchPosition = debounce(function(id, x, y) {
		updateSwitchPosition(id, x, y);
	}, 100);
	
    switchElements.each(function (d) {
    const group = d3.select(this);
    const portSize = 15;
    const portSpacing = 5;
    const portsPerRow = 16;
    const switchWidth = portsPerRow * (portSize + portSpacing);
    const numRows = Math.ceil(d.ports / portsPerRow);  // Розрахунок кількості рядків для портів
    const switchHeight = 20 + numRows * (portSize + portSpacing);  // Висота комутатора з портами

    // Білий прямокутник для назви комутатора
    group.append("rect")
        .attr("width", switchWidth)
        .attr("height", 20)
        .attr("fill", "#0b5ed7");

    // Текст з назвою комутатора
    group.append("text")
        .attr("x", switchWidth / 2)
        .attr("y", 13)
        .attr("text-anchor", "middle")
        .attr("fill", "#fff")
        .text(d.name);

    // Білий квадрат для портів (з відповідною висотою)
    group.append("rect")
        .attr("x", 0)
        .attr("y", 20)
        .attr("width", switchWidth)
        .attr("height", numRows * (portSize + portSpacing))
        .attr("fill", "#fff"); // Товщина обводу

    // Малювання портів
    for (let i = 0; i < d.ports; i++) {
        const row = Math.floor(i / portsPerRow);
        const col = i % portsPerRow;

        // Порти (чорні прямокутники)
        group.append("rect")
            .attr("class", "port")
            .attr("x", col * (portSize + portSpacing)+ 2)
            .attr("y", 24 + row * (portSize + portSpacing))  // Висота старту кожного порту
            .attr("width", portSize)
            .attr("height", portSize)
            .attr("fill", "#cfdaeb")
            .on("click", () => selectPort(d.id, i + 1));

        // Текст з номером порту
        group.append("text")
            .attr("x", (col * (portSize + portSpacing) + portSize / 2) + 2)
            .attr("y", 34 + row * (portSize + portSpacing))
            .attr("text-anchor", "middle")
            .attr("font-size", "10px")
            .attr("fill", "#222")
            .text(i + 1);
    }
});

	drawConnections();  // Малюємо всі з'єднання
}
// Функція для отримання координат порту на карті
function getPortPosition(switchObj, port) {
    const row = Math.floor((port - 1) / 16);
    const col = (port - 1) % 16;
    const portSize = 15;
    const portSpacing = 5;
    const x = switchObj.x + col * (portSize + portSpacing);
    const y = switchObj.y + row * (portSize + portSpacing);
    return { x, y };
}
function debounce(func, delay) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), delay);
    };
}
function updateSwitchPosition(id, x, y) {
    const mouseLatLng = map.containerPointToLatLng([x, y]);
    const lat = mouseLatLng.lat;
    const lng = mouseLatLng.lng;
    console.log(`Updated lat: ${lat}, lng: ${lng}`);
    fetch('/?do=topology&act=update', {
        method: 'POST',
        body: new URLSearchParams({ id, lat, lng })
    });
}
function createConnection(switch1, port1, switch2, port2) {
    fetch('/?do=topology&act=addConn', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ switch1, port1, switch2, port2 })
    }).then(() => fetchData());
}
setInitialMapPosition();
map.on('moveend', saveMapPosition);
fetchData();
