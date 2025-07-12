function setIntervalImmediately($fn = null, $interval = 1000) {
	if (typeof $fn === "function") {
		$fn();

		setInterval(() => {
			$fn();
		}, $interval);
	}
}

// --------------------------------------------------

function unixToFormattedTime(unixFormat) {
	// Create a new JavaScript Date object based on the timestamp
	// multiplied by 1000 so that the argument is in milliseconds, not seconds
	let date = new Date(unixFormat * 1000);

	// Hours part from the timestamp
	let hours = date.getHours();

	// Minutes part from the timestamp
	let minutes = "0" + date.getMinutes();

	// Will display time in 10:30 format
	let formattedTime = hours + ":" + minutes.slice(-2);

	return formattedTime;
}

// --------------------------------------------------

function docKeyInputPreventDefault(event) {
	event.preventDefault();
}

function disableDocumentKeyboardInput() {
	document.addEventListener("keypress", docKeyInputPreventDefault);
	document.addEventListener("keydown", docKeyInputPreventDefault);
	document.addEventListener("keyup", docKeyInputPreventDefault);
}

function enableDocumentKeyboardInput() {
	document.removeEventListener("keypress", docKeyInputPreventDefault);
	document.removeEventListener("keydown", docKeyInputPreventDefault);
	document.removeEventListener("keyup", docKeyInputPreventDefault);
}

function showLoadingGifOverlay() {
	if (document.querySelector("div.overlay").classList.contains("d-none")) {
		document.querySelector("div.overlay").classList.remove("d-none");
	}

	// Prevent keyboard input when overlay is on
	disableDocumentKeyboardInput();
}

function hideLoadingGifOverlay() {
	if (!document.querySelector("div.overlay").classList.contains("d-none")) {
		document.querySelector("div.overlay").classList.add("d-none");
	}

	// Allow keyboard input when overlay is off
	enableDocumentKeyboardInput();
}

// --------------------------------------------------

/**
 * Convert a template string into HTML DOM nodes
 * @param  {String} str The template string
 * @return {Node}       The template HTML
 */
let createVirtualDOM = function (str) {
	let parser = new DOMParser();
	let doc = parser.parseFromString(str, "text/html");
	return doc.body;
};

/**
 * Get the type for a node
 * @param  {Node}   node The node
 * @return {String}      The type
 */
let getNodeType = function (node) {
	if (node.nodeType === 3) return "text";
	if (node.nodeType === 8) return "comment";
	return node.tagName.toLowerCase();
};

/**
 * Get the content from a node
 * @param  {Node}   node The node
 * @return {String}      The type
 */
let getNodeContent = function (node) {
	if (node.childNodes && node.childNodes.length > 0) return null;
	return node.textContent;
};

let enableDiffLogging = false; // Toggle this to enable/disable logging

/**
 * Compare the template to the UI and make updates, including attributes
 * @param  {Node} template The template HTML
 * @param  {Node} element  The UI HTML
 */
let diff = function (template, element, callback) {
	// Get arrays of child nodes
	let domNodes = Array.prototype.slice.call(element.childNodes);
	let templateNodes = Array.prototype.slice.call(template.childNodes);

	// Remove extra elements in DOM
	let count = domNodes.length - templateNodes.length;
	if (count > 0) {
		for (; count > 0; count--) {
			if (enableDiffLogging) console.log("Removing:", domNodes[domNodes.length - count]);
			domNodes[domNodes.length - count].parentNode.removeChild(domNodes[domNodes.length - count]);
		}
	}

	// Diff each item in the templateNodes
	templateNodes.forEach(function (node, index) {
		// If element doesn't exist, create it
		if (!domNodes[index]) {
			if (enableDiffLogging) console.log("Creating new element:", node);
			element.appendChild(node.cloneNode(true));
			return;
		}

		// If element is not the same type, replace it
		if (getNodeType(node) !== getNodeType(domNodes[index])) {
			if (enableDiffLogging) console.log("Replacing element:", domNodes[index], "with", node);
			domNodes[index].parentNode.replaceChild(node.cloneNode(true), domNodes[index]);
			return;
		}

		// Compare attributes
		if (node.nodeType === 1) {
			let templateAttributes = node.attributes;
			let domAttributes = domNodes[index].attributes;

			// Remove outdated attributes
			for (let attr of domAttributes) {
				if (!node.hasAttribute(attr.name)) {
					if (enableDiffLogging) console.log("Removing attribute:", attr.name);
					domNodes[index].removeAttribute(attr.name);
				}
			}

			// Update or add attributes
			for (let attr of templateAttributes) {
				if (domNodes[index].getAttribute(attr.name) !== attr.value) {
					if (enableDiffLogging) console.log("Updating attribute:", attr.name, "to", attr.value);
					domNodes[index].setAttribute(attr.name, attr.value);
				}
			}
		}

		// If content is different, update it
		let templateContent = getNodeContent(node);
		if (templateContent && templateContent !== getNodeContent(domNodes[index])) {
			if (enableDiffLogging) console.log("Updating content:", domNodes[index], "to", templateContent);
			domNodes[index].textContent = templateContent;
		}

		// Handle empty/non-empty elements
		if (domNodes[index].childNodes.length > 0 && node.childNodes.length < 1) {
			if (enableDiffLogging) console.log("Clearing content of:", domNodes[index]);
			domNodes[index].innerHTML = "";
			return;
		}

		if (domNodes[index].childNodes.length < 1 && node.childNodes.length > 0) {
			let fragment = document.createDocumentFragment();
			diff(node, fragment);
			if (enableDiffLogging) console.log("Appending new child elements to:", domNodes[index]);
			domNodes[index].appendChild(fragment);
			return;
		}

		// Diff child nodes recursively
		if (node.childNodes.length > 0) {
			diff(node, domNodes[index]);
		}
	});

	if (typeof callback === "function") {
		callback();
	}
};

function initializeSSE(sseURL, input, token = localStorage.getItem("authToken")) {
	let evtSource = new EventSource(`${sseURL}?authToken=${token}&input=${input}`);

	setupEventListeners(sseURL, evtSource);
	return evtSource;
}

function setupEventListeners(sseURL, evtSource) {
	evtSource.removeEventListener("ping", handlePing);
	evtSource.removeEventListener("update", handleUpdate);
	evtSource.removeEventListener("error", handleError);

	evtSource.arguments = { sseURL: sseURL };
	evtSource.addEventListener("ping", handlePing);
	evtSource.addEventListener("update", handleUpdate);
	evtSource.addEventListener("error", handleError);
}

// --------------------------------------------------

function visibilityChange(event) {
	const modalShown = $(".modal.show").length > 0;

	let evtSource = document.arguments.evtSource;
	let sseURL = document.arguments.sseURL;
	let input = document.arguments.input;

	if (!document.hidden && !modalShown) {
		showLoadingGifOverlay();

		setTimeout(() => {
			if (!document.hidden) {
				refreshAuthToken().then((newToken) => {
					evtSource.close();
					document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
				});
			}
		}, 1000);
	} else {
		evtSource.close();
	}
}

function handlePing(event) {
	let jsonObj = JSON.parse(event.data);
	if (!jsonObj.authenticated) window.location.reload(true);
}

function handleUpdate(event) {
	let jsonObj = JSON.parse(event.data);
	if (jsonObj.success) {
		diff(createVirtualDOM(jsonObj.data), document.body, () => {
			hideLoadingGifOverlay();
			DOMContentLoaded();
		});
	}
}

function handleError(event) {
	let sseURL = event.currentTarget.arguments.sseURL;
	let input = event.currentTarget.arguments.input;

	console.warn("SSE connection error. Retrying...");

	if (!document.reconnecting) {
		document.reconnecting = true;
		setTimeout(() => {
			document.reconnecting = false;
			refreshAuthToken().then((newToken) => {
				document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
			});
		}, 3000);
	}
}

function handleBeforeUnload(event) {
	showLoadingGifOverlay();
	if (event.currentTarget.arguments.evtSource) {
		event.currentTarget.arguments.evtSource.close();
	}
}

async function refreshAuthToken() {
	const response = await fetch(`/helpers/php/handlers/auth_token_refresh.php`, {
		method: "POST",
	});
	const data = await response.json();
	return data.data;
}
