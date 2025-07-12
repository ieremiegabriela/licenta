disableDocumentKeyboardInput();

document.addEventListener("DOMContentLoaded", (event) => {
	hideLoadingGifOverlay();

	let sseURL = `/modules/friends/sse.php`;
	let evtSource = initializeSSE(sseURL, input);

	document.arguments = window.arguments = { evtSource: evtSource, sseURL: sseURL, input: input };

	window.onbeforeunload = handleBeforeUnload;
	document.onvisibilitychange = visibilityChange;
});
