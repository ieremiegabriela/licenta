document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#home").classList.contains("active")) {
		document.querySelector("a#home").classList.add("active");
	}
	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);

	const lastMarkedTimeTag = document.querySelector("span#lastMarkTime");
	const dataTimestamp = lastMarkedTimeTag ? lastMarkedTimeTag.getAttribute("data-timestamp") : null;
	const dataStatusClasses = lastMarkedTimeTag ? lastMarkedTimeTag.getAttribute("data-status-classes") : null;

	if (lastMarkedTimeTag && dataTimestamp && dataStatusClasses) {
		lastMarkedTimeTag.innerHTML = `<i>${unixToFormattedDateTime(
			dataTimestamp
		)}</i>&nbsp;·&nbsp;<i class="${dataStatusClasses}"></i>`;
	} else if (lastMarkedTimeTag) {
		lastMarkedTimeTag.innerHTML = "<i>Hasn't marked a status yet</i>";
	}

	bindButtonHandlers();
}

function beforeUnload(event) {
	if (document.querySelector("a#home").classList.contains("active")) {
		document.querySelector("a#home").classList.remove("active");
	}
}

function bindButtonHandlers() {
	document.querySelector("button.btn-safe").onclick = handleStatusBtnClick;
	document.querySelector("button.btn-unsafe").onclick = handleStatusBtnClick;
}

function handleStatusBtnClick(event) {
	const targetBtn = event.currentTarget;
	let statusType = null;
	switch (true) {
		case targetBtn.classList.contains("btn-safe"):
			statusType = "safe";
			break;
		case targetBtn.classList.contains("btn-unsafe"):
			statusType = "danger";
			break;
		default:
			alert("Ooops! Something went wrong...");
			window.location.reload(true);
	}

	submitStatus({ data: { type: statusType } });
}

function submitStatus({ data = null } = {}) {
	if (!data) return;
	showLoadingGifOverlay();
	let evtSource = document.arguments.evtSource;
	let sseURL = document.arguments.sseURL;
	evtSource.close();
	const request = $.ajax({
		url: "/modules/index/handlers/submit_status.php",
		data: data,
		type: "POST",
		success: () => {
			let evtSource = document.arguments.evtSource;
			let sseURL = document.arguments.sseURL;
			refreshAuthToken().then((newToken) => {
				evtSource.close();
				document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
				hideLoadingGifOverlay();
			});
		},
	});
	return request;
}
