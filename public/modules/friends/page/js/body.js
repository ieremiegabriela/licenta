document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#friends").classList.contains("active")) {
		document.querySelector("a#friends").classList.add("active");
	}

	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);

	const table = $("#friendsTable")
		.DataTable({
			drawCallback: function () {
				// Set custom styles
				$(`input.dt-input[type="search"]`).attr("placeholder", "Find friends...");
				$("div.dt-layout-row").first().addClass("d-inline-flex");
				$("tbody tr td").first().attr("width", "5%");

				const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
				const tooltipList = [...tooltipTriggerList].map(
					(tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
				);
			},
			language: {
				search: "",
				lengthMenu: "_MENU_",
				info: "Displaying _START_ to _END_ of _TOTAL_ records",
				paginate: {
					first: "First",
					last: "Last",
					next: `<i class="fa-solid fa-chevron-right"></i>`,
					previous: `<i class="fa-solid fa-chevron-left"></i>`,
				},
			},
			columnDefs: [
				{ target: 1, className: "dt-left", width: "60%" },
				{ target: 2, className: "dt-center dt-middle", width: "20%" },
				{ target: 3, className: "dt-center dt-middle", width: "20%", orderable: false, searchable: false },
				{ target: 3, visibile: false, searchable: false },
			],
			order: [[4, "desc"]],
		})
		.column(4)
		.visible(false);

	sessionStorage.setItem("originalModalContent", document.querySelector("#addFriendsModal").innerHTML);
	bindAddFriendsModalHandlers();
	bindRowBtnHandlers();
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
		url: "modules/friends/handlers/authorize_action.php",
		data: {
			userId: userId,
			action: action,
		},
		type: "POST",
	});

	return request;
}

function revokeFriendRequest({ userId = null } = {}) {
	if (!userId) return;

	const request = $.ajax({
		url: "modules/friends/handlers/revoke_friend_request.php",
		data: {
			userId: userId,
		},
		type: "POST",
		success: () => {
			hideLoadingGifOverlay();
			let evtSource = document.arguments.evtSource;
			let sseURL = document.arguments.sseURL;
			refreshAuthToken().then((newToken) => {
				evtSource.close();
				document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
			});
		},
	});

	return request;
}

function removeFriendRequest({ userId = null } = {}) {
	if (!userId) return;

	const request = $.ajax({
		url: "modules/friends/handlers/remove_friend.php",
		data: {
			userId: userId,
		},
		type: "POST",
		success: () => {
			hideLoadingGifOverlay();
			let evtSource = document.arguments.evtSource;
			let sseURL = document.arguments.sseURL;
			refreshAuthToken().then((newToken) => {
				evtSource.close();
				document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
			});
		},
	});

	return request;
}

function acceptFriendRequest({ userId = null } = {}) {
	if (!userId) return;

	const request = $.ajax({
		url: "modules/friends/handlers/accept_friend_request.php",
		data: {
			userId: userId,
		},
		type: "POST",
		success: () => {
			hideLoadingGifOverlay();
			let evtSource = document.arguments.evtSource;
			let sseURL = document.arguments.sseURL;
			refreshAuthToken().then((newToken) => {
				evtSource.close();
				document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
			});
		},
	});

	return request;
}

function handleRevokeRequest(event) {
	const userId = event.currentTarget.getAttribute("data-id");
	authorizeAction({ action: "revoke-request", userId: userId }).then((response) => {
		let jsonObj = $.parseJSON(response);

		switch (jsonObj.success) {
			default:
			case 0:
				alert(jsonObj.message);
				window.location.reload(true);
				break;
			case 1:
				revokeFriendRequest({ userId });
				break;
		}
	});
}
function handleRemoveFriend(event) {
	const userId = event.currentTarget.getAttribute("data-id");
	authorizeAction({ action: "remove-friend", userId: userId }).then((response) => {
		let jsonObj = $.parseJSON(response);

		switch (jsonObj.success) {
			default:
			case 0:
				alert(jsonObj.message);
				window.location.reload(true);
				break;
			case 1:
				removeFriendRequest({ userId });
				break;
		}
	});
}
function handleAcceptRequest(event) {
	const userId = event.currentTarget.getAttribute("data-id");
	authorizeAction({ action: "accept-request", userId: userId }).then((response) => {
		let jsonObj = $.parseJSON(response);

		switch (jsonObj.success) {
			default:
			case 0:
				alert(jsonObj.message);
				window.location.reload(true);
				break;
			case 1:
				acceptFriendRequest({ userId });
				break;
		}
	});
}
function handleSendMessage(event) {
	const userId = event.currentTarget.getAttribute("data-id");
	const url = `/modules/friends/handlers/send_message_gateway.php?userId=${userId}`;
	authorizeAction({ action: "send-message", userId: userId }).then((response) => {
		let jsonObj = $.parseJSON(response);

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

function bindRowBtnHandlers() {
	document.querySelectorAll("button.revoke-request").forEach((element, index) => {
		element.onclick = handleRevokeRequest;
	});
	document.querySelectorAll("button.remove-friend").forEach((element, index) => {
		element.onclick = handleRemoveFriend;
	});
	document.querySelectorAll("button.accept-request").forEach((element, index) => {
		element.onclick = handleAcceptRequest;
	});
	document.querySelectorAll("button.send-message").forEach((element, index) => {
		element.onclick = handleSendMessage;
	});
}

function bindAddFriendsModalHandlers() {
	$("#addFriendsModal")
		.off("shown.bs.modal")
		.on("shown.bs.modal", (event) => {
			document.querySelector("input#searchBox").focus();
		});
	$("#addFriendsModal").off("show.bs.modal").on("show.bs.modal", handleModalShow);
	$("#addFriendsModal").off("hidden.bs.modal").on("hidden.bs.modal", handleModalHidden);

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

function handleFriendRequestResponse(response) {
	const originalModalContent = sessionStorage.getItem("originalModalContent");
	const targetModal = document.querySelector("#addFriendsModal");
	let jsonObj = $.parseJSON(response);

	switch (jsonObj.success) {
		default:
		case 0:
			targetModal.innerHTML = originalModalContent;
			bindAddFriendsModalHandlers();
			const alertDanger = document.querySelector("div.alert.friend-request-danger");
			if (alertDanger.classList.contains("d-none")) {
				alertDanger.classList.remove("d-none");
			}
			setTimeout(() => {
				$(alertDanger).fadeOut(500, () => {
					if (!alertDanger.classList.contains("d-none")) {
						alertDanger.classList.add("d-none");
					}
				});
			}, 3500);
			break;
		case 1:
			targetModal.innerHTML = originalModalContent;
			bindAddFriendsModalHandlers();
			const alertSuccess = document.querySelector("div.alert.friend-request-success");
			if (alertSuccess.classList.contains("d-none")) {
				alertSuccess.classList.remove("d-none");
			}
			setTimeout(() => {
				$(alertSuccess).fadeOut(500, () => {
					if (!alertSuccess.classList.contains("d-none")) {
						alertSuccess.classList.add("d-none");
					}
				});
			}, 3500);
			break;
	}
}

function sendFriendRequest({ userId = null } = {}) {
	if (!userId) return;

	const request = $.ajax({
		url: "modules/friends/handlers/send_friend_request.php",
		data: {
			userId: userId,
		},
		type: "POST",
		success: handleFriendRequestResponse,
	});

	return request;
}

function handleAddFriendClick(event) {
	const userId = event.currentTarget.getAttribute("data-id");

	sendFriendRequest({ userId: userId });
}

function handleResultsIntegration(response, { value }) {
	const div = document.querySelector("div.search-results");
	let jsonObj = $.parseJSON(response);

	if (jsonObj.success === 0) {
		alert(jsonObj.message);
		window.location.reload(true);
	}

	if (value) {
		div.innerHTML = jsonObj.data;
	} else {
		div.innerHTML = String("");
	}

	document.querySelectorAll("button.add-friend-btn").forEach((element, index) => {
		element.onclick = handleAddFriendClick;
	});
}

function fetchSearchResults({ value = null, limitOne = 1 } = {}) {
	const request = $.ajax({
		url: "modules/friends/handlers/fetch_search_results.php",
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
