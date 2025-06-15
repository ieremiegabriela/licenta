disableDocumentKeyboardInput();

document.addEventListener("DOMContentLoaded", (event) => {
	hideLoadingGifOverlay();

	if (!document.querySelector("a#friends").classList.contains("active")) {
		document.querySelector("a#friends").classList.add("active");
	}

	document.querySelectorAll("div.message-container").forEach((element, index) => {
		element.onclick = messageContainerClick;
	});

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

			// Toggle the visibility
			let table = this.api();
			let column = table.column(4);
			column.visible(0);
		},
		ajax: {
			url: `${window.location.origin}/modules/friends/handlers/fetch_friends.php`,
			dataSrc: "data",
		},
		columns: [
			{ data: "picture" },
			{ data: "name" },
			{ data: "status" },
			{ data: "actions" },
			{ data: "timestamp" },
		],
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
});
