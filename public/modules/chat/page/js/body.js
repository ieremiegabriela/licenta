document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#messages").classList.contains("active")) {
		document.querySelector("a#messages").classList.add("active");
	}

	// Convert the unix epoch into actual date time format according to the timezone of the client
	document.querySelectorAll("span.message-status").forEach((element, index) => {
		element.innerHTML = `${unixToFormattedTime(
			element.getAttribute("data-unix-epoch")
		)}&nbsp;·&nbsp;${element.getAttribute("data-status")}`;
	});

	document.querySelectorAll("div.message-container:not(:has(.conv-info))").forEach((element, index) => {
		element.onclick = messageContainerClick;
	});

	if (document.querySelector("#sendMessageForm")) {
		document.querySelector("#sendMessageForm").onsubmit = handleMessageSubmission;
		document.querySelector("input").oninput = handleFieldInput;
	}
}

function handleFieldInput(event) {
	let inputField = event.currentTarget;
	let submitBtn = document.querySelector(`button[type="submit"]`);
	let message = inputField.value;

	if (!message && submitBtn.classList.contains("bg-warning")) {
		submitBtn.classList.remove("bg-warning");
		submitBtn.classList.add("bg-secondary-subtle");
		submitBtn.classList.add("text-shadow");
		submitBtn.style.cursor = "not-allowed";
	} else if (message && submitBtn.classList.contains("bg-secondary-subtle")) {
		submitBtn.classList.remove("bg-secondary-subtle");
		submitBtn.classList.remove("text-shadow");
		submitBtn.classList.add("bg-warning");
		submitBtn.style.cursor = "pointer";
	}
}

function handleMessageSubmission(event) {
	event.preventDefault();

	let inputField = event.currentTarget.querySelector("input");
	let message = inputField.value;
	if (!message) return;

	disableDocumentKeyboardInput();

	let submitBtn = document.querySelector(`button[type="submit"]`);
	let div = document.createElement("div");
	let img = document.createElement("img");
	div.style.width = "66px";
	div.classList.add("btn", "btn-primary", "ms-1", "custom-border-radius", "bg-info-subtle", "border-0", "p-0", "m-0");
	img.setAttribute("src", "assets/img/loading.gif");
	img.setAttribute("alt", "#");
	img.setAttribute("height", "38");
	div.appendChild(img);

	submitBtn.style.display = "none";
	submitBtn.after(div);
	inputField.focus();

	let evtSource = document.arguments.evtSource;
	let sseURL = document.arguments.sseURL;
	evtSource.close();

	sendMessage({
		id: id,
		message: message,
	}).then((response) => {
		refreshAuthToken().then((newToken) => {
			evtSource.close();
			document.arguments.evtSource = initializeSSE(sseURL, input, newToken);
			inputField.value = String();

			enableDocumentKeyboardInput();
		});
	});
}

async function sendMessage(data) {
	const url = `${window.location.origin}/modules/chat/handlers/send_message.php`;

	try {
		const response = await fetch(url, {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			body: JSON.stringify(data),
		});

		if (!response.ok) {
			throw new Error(`Response status: ${response.status}`);
		} else {
			const data = await response.json();

			return data;
		}
	} catch (error) {
		console.error(error);
	}
}

function messageContainerClick(event) {
	if (!event.currentTarget.querySelector("span.message-status").classList.contains("last-message")) {
		switch (event.currentTarget.querySelector("span.message-status").classList.contains("d-none")) {
			case true:
				event.currentTarget.querySelector("span.message-status").classList.remove("d-none");
				break;
			case false:
				event.currentTarget.querySelector("span.message-status").classList.add("d-none");
				break;
		}
	}
}
