document.addEventListener("DOMContentLoaded", DOMContentLoaded);

function DOMContentLoaded(event) {
	if (!document.querySelector("a#home").classList.contains("active")) {
		document.querySelector("a#home").classList.add("active");
	}
	document.removeEventListener("beforeUnload", beforeUnload);
	document.addEventListener("beforeUnload", beforeUnload);
}

function beforeUnload(event) {
	if (document.querySelector("a#home").classList.contains("active")) {
		document.querySelector("a#home").classList.remove("active");
	}
}
