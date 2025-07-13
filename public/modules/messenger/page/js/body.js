document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#messages").classList.contains("active")) {
		document.querySelector("a#messages").classList.add("active");
	}
	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);

	// Convert the unix epoch into actual date time format according to the timezone of the client
	document.querySelectorAll("span.chat-timestamp").forEach((element, index) => {
		element.textContent = unixToFormattedTime(element.getAttribute("data-unix-epoch"));
	});

	sessionStorage.setItem("originalModalContent", document.querySelector("#messageFriendsModal").innerHTML);
	bindMessageFriendsModalHandlers();
}

function beforeUnload(event) {
	if (document.querySelector("a#messages").classList.contains("active")) {
		document.querySelector("a#messages").classList.remove("active");
	}
}

function authorizeAction({ action = null, userId = null } = {}) {
	showLoadingGifOverlay();
	let evtSource = document.arguments.evtSource;
	evtSource.close();

	const request = $.ajax({
		url: "modules/messenger/handlers/authorize_action.php",
		data: {
			userId: userId,
			action: action,
		},
		type: "POST",
	});

	return request;
}

function handleSendMessage(event) {
	const userId = event.currentTarget.getAttribute("data-id");
	const url = `/modules/messenger/handlers/send_message_gateway.php?userId=${userId}`;
	authorizeAction({ action: "send-message", userId: userId }).then((response) => {
		const jsonObj = $.parseJSON(response);

		switch (jsonObj.success) {
			default:
			case 0:
				alert(jsonObj.message);
				window.location.reload(true);
				break;
			case 1:
				window.location.replace(url);
				break;
		}
	});
}

function bindMessageFriendsModalHandlers() {
	$("#messageFriendsModal")
		.off("shown.bs.modal")
		.on("shown.bs.modal", (event) => {
			document.querySelector("input#searchBox").focus();
		});
	$("#messageFriendsModal").off("show.bs.modal").on("show.bs.modal", handleModalShow);
	$("#messageFriendsModal").off("hidden.bs.modal").on("hidden.bs.modal", handleModalHidden);

	document.querySelector("input#searchBox").oninput = handleUserSearchInput;
	document.querySelector("button#allResultsBtn").onclick = handleUserSearchInput;
}

function handleButtonState(value) {
	const button = document.querySelector("button#allResultsBtn");

	switch (!value) {
		case false:
			if (button.classList.contains("disabled")) {
				button.classList.remove("disabled");
			}
			break;
		case true:
			if (!button.classList.contains("disabled")) {
				button.classList.add("disabled");
			}
			break;
	}
}

function handleUserSearchInput(event) {
	switch (true) {
		case event.currentTarget.type === "text":
			handleButtonState(event.currentTarget.value);

			fetchSearchResults({
				value: event.currentTarget.value,
				limitOne: 1,
			});
			break;
		case event.currentTarget.type === "button":
			fetchSearchResults({
				value: document.querySelector("input#searchBox").value,
				limitOne: 0,
			});
			break;
	}
}

function handleResultsIntegration(response, { value }) {
	const div = document.querySelector("div.search-results");
	const jsonObj = $.parseJSON(response);

	if (jsonObj.success === 0) {
		alert(jsonObj.message);
		window.location.reload(true);
	}

	if (value) {
		div.innerHTML = jsonObj.data;
	} else {
		div.innerHTML = String("");
	}

	document.querySelectorAll("button.message-friend-btn").forEach((element, index) => {
		element.onclick = handleSendMessage;
	});
}

function fetchSearchResults({ value = null, limitOne = 1 } = {}) {
	const request = $.ajax({
		url: "modules/messenger/handlers/fetch_search_results.php",
		data: {
			searchedVal: value,
			limitOne: limitOne,
		},
		type: "POST",
		success: (response) => {
			handleResultsIntegration(response, { value });
		},
	});

	return request;
}

function handleModalShow(event) {
	let evtSource = document.arguments.evtSource;
	evtSource.close();
}

function handleModalHidden(event) {
	if (document.activeElement) {
		document.activeElement.blur();
	}

	document.querySelector("input#searchBox").value = String("");

	let evtSource = document.arguments.evtSource;
	let sseURL = document.arguments.sseURL;
	refreshAuthToken().then((newToken) => {
		evtSource.close();
		document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
	});
}
