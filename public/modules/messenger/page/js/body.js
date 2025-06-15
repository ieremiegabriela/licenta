document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#messages").classList.contains("active")) {
		document.querySelector("a#messages").classList.add("active");
	}
	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);

	// Convert the unix epoch into actual date time format according to the timezone of the client
	document.querySelectorAll("span.chat-timestamp").forEach((element, index) => {
		element.textContent = unixToDateTime(element.getAttribute("data-unix-epoch"));
	});
}

function beforeUnload(event) {
	if (document.querySelector("a#messages").classList.contains("active")) {
		document.querySelector("a#messages").classList.remove("active");
	}
}
