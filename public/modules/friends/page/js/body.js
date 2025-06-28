document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#friends").classList.contains("active")) {
		document.querySelector("a#friends").classList.add("active");
	}

	$("#friendsTable").DataTable({
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
	});

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
}

function fetchSearchResults({ value = null, limitOne = 1 } = {}) {
	let request = $.ajax({
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

	let evtSource = document.arguments.evtSource;
	let sseURL = document.arguments.sseURL;
	refreshAuthToken().then((newToken) => {
		evtSource.close();
		document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
	});
}
