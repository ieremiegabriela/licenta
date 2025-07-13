document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#feed").classList.contains("active")) {
		document.querySelector("a#feed").classList.add("active");
	}

	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);

	const table = $("#feedTable")
		.DataTable({
			drawCallback: function () {
				// Set custom styles
				$(`input.dt-input[type="search"]`).attr("placeholder", "Find in feed...");
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
			order: [],
		})
		.column(4)
		.visible(false);

	bindRowBtnHandlers();
	embedParsedTimestamps();
}

function embedParsedTimestamps() {
	document.querySelectorAll("span.last-mark-time").forEach((element) => {
		const dataTimestamp = element.getAttribute("data-timestamp");
		const dataStatusClasses = element.getAttribute("data-status-classes");

		if (dataTimestamp && dataStatusClasses) {
			element.innerHTML = `<i>${unixToFormattedDateTime(
				dataTimestamp
			)}</i>&nbsp;·&nbsp;<i class="${dataStatusClasses}"></i>`;
		}
	});
}

function beforeUnload(event) {
	if (document.querySelector("a#feed").classList.contains("active")) {
		document.querySelector("a#feed").classList.remove("active");
	}
}

function authorizeAction({ action = null, userId = null } = {}) {
	showLoadingGifOverlay();
	let evtSource = document.arguments.evtSource;
	evtSource.close();

	const request = $.ajax({
		url: "modules/feed/handlers/authorize_action.php",
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
	const url = `/modules/feed/handlers/send_message_gateway.php?userId=${userId}`;
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

function bindRowBtnHandlers() {
	document.querySelectorAll("button.send-message").forEach((element, index) => {
		element.onclick = handleSendMessage;
	});
}
