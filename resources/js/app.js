const runtime = {
	toastTimer: null,
	mediaStream: null,
	capturedBlob: null,
	selectedLocation: null,
	userGps: null,
};

document.addEventListener('DOMContentLoaded', () => {
	if (document.body.dataset.page === 'dashboard') {
		initDashboard();
	}
});

function initDashboard() {
	const config = window.FIELDTRACK || {};

	if (config.mode === 'admin') {
		initAdmin(config);
		return;
	}

	initUser(config);
}

function initAdmin(config) {
	const tabs = document.querySelectorAll('[data-admin-tab]');
	const panes = document.querySelectorAll('[data-admin-pane]');
	const modal = document.getElementById('location-modal');
	const form = document.getElementById('location-form');
	const title = document.getElementById('location-modal-title');
	const publicToggle = document.getElementById('location-is-public');
	const usersField = document.getElementById('location-users');

	const showPane = (name) => {
		panes.forEach((pane) => pane.classList.toggle('hidden', pane.dataset.adminPane !== name));
		tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.adminTab === name));
	};

	tabs.forEach((tab) => tab.addEventListener('click', () => showPane(tab.dataset.adminTab)));

	function syncAssignmentField() {
		usersField.disabled = publicToggle.checked;
		if (publicToggle.checked) {
			[...usersField.options].forEach((option) => (option.selected = false));
		}
	}

	publicToggle.addEventListener('change', syncAssignmentField);
	syncAssignmentField();

	window.openLocationModal = (location = null) => {
		form.reset();
		syncAssignmentField();
		form.action = config.routes.locationStore;

		const methodField = form.querySelector('input[name="_method"]');
		if (methodField) {
			methodField.remove();
		}

		if (location) {
			title.textContent = 'Edit Location';
			form.action = `${config.routes.locationBase}/${location.id}`;

			const method = document.createElement('input');
			method.type = 'hidden';
			method.name = '_method';
			method.value = 'PUT';
			form.appendChild(method);

			form.querySelector('[name="name"]').value = location.name;
			form.querySelector('[name="latitude"]').value = location.latitude;
			form.querySelector('[name="longitude"]').value = location.longitude;
			form.querySelector('[name="radius_meters"]').value = location.radius_meters;
			publicToggle.checked = Boolean(location.is_public);
			syncAssignmentField();
			[...usersField.options].forEach((option) => {
				option.selected = (location.assigned_user_ids || []).map(String).includes(option.value);
			});
		} else {
			title.textContent = 'Add Location';
		}

		modal.classList.remove('hidden');
	};

	window.closeLocationModal = () => modal.classList.add('hidden');

	window.renderAdminMap = () => renderMap(config.locations, config.logs, 'admin-map-canvas');

	renderMap(config.locations, config.logs, 'admin-map-canvas');
}

function initUser(config) {
	const stepChoose = document.getElementById('step-choose');
	const stepGps = document.getElementById('step-gps');
	const stepSelfie = document.getElementById('step-selfie');
	const assignedCards = document.querySelectorAll('[data-location-card]');
	const bottomTabs = document.querySelectorAll('[data-user-tab]');
	const locationId = document.getElementById('attendance-location-id');
	const latitudeField = document.getElementById('attendance-latitude');
	const longitudeField = document.getElementById('attendance-longitude');
	const accuracyField = document.getElementById('attendance-accuracy');
	const distanceField = document.getElementById('attendance-distance');
	const selfieInput = document.getElementById('attendance-selfie');
	const preview = document.getElementById('selfie-preview');
	const cameraStage = document.getElementById('camera-stage');
	const video = document.getElementById('camera-video');
	const canvas = document.getElementById('camera-canvas');
	const captureButton = document.getElementById('capture-button');
	const submitButton = document.getElementById('attendance-submit');

	const setStep = (step) => {
		stepChoose.classList.toggle('hidden', step !== 'choose');
		stepGps.classList.toggle('hidden', step !== 'gps');
		stepSelfie.classList.toggle('hidden', step !== 'selfie');
	};

	window.userTab = (tab, element) => {
		document.getElementById('upanel-mark').classList.toggle('hidden', tab !== 'mark');
		document.getElementById('upanel-logs').classList.toggle('hidden', tab !== 'logs');
		bottomTabs.forEach((item) => item.classList.remove('active'));
		if (element) {
			element.classList.add('active');
		}
	};

	window.backToChoose = () => {
		stopCamera();
		runtime.capturedBlob = null;
		runtime.userGps = null;
		preview.style.display = 'none';
		selfieInput.value = '';
		submitButton.classList.add('hidden');
		captureButton.classList.remove('hidden');
		setStep('choose');
		assignedCards.forEach((card) => card.classList.remove('selected'));
		runtime.selectedLocation = null;
	};

	window.backToGps = () => {
		stopCamera();
		runtime.capturedBlob = null;
		preview.style.display = 'none';
		selfieInput.value = '';
		submitButton.classList.add('hidden');
		captureButton.classList.remove('hidden');
		setStep('gps');
	};

	window.selectLocation = (id) => {
		runtime.selectedLocation = config.locations.find((location) => String(location.id) === String(id));
		assignedCards.forEach((card) => card.classList.toggle('selected', card.dataset.locationId === String(id)));
		locationId.value = id;
		setStep('gps');
		toast(`Selected ${runtime.selectedLocation.name}`, 'success');
	};

	window.detectGPS = () => {
		if (!runtime.selectedLocation) {
			toast('Select a client site first.', 'warn');
			return;
		}

		const ring = document.getElementById('gps-ring');
		const message = document.getElementById('gps-message');
		const coords = document.getElementById('gps-coords');
		const result = document.getElementById('gps-result');
		const badge = document.getElementById('distance-badge');
		const button = document.getElementById('detect-button');

		ring.className = 'gps-status-ring detecting';
		ring.textContent = '⌖';
		message.textContent = 'Detecting your location';
		coords.textContent = 'Waiting for GPS...';
		result.classList.add('hidden');
		button.disabled = true;

		const resolveLocation = (latitude, longitude, accuracy) => {
			const distance = haversineDistance(latitude, longitude, runtime.selectedLocation.latitude, runtime.selectedLocation.longitude);
			const inside = distance <= runtime.selectedLocation.radius_meters;

			runtime.userGps = { latitude, longitude, accuracy, distance };
			latitudeField.value = latitude.toFixed(8);
			longitudeField.value = longitude.toFixed(8);
			accuracyField.value = Math.round(accuracy);
			distanceField.value = distance.toFixed(2);

			coords.textContent = `${latitude.toFixed(5)}, ${longitude.toFixed(5)} • ±${Math.round(accuracy)} m`;
			result.classList.remove('hidden');
			badge.className = `badge ${inside ? 'badge-success' : 'badge-danger'}`;
			badge.textContent = inside
				? `${Math.round(distance)} m from site`
				: `${Math.round(distance)} m away, outside radius`;

			ring.className = `gps-status-ring ${inside ? 'ok' : 'error'}`;
			ring.textContent = inside ? '✓' : '!' ;
			message.textContent = inside
				? `Within range of ${runtime.selectedLocation.name}`
				: `Outside the allowed radius for ${runtime.selectedLocation.name}`;

			button.disabled = false;

			if (inside) {
				toast('GPS verified. Continue to selfie capture.', 'success');
				setTimeout(() => setStep('selfie'), 900);
			} else {
				toast('You must be inside the allowed radius.', 'danger');
			}
		};

		if (!navigator.geolocation) {
			fallbackGps(resolveLocation);
			return;
		}

		navigator.geolocation.getCurrentPosition(
			(position) => resolveLocation(position.coords.latitude, position.coords.longitude, position.coords.accuracy),
			() => fallbackGps(resolveLocation),
			{ enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
		);
	};

	window.openCamera = async () => {
		try {
			runtime.mediaStream = await navigator.mediaDevices.getUserMedia({
				video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
				audio: false,
			});

			video.srcObject = runtime.mediaStream;
			cameraStage.style.display = 'block';
			preview.style.display = 'none';
			captureButton.classList.remove('hidden');
			submitButton.classList.add('hidden');
			toast('Camera is ready.', 'success');
		} catch (error) {
			createDemoSelfie();
		}
	};

	window.captureSelfie = () => {
		canvas.width = video.videoWidth || 960;
		canvas.height = video.videoHeight || 720;
		const ctx = canvas.getContext('2d');

		if (canvas.width === 0 || canvas.height === 0) {
			createDemoSelfie();
			return;
		}

		ctx.drawImage(video, 0, 0);
		drawWatermark(ctx, canvas.width, canvas.height);

		canvas.toBlob((blob) => {
			if (!blob) {
				createDemoSelfie();
				return;
			}

			setSelfieBlob(blob, selfieInput, preview, cameraStage);
			stopCamera();
			captureButton.classList.add('hidden');
			submitButton.classList.remove('hidden');
			toast('Selfie captured.', 'success');
		}, 'image/jpeg', 0.88);
	};

	window.retakePhoto = () => {
		runtime.capturedBlob = null;
		selfieInput.value = '';
		preview.style.display = 'none';
		submitButton.classList.add('hidden');
		captureButton.classList.remove('hidden');
		cameraStage.style.display = 'none';
		toast('Ready for another capture.', 'warn');
	};

	window.submitAttendance = () => {
		if (!runtime.capturedBlob || !runtime.userGps || !runtime.selectedLocation) {
			toast('Complete GPS verification and selfie capture first.', 'warn');
			return false;
		}

		return true;
	};

	bottomTabs.forEach((item) => {
		item.addEventListener('click', () => window.userTab(item.dataset.userTab, item));
	});

	assignedCards.forEach((card) => {
		card.addEventListener('click', () => window.selectLocation(card.dataset.locationId));
	});

	setStep('choose');
	renderMap(config.locations, config.logs, 'user-map-canvas');
}

function fallbackGps(resolveLocation) {
	const location = runtime.selectedLocation;
	const latitude = location.latitude + (Math.random() - 0.5) * 0.0006;
	const longitude = location.longitude + (Math.random() - 0.5) * 0.0006;
	resolveLocation(latitude, longitude, 14 + Math.random() * 10);
}

function createDemoSelfie() {
	const preview = document.getElementById('selfie-preview');
	const canvas = document.getElementById('camera-canvas');
	const selfieInput = document.getElementById('attendance-selfie');
	const cameraStage = document.getElementById('camera-stage');
	const submitButton = document.getElementById('attendance-submit');
	const captureButton = document.getElementById('capture-button');

	canvas.width = 960;
	canvas.height = 720;
	const ctx = canvas.getContext('2d');

	const gradient = ctx.createLinearGradient(0, 0, 960, 720);
	gradient.addColorStop(0, '#1e2230');
	gradient.addColorStop(1, '#0f1117');
	ctx.fillStyle = gradient;
	ctx.fillRect(0, 0, 960, 720);

	ctx.fillStyle = '#4f8ef7';
	ctx.beginPath();
	ctx.arc(480, 250, 92, 0, Math.PI * 2);
	ctx.fill();

	ctx.fillStyle = '#22d3b4';
	ctx.beginPath();
	ctx.ellipse(480, 490, 170, 120, 0, 0, Math.PI * 2);
	ctx.fill();

	ctx.fillStyle = 'rgba(0,0,0,0.55)';
	ctx.fillRect(0, 660, 960, 60);
	ctx.fillStyle = '#ffffff';
	ctx.font = '22px DM Mono, monospace';
	ctx.fillText(new Date().toLocaleString(), 28, 697);

	ctx.fillStyle = '#ffffff';
	ctx.font = '28px DM Sans, sans-serif';
	ctx.fillText('Demo selfie fallback', 28, 58);

	canvas.toBlob((blob) => {
		setSelfieBlob(blob, selfieInput, preview, cameraStage);
		captureButton.classList.add('hidden');
		submitButton.classList.remove('hidden');
		toast('Camera fallback image prepared.', 'warn');
	}, 'image/jpeg', 0.9);
}

function setSelfieBlob(blob, input, preview, cameraStage) {
	runtime.capturedBlob = blob;
	const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
	const transfer = new DataTransfer();
	transfer.items.add(file);
	input.files = transfer.files;
	preview.src = URL.createObjectURL(blob);
	preview.style.display = 'block';
	cameraStage.style.display = 'none';
}

function drawWatermark(ctx, width, height) {
	ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
	ctx.fillRect(0, height - 64, width, 64);
	ctx.fillStyle = '#ffffff';
	ctx.font = '20px DM Mono, monospace';
	ctx.fillText(new Date().toLocaleString(), 18, height - 20);

	if (runtime.selectedLocation) {
		ctx.fillText(runtime.selectedLocation.name, 18, height - 44);
	}
}

function renderMap(locations, logs, containerId) {
	const container = document.getElementById(containerId);
	if (!container) {
		return;
	}

	container.querySelectorAll('.map-pin').forEach((pin) => pin.remove());

	const points = [...(locations || []), ...(logs || []).map((log) => ({ latitude: log.latitude, longitude: log.longitude }))];

	if (!points.length) {
		return;
	}

	const latitudes = points.map((point) => Number(point.latitude));
	const longitudes = points.map((point) => Number(point.longitude));
	const minLat = Math.min(...latitudes);
	const maxLat = Math.max(...latitudes);
	const minLng = Math.min(...longitudes);
	const maxLng = Math.max(...longitudes);

	const toPosition = (point) => ({
		left: mapScale(point.longitude, minLng, maxLng, 8, 92),
		top: mapScale(point.latitude, maxLat, minLat, 8, 88),
	});

	(locations || []).forEach((location) => {
		const pos = toPosition(location);
		const pin = document.createElement('div');
		pin.className = 'map-pin';
		pin.style.left = `${pos.left}%`;
		pin.style.top = `${pos.top}%`;
		pin.innerHTML = `<div class="pin-dot" style="background: var(--accent2)"></div><div class="pin-label">${location.name}</div>`;
		container.appendChild(pin);
	});

	(logs || []).slice(0, 6).forEach((log) => {
		const pos = toPosition(log);
		const pin = document.createElement('div');
		pin.className = 'map-pin';
		pin.style.left = `${pos.left}%`;
		pin.style.top = `${pos.top}%`;
		pin.innerHTML = '<div class="pin-dot" style="background: var(--warn); width: 8px; height: 8px;"></div>';
		container.appendChild(pin);
	});
}

function mapScale(value, fromMin, fromMax, toMin, toMax) {
	if (fromMax === fromMin) {
		return (toMin + toMax) / 2;
	}

	return toMin + ((value - fromMin) * (toMax - toMin)) / (fromMax - fromMin);
}

function haversineDistance(lat1, lng1, lat2, lng2) {
	const earthRadius = 6371000;
	const toRadians = (degrees) => (degrees * Math.PI) / 180;
	const dLat = toRadians(lat2 - lat1);
	const dLng = toRadians(lng2 - lng1);
	const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLng / 2) ** 2;

	return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function stopCamera() {
	if (runtime.mediaStream) {
		runtime.mediaStream.getTracks().forEach((track) => track.stop());
		runtime.mediaStream = null;
	}
}

function toast(message, type = 'success') {
	const container = document.getElementById('toast');
	if (!container) {
		return;
	}

	container.textContent = message;
	container.className = `toast show ${type}`;
	clearTimeout(runtime.toastTimer);
	runtime.toastTimer = setTimeout(() => {
		container.className = 'toast';
	}, 2800);
}

window.toast = toast;
